<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Order;
use App\Models\Product;
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

        $gmvThisMonth = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $pendingDistributors = DistributorProfile::query()
            ->where('is_approved', false)
            ->count();

        $pendingProfiles = DistributorProfile::query()
            ->where('is_approved', false)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => [
                [
                    'label' => 'Customers',
                    'value' => User::role('customer')->count(),
                    'desc' => 'Registered buyers',
                    'color' => 'blue',
                    'icon' => 'icon-users',
                ],
                [
                    'label' => 'Distributors',
                    'value' => User::role('distributor')->count(),
                    'desc' => $pendingDistributors.' pending approval',
                    'color' => $pendingDistributors > 0 ? 'amber' : 'green',
                    'icon' => 'icon-users',
                ],
                [
                    'label' => 'Products',
                    'value' => Product::count(),
                    'desc' => Category::count().' categories',
                    'color' => 'purple',
                    'icon' => 'icon-database',
                ],
                [
                    'label' => 'Orders (month)',
                    'value' => $ordersThisMonth,
                    'desc' => 'Orders placed this month',
                    'color' => 'green',
                    'icon' => 'icon-check-circle',
                ],
                [
                    'label' => 'GMV (month)',
                    'value' => format_inr($gmvThisMonth),
                    'desc' => 'Gross merchandise value',
                    'color' => 'green',
                    'icon' => 'icon-dollar-sign',
                ],
            ],
            'pendingProfiles' => $pendingProfiles,
        ]);
    }
}
