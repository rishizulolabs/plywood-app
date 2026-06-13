<?php

namespace App\Http\Controllers\Customer;

use App\Models\Inquiry;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends CustomerController
{
    public function index(): View
    {
        $user = $this->customer();

        $inquiryCount = $user->inquiries()->count();
        $orderCount = $user->orders()->count();
        $pendingQuotesCount = $user->inquiries()
            ->whereIn('status', ['pending', 'quoted', 'negotiating'])
            ->count();
        $cartItemCount = count(session('inquiry_cart', []));

        $recentInquiries = Inquiry::query()
            ->where('customer_id', $user->id)
            ->with(['distributorProfile', 'quote'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            [
                'label' => 'Inquiries',
                'value' => $inquiryCount,
                'desc' => 'Quote requests sent',
                'color' => 'blue',
                'icon' => 'icon-file-text',
                'href' => route('customer.inquiries.index'),
            ],
            [
                'label' => 'Orders',
                'value' => $orderCount,
                'desc' => 'Confirmed purchases',
                'color' => 'green',
                'icon' => 'icon-package',
                'href' => route('customer.orders.index'),
            ],
            [
                'label' => 'Pending quotes',
                'value' => $pendingQuotesCount,
                'desc' => 'Awaiting distributor response',
                'color' => 'amber',
                'icon' => 'icon-dollar-sign',
                'href' => route('customer.inquiries.index'),
            ],
            [
                'label' => 'Cart items',
                'value' => $cartItemCount,
                'desc' => 'Ready to submit',
                'color' => 'purple',
                'icon' => 'icon-shopping-cart',
                'href' => route('customer.inquiry-cart.index'),
            ],
        ];

        $quickActions = [
            [
                'title' => 'Browse catalog',
                'desc' => 'Explore plywood by category and specs',
                'icon' => 'icon-layers',
                'href' => route('customer.catalog.index'),
                'color' => 'blue',
            ],
            [
                'title' => 'Inquiry cart',
                'desc' => 'Review products before requesting a quote',
                'icon' => 'icon-shopping-cart',
                'href' => route('customer.inquiry-cart.index'),
                'color' => 'purple',
            ],
            [
                'title' => 'My inquiries',
                'desc' => 'Track quote requests and responses',
                'icon' => 'icon-file-text',
                'href' => route('customer.inquiries.index'),
                'color' => 'amber',
            ],
            [
                'title' => 'My orders',
                'desc' => 'View payment and delivery status',
                'icon' => 'icon-package',
                'href' => route('customer.orders.index'),
                'color' => 'green',
            ],
        ];

        return view('customer.dashboard', compact(
            'user',
            'stats',
            'quickActions',
            'recentInquiries',
            'inquiryCount',
        ));
    }
}
