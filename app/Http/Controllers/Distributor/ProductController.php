<?php

namespace App\Http\Controllers\Distributor;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ProductController extends DistributorController
{
    public function index(Request $request): View
    {
        $profile = $this->distributorProfile();
        $search = trim((string) $request->query('search', ''));

        $products = $profile
            ? Product::query()
                ->where('distributor_profile_id', $profile->id)
                ->with('category')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('grade', 'like', "%{$search}%")
                            ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
                    });
                })
                ->latest()
                ->paginate(15)
                ->withQueryString()
            : new LengthAwarePaginator([], 0, 15);

        return view('distributor.products.index', [
            'profile' => $profile,
            'products' => $products,
            'search' => $search,
        ]);
    }
}
