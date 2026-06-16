<?php

namespace App\Http\Controllers\Customer;

use App\Models\CartItem;
use App\Models\Inquiry;
use App\Models\InquiryItem;
use App\Models\Order;
use App\Models\Product;
use App\Support\CustomerCart;
use App\Support\InquiryDistributorResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends CustomerController
{
    public function index(): View
    {
        $customer = $this->customer();

        return view('customer.cart.index', [
            'cartItems' => CustomerCart::items($customer),
        ]);
    }

    public function remove(Product $product): RedirectResponse
    {
        CustomerCart::remove($this->customer(), $product);

        return redirect()
            ->route('customer.cart.index')
            ->with('success', "{$product->name} removed from your cart.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $customer = $this->customer();

        CartItem::query()
            ->where('user_id', $customer->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $minQty = max(1, (int) ($product->min_order_qty ?? 1));

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:'.$minQty],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        CustomerCart::update(
            $customer,
            $product,
            $validated['quantity'],
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('customer.cart.index')
            ->with('success', "{$product->name} updated in your cart.");
    }

    public function proceed(Request $request): RedirectResponse
    {
        $customer = $this->customer();
        $cart = CustomerCart::items($customer);

        if ($cart === []) {
            return redirect()
                ->route('customer.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $groupedItems = [];

        foreach ($cart as $item) {
            $product = Product::query()->find($item['product_id'] ?? null);

            if (! $product) {
                continue;
            }

            $distributor = InquiryDistributorResolver::forProduct($product, $customer);

            if (! $distributor) {
                return redirect()
                    ->route('customer.cart.index')
                    ->with('error', "No distributor is available for {$item['product_name']}. Please contact support.");
            }

            $groupedItems[$distributor->id][] = [
                'product_id' => $product->id,
                'quantity' => (int) ($item['quantity'] ?? 1),
                'customer_remarks' => $item['notes'] ?? null,
            ];
        }

        if ($groupedItems === []) {
            return redirect()
                ->route('customer.cart.index')
                ->with('error', 'Could not place order. Please add valid products to your cart.');
        }

        DB::transaction(function () use ($groupedItems, $customer) {
            foreach ($groupedItems as $distributorId => $items) {
                $inquiry = Inquiry::create([
                    'customer_id' => $customer->id,
                    'distributor_profile_id' => $distributorId,
                    'status' => 'pending',
                    'delivery_city' => $customer->city ?? 'Not specified',
                    'delivery_pincode' => $customer->pincode ?? '000000',
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

                Order::create([
                    'inquiry_id' => $inquiry->id,
                    'customer_id' => $customer->id,
                    'distributor_profile_id' => $distributorId,
                    'total_amount' => $this->calculateOrderTotal($distributorId, $items),
                    'payment_status' => 'pending',
                    'fulfillment_status' => 'processing',
                    'delivery_address' => $this->deliveryAddressFor($customer),
                ]);
            }
        });

        $orderCount = count($groupedItems);

        return redirect()
            ->route('customer.orders.index')
            ->with('success', $orderCount === 1
                ? 'Your order was sent to the distributor.'
                : "Your {$orderCount} orders were sent to distributors.");
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

            $price = $offered?->pivot->price ?? 0;
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
