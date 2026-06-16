<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Product;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    public function index(): View
    {
        return view('public.home', [
            'productCount' => Product::assignedToDistributor()->count(),
            'distributorCount' => DistributorProfile::query()->where('is_approved', true)->count(),
            'categoryCount' => Category::query()->count(),
        ]);
    }
}
