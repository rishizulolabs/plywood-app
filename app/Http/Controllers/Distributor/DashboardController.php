<?php

namespace App\Http\Controllers\Distributor;

use Illuminate\View\View;

class DashboardController extends DistributorController
{
    public function index(): View
    {
        $user = auth()->user();
        $profile = $user->distributorProfile;

        $productCount = $profile?->products()->count() ?? 0;
        $inquiryCount = $profile?->inquiries()->count() ?? 0;
        $orderCount = $profile?->orders()->count() ?? 0;
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
                'label' => 'Inquiries',
                'value' => $inquiryCount,
                'desc' => 'Customer quote requests',
                'color' => 'amber',
                'icon' => 'icon-file-text',
                'href' => route('distributor.inquiries.index'),
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
                'label' => 'Approval',
                'value' => $isApproved ? 'Approved' : 'Pending',
                'desc' => $isApproved ? 'Account is active' : 'Awaiting admin review',
                'color' => $isApproved ? 'green' : 'amber',
                'icon' => 'icon-check-circle',
                'href' => route('profile.edit'),
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
                'title' => 'View inquiries',
                'desc' => 'Respond to customer quote requests',
                'icon' => 'icon-file-text',
                'href' => route('distributor.inquiries.index'),
                'color' => 'amber',
            ],
            [
                'title' => 'Track orders',
                'desc' => 'Monitor payment and fulfillment',
                'icon' => 'icon-package',
                'href' => route('distributor.orders.index'),
                'color' => 'green',
            ],
            [
                'title' => 'Business profile',
                'desc' => 'Update GST, cities and bank details',
                'icon' => 'icon-user',
                'href' => route('profile.edit'),
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
