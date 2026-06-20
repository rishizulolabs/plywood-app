<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $distributor = $this->customerDistributor($user);
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');

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
            ->paginate((int) $request->query('per_page', 20));

        $distributorId = $distributor?->id;

        $items = $products->getCollection()
            ->map(fn (Product $product) => $this->productPayload($product, $distributorId, $user))
            ->values();

        $payload = [
            'products' => $items,
            'distributor_linked' => $distributor !== null,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ];

        if ($distributor) {
            $payload['distributor'] = [
                'id' => $distributor->id,
                'business_name' => $distributor->business_name,
            ];
        }

        return $this->jsonSuccess($payload);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $distributor = $this->customerDistributor($user);
        $product->load(['category', 'media']);

        return $this->jsonSuccess([
            'product' => $this->productPayload($product, $distributor?->id, $user),
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->whereHas('products')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

        return $this->jsonSuccess(['categories' => $categories]);
    }
}
