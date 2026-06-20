<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Api\ApiController;
use App\Models\InquiryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestockRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $profile = $request->user()->distributorProfile;

        if (! $profile) {
            return $this->jsonError('Distributor profile not found.', 404);
        }

        $products = Product::query()
            ->with(['category', 'media'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('products.name', 'like', "%{$search}%")
                        ->orWhere('products.brand', 'like', "%{$search}%")
                        ->orWhere('products.grade', 'like', "%{$search}%")
                        ->orWhere('products.thickness', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('products.name')
            ->paginate((int) $request->query('per_page', 20));

        $productIds = $products->getCollection()->pluck('id');

        $assignments = $productIds->isEmpty()
            ? collect()
            : DB::table('distributor_product')
                ->where('distributor_profile_id', $profile->id)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

        $customerOrderTotals = $productIds->isEmpty()
            ? collect()
            : InquiryItem::query()
                ->selectRaw('inquiry_items.product_id, SUM(inquiry_items.quantity) as total')
                ->join('inquiries', 'inquiries.id', '=', 'inquiry_items.inquiry_id')
                ->join('orders', 'orders.inquiry_id', '=', 'inquiries.id')
                ->where('orders.distributor_profile_id', $profile->id)
                ->whereIn('inquiry_items.product_id', $productIds)
                ->groupBy('inquiry_items.product_id')
                ->pluck('total', 'product_id');

        $items = $products->getCollection()->map(function (Product $product) use ($profile, $assignments, $customerOrderTotals) {
            $assignment = $assignments->get($product->id);
            $payload = $this->productPayload($product, $profile->id);

            $payload['is_assigned'] = $assignment !== null;
            $payload['customer_orders_total'] = (int) ($customerOrderTotals[$product->id] ?? 0);

            return $payload;
        })->values();

        return $this->jsonSuccess([
            'products' => $items,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function restock(Request $request, Product $product): JsonResponse
    {
        $profile = $request->user()->distributorProfile;

        if (! $profile) {
            return $this->jsonError('Distributor profile not found.', 404);
        }

        $assignedProduct = $profile->offeredProducts()
            ->where('products.id', $product->id)
            ->first();

        if (! $assignedProduct) {
            $profile->offeredProducts()->attach($product->id, [
                'price' => 0,
                'stock_quantity' => 0,
            ]);

            $assignedProduct = $profile->offeredProducts()
                ->where('products.id', $product->id)
                ->first();
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $unitPrice = (float) $assignedProduct->pivot->price;
        $quantity = (int) $validated['quantity'];

        RestockRequest::create([
            'distributor_profile_id' => $profile->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $unitPrice * $quantity,
            'status' => 'pending',
        ]);

        return $this->jsonSuccess([], "Restock order placed for {$product->name} (×{$quantity}).");
    }
}
