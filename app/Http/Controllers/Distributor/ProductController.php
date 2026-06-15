<?php

namespace App\Http\Controllers\Distributor;

use App\Models\InquiryItem;
use App\Models\Product;
use App\Models\RestockRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends DistributorController
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $profile = $this->distributorProfile();

        if (! $profile) {
            $products = new LengthAwarePaginator([], 0, 15);

            return view('distributor.products.index', [
                'products' => $products,
                'search' => $search,
                'customerOrderTotals' => collect(),
            ]);
        }

        $products = $profile->offeredProducts()
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
            ->paginate(15)
            ->withQueryString();

        $productIds = $products->getCollection()->pluck('id');

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

        return view('distributor.products.index', [
            'products' => $products,
            'search' => $search,
            'customerOrderTotals' => $customerOrderTotals,
        ]);
    }

    public function restock(Request $request, Product $product): RedirectResponse
    {
        $profile = $this->distributorProfile();

        if (! $profile) {
            abort(403);
        }

        $assignedProduct = $profile->offeredProducts()
            ->where('products.id', $product->id)
            ->first();

        if (! $assignedProduct) {
            abort(404);
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

        DB::table('distributor_product')
            ->where('distributor_profile_id', $profile->id)
            ->where('product_id', $product->id)
            ->increment('stock_quantity', $quantity);

        return redirect()
            ->route('distributor.purchase-orders.index', $request->only('search'))
            ->with('success', "Restock order placed for {$product->name} (×{$quantity}). Admin will review your request.");
    }
}
