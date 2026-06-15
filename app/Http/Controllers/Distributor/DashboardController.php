<?php

namespace App\Http\Controllers\Distributor;

use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends DistributorController
{
    public function index(): View
    {
        $user = auth()->user();
        $profile = $user->distributorProfile;

        $productCount = Product::query()->count();
        $orderCount = $profile?->orders()->count() ?? 0;
        $processingCount = $profile?->orders()
            ->where('fulfillment_status', 'processing')
            ->count() ?? 0;
        $isApproved = (bool) ($profile?->is_approved);

        $stats = [
            [
                'label' => 'Products listed',
                'value' => $productCount,
                'desc' => 'Items in your catalog',
                'color' => 'blue',
                'icon' => 'icon-database',
                'href' => route('distributor.products.index'),
            ],
            [
                'label' => 'Active orders',
                'value' => $orderCount,
                'desc' => 'Orders to fulfill',
                'color' => 'green',
                'icon' => 'icon-package',
                'href' => route('distributor.orders.index'),
            ],
            [
                'label' => 'Processing',
                'value' => $processingCount,
                'desc' => 'Awaiting fulfillment',
                'color' => 'amber',
                'icon' => 'icon-activity',
                'href' => route('distributor.orders.index'),
            ],
            [
                'label' => 'Approval',
                'value' => $isApproved ? 'Approved' : 'Pending',
                'desc' => $isApproved ? 'Account is active' : 'Awaiting admin review',
                'color' => $isApproved ? 'green' : 'amber',
                'icon' => 'icon-check-circle',
                'href' => route('distributor.dashboard'),
            ],
        ];

        $quickActions = [
            [
                'title' => 'Manage products',
                'desc' => 'View and update your plywood listings',
                'icon' => 'icon-database',
                'href' => route('distributor.products.index'),
                'color' => 'blue',
            ],
            [
                'title' => 'Track orders',
                'desc' => 'Monitor payment and fulfillment',
                'icon' => 'icon-package',
                'href' => route('distributor.orders.index'),
                'color' => 'green',
            ],
            [
                'title' => 'Purchase orders',
                'desc' => 'View restock requests placed with admin',
                'icon' => 'icon-file-text',
                'href' => route('distributor.purchase-orders.index'),
                'color' => 'purple',
            ],
        ];

        return view('distributor.dashboard', compact(
            'user',
            'profile',
            'stats',
            'quickActions',
            'isApproved',
        ));
    }
}
