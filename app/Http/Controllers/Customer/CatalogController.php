<?php

namespace App\Http\Controllers\Customer;

use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Product;
use App\Support\CustomerCart;
use App\Support\InquiryDistributorResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends CustomerController
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');
        $distributor = $this->customerDistributor($request);

        $products = Product::query()
            ->with(['category', 'media'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('grade', 'like', "%{$search}%")
                        ->orWhere('thickness', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->whereHas('products')
            ->orderBy('name')
            ->get();

        return view('customer.catalog.index', compact('products', 'categories', 'search', 'categoryId', 'distributor'));
    }

    public function show(Request $request, Product $product): View
    {
        $distributor = $this->customerDistributor($request);

        $product->load(['category', 'media', 'distributorProfile']);

        return view('customer.catalog.show', compact('product', 'distributor'));
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        $distributor = $this->customerDistributor($request)
            ?? InquiryDistributorResolver::forProduct($product, $request->user());

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        CustomerCart::add($request->user(), $product, $validated['quantity'], $distributor);

        return redirect()
            ->route('customer.cart.index')
            ->with('success', "{$product->name} added to your cart.");
    }

    private function customerDistributor(Request $request): ?DistributorProfile
    {
        $distributor = $request->user()->assignedDistributor;

        if (! $distributor?->is_approved) {
            return null;
        }

        return $distributor;
    }
}
