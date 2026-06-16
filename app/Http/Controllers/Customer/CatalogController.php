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

        $productsQuery = Product::query()
            ->with(['category', 'media']);

        if ($distributor) {
            $productsQuery->assignedToDistributor($distributor->id);
        } else {
            $productsQuery->whereRaw('1 = 0');
        }

        $products = $productsQuery
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

        $categoriesQuery = Category::query()->orderBy('name');

        if ($distributor) {
            $categoriesQuery->whereHas('products', fn ($query) => $query->assignedToDistributor($distributor->id));
        } else {
            $categoriesQuery->whereRaw('1 = 0');
        }

        $categories = $categoriesQuery->get();

        return view('customer.catalog.index', compact('products', 'categories', 'search', 'categoryId', 'distributor'));
    }

    public function show(Request $request, Product $product): View
    {
        $distributor = $this->customerDistributor($request);

        abort_unless($distributor && $product->isAssignedToDistributor($distributor->id), 404);

        $product->load(['category', 'media', 'distributorProfile']);

        return view('customer.catalog.show', compact('product', 'distributor'));
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        $distributor = $this->customerDistributor($request);

        abort_unless($distributor && $product->isAssignedToDistributor($distributor->id), 404);

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
