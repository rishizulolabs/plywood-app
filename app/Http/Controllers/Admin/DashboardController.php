<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestockRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $ordersThisMonth = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $distributorBuyingTotal = RestockRequest::query()->sum('total_amount');
        $restockRequestsThisMonth = RestockRequest::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $restockRequestsTotal = RestockRequest::query()->count();

        $pendingDistributors = DistributorProfile::query()
            ->where('is_approved', false)
            ->count();

        return view('admin.dashboard', [
            'stats' => [
                [
                    'label' => 'Customers',
                    'value' => User::role('customer')->count(),
                    'desc' => 'Registered buyers',
                    'color' => 'blue',
                    'icon' => 'icon-user',
                    'href' => route('admin.customers.index'),
                ],
                [
                    'label' => 'Distributors',
                    'value' => User::role('distributor')->count(),
                    'desc' => $pendingDistributors > 0
                        ? $pendingDistributors.' pending approval'
                        : 'All accounts approved',
                    'color' => $pendingDistributors > 0 ? 'amber' : 'green',
                    'icon' => 'icon-users',
                    'href' => route('admin.distributors.index'),
                ],
                [
                    'label' => 'Products',
                    'value' => Product::count(),
                    'desc' => Category::count().' categories',
                    'color' => 'purple',
                    'icon' => 'icon-database',
                    'href' => route('admin.products.index'),
                ],
                [
                    'label' => 'Orders this month',
                    'value' => $ordersThisMonth,
                    'desc' => 'Placed by customers',
                    'color' => 'green',
                    'icon' => 'icon-shopping-cart',
                    'href' => route('admin.customer-orders.index'),
                ],
                [
                    'label' => 'Distributor purchases',
                    'value' => format_inr_compact($distributorBuyingTotal),
                    'desc' => $restockRequestsThisMonth > 0
                        ? $restockRequestsThisMonth.' restock request'.($restockRequestsThisMonth === 1 ? '' : 's').' this month'
                        : $restockRequestsTotal.' total restock requests',
                    'color' => 'blue',
                    'icon' => 'icon-package',
                    'href' => route('admin.distributor-orders.index'),
                    'is_currency' => true,
                ],
            ],
        ]);
    }
}
