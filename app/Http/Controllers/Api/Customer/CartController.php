<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\CartItem;
use App\Models\DistributorProfile;
use App\Models\Inquiry;
use App\Models\InquiryItem;
use App\Models\Order;
use App\Models\Product;
use App\Support\CustomerCart;
use App\Support\InquiryDistributorResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $cartItems = CustomerCart::items($user);

        $items = [];
        $total = 0.0;

        foreach ($cartItems as $item) {
            $product = Product::query()->find($item['product_id']);

            if (! $product) {
                continue;
            }

            $distributorId = $item['distributor_profile_id'] ?? null;
            $price = $this->priceForProduct($product, $distributorId, $user);

            $quantity = (int) ($item['quantity'] ?? 1);
            $lineTotal = $price * $quantity;
            $total += $lineTotal;

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'min_order_qty' => max(1, (int) ($product->min_order_qty ?? 1)),
                'notes' => $item['notes'],
                'price' => $price,
                'price_formatted' => format_inr($price),
                'line_total' => $lineTotal,
                'line_total_formatted' => format_inr($lineTotal),
                'image_url' => $this->productImageUrl($product),
                'distributor' => $item['distributor'],
            ];
        }

        return $this->jsonSuccess([
            'items' => $items,
            'count' => count($items),
            'total' => round($total, 2),
            'total_formatted' => format_inr($total),
        ]);
    }

    public function add(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $distributor = $this->customerDistributor($user)
            ?? InquiryDistributorResolver::forProduct($product, $user);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        CustomerCart::add($user, $product, $validated['quantity'], $distributor);

        return $this->jsonSuccess([
            'count' => CustomerCart::count($user),
        ], "{$product->name} added to cart.");
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $minQty = max(1, (int) ($product->min_order_qty ?? 1));

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:'.$minQty],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        CustomerCart::update(
            $user,
            $product,
            $validated['quantity'],
            $validated['notes'] ?? null,
        );

        return $this->jsonSuccess([
            'count' => CustomerCart::count($user),
        ], 'Cart updated.');
    }

    public function remove(Request $request, Product $product): JsonResponse
    {
        CustomerCart::remove($request->user(), $product);

        return $this->jsonSuccess([
            'count' => CustomerCart::count($request->user()),
        ], "{$product->name} removed from cart.");
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $user = $request->user();
        $cart = CustomerCart::items($user);

        if ($cart === []) {
            return $this->jsonError('Your cart is empty.');
        }

        $groupedItems = [];

        foreach ($cart as $item) {
            $product = Product::query()->find($item['product_id'] ?? null);

            if (! $product) {
                continue;
            }

            $distributor = null;

            if (! empty($item['distributor_profile_id'])) {
                $distributor = DistributorProfile::query()
                    ->whereKey($item['distributor_profile_id'])
                    ->where('is_approved', true)
                    ->first();
            }

            $distributor ??= InquiryDistributorResolver::forProduct($product, $user);

            if (! $distributor) {
                return $this->jsonError("No distributor is available for {$item['product_name']}. Please contact support.");
            }

            $groupedItems[$distributor->id][] = [
                'product_id' => $product->id,
                'quantity' => (int) ($item['quantity'] ?? 1),
                'customer_remarks' => $item['notes'] ?? null,
            ];
        }

        if ($groupedItems === []) {
            return $this->jsonError('Could not place order. Please add valid products to your cart.');
        }

        $orderIds = [];

        DB::transaction(function () use ($groupedItems, $user, &$orderIds) {
            foreach ($groupedItems as $distributorId => $items) {
                $inquiry = Inquiry::create([
                    'customer_id' => $user->id,
                    'distributor_profile_id' => $distributorId,
                    'status' => 'pending',
                    'delivery_city' => $user->city ?? 'Not specified',
                    'delivery_pincode' => $user->pincode ?? '000000',
                ]);

                foreach ($items as $item) {
                    InquiryItem::create([
                        'inquiry_id' => $inquiry->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'customer_remarks' => $item['customer_remarks'],
                    ]);
                }

                $inquiry->update(['status' => 'converted']);

                $order = Order::create([
                    'inquiry_id' => $inquiry->id,
                    'customer_id' => $user->id,
                    'distributor_profile_id' => $distributorId,
                    'total_amount' => $this->calculateOrderTotal($distributorId, $items),
                    'payment_status' => 'pending',
                    'fulfillment_status' => 'processing',
                    'delivery_address' => $this->deliveryAddressFor($user),
                ]);

                $orderIds[] = $order->id;
            }

            CartItem::query()->where('user_id', $user->id)->delete();
        });

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->with(['distributorProfile', 'inquiry.items.product'])
            ->get()
            ->map(fn (Order $order) => $this->orderPayload($order));

        return $this->jsonSuccess([
            'orders' => $orders,
        ], count($orderIds) === 1
            ? 'Your order was sent to the distributor.'
            : 'Your orders were sent to distributors.');
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Order $order): array
    {
        $items = $order->inquiry?->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'quantity' => $item->quantity,
                'notes' => $item->customer_remarks,
            ];
        }) ?? collect();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'total_amount' => (float) $order->total_amount,
            'total_formatted' => format_inr($order->total_amount),
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
            'delivery_address' => $order->delivery_address,
            'distributor' => $order->distributorProfile?->business_name,
            'items' => $items,
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }

    private function calculateOrderTotal(int $distributorId, array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            $product = Product::query()->find($item['product_id']);

            if (! $product) {
                continue;
            }

            $offered = $product->distributors()
                ->where('distributor_profile_id', $distributorId)
                ->first();

            $price = (float) ($offered?->pivot->price ?? $this->priceForProduct($product, null, null));
            $total += (float) $price * (int) $item['quantity'];
        }

        return round($total, 2);
    }

    private function deliveryAddressFor($customer): string
    {
        $parts = array_filter([
            $customer->address,
            $customer->city,
            $customer->state,
            $customer->pincode,
        ]);

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        return $customer->city ?? 'Not specified';
    }
}
