<?php

namespace App\Http\Controllers\Customer;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends CustomerController
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');

        $products = Product::query()
            ->with(['category', 'distributorProfile'])
            ->whereHas('distributorProfile', fn ($query) => $query->where('is_approved', true))
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
            ->whereHas('products', function ($query) {
                $query->whereHas('distributorProfile', fn ($distributorQuery) => $distributorQuery->where('is_approved', true));
            })
            ->orderBy('name')
            ->get();

        return view('customer.catalog.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product->load('distributorProfile');

        if (! $product->distributorProfile?->is_approved) {
            return back()->with('error', 'This product is not available.');
        }

        $cart = session('inquiry_cart', []);

        $existingIndex = collect($cart)->search(fn ($item) => ($item['product_id'] ?? null) === $product->id);

        if ($existingIndex !== false) {
            $cart[$existingIndex]['quantity'] = ($cart[$existingIndex]['quantity'] ?? 0) + $validated['quantity'];
        } else {
            $cart[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $validated['quantity'],
                'distributor' => $product->distributorProfile?->business_name,
                'notes' => null,
            ];
        }

        session(['inquiry_cart' => $cart]);

        return redirect()
            ->route('customer.inquiry-cart.index')
            ->with('success', "{$product->name} added to your inquiry cart.");
    }
}
