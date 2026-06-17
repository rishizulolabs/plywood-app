<?php

namespace App\Http\Controllers\Customer;

use App\Models\Order;
use App\Support\CustomerCart;
use Illuminate\View\View;

class DashboardController extends CustomerController
{
    public function index(): View
    {
        $user = $this->customer();

        $orderCount = $user->orders()->count();
        $processingCount = $user->orders()
            ->where('fulfillment_status', 'processing')
            ->count();
        $cartItemCount = CustomerCart::count($user);

        $recentOrders = Order::query()
            ->where('customer_id', $user->id)
            ->with(['distributorProfile', 'inquiry.items.product'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            [
                'label' => 'Orders',
                'value' => $orderCount,
                'desc' => 'Orders placed',
                'color' => 'green',
                'icon' => 'icon-package',
                'href' => route('customer.orders.index'),
            ],
            [
                'label' => 'Processing',
                'value' => $processingCount,
                'desc' => 'Awaiting fulfillment',
                'color' => 'amber',
                'icon' => 'icon-activity',
                'href' => route('customer.orders.index'),
            ],
            [
                'label' => 'Cart items',
                'value' => $cartItemCount,
                'desc' => 'Ready to order',
                'color' => 'purple',
                'icon' => 'icon-shopping-cart',
                'href' => route('customer.cart.index'),
            ],
        ];

        return view('customer.dashboard', compact(
            'stats',
            'recentOrders',
            'orderCount',
        ));
    }
}
