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

        $quickActions = [
            [
                'title' => 'Browse catalog',
                'desc' => 'Explore plywood by category and specs',
                'icon' => 'icon-layers',
                'href' => route('customer.catalog.index'),
                'color' => 'blue',
            ],
            [
                'title' => 'Cart',
                'desc' => 'Review products before placing an order',
                'icon' => 'icon-shopping-cart',
                'href' => route('customer.cart.index'),
                'color' => 'purple',
            ],
            [
                'title' => 'My orders',
                'desc' => 'View payment and delivery status',
                'icon' => 'icon-package',
                'href' => route('customer.orders.index'),
                'color' => 'green',
            ],
            [
                'title' => 'Profile',
                'desc' => 'Update company and delivery details',
                'icon' => 'icon-user',
                'href' => route('profile.edit'),
                'color' => 'amber',
            ],
        ];

        return view('customer.dashboard', compact(
            'user',
            'stats',
            'quickActions',
            'recentOrders',
            'orderCount',
        ));
    }
}
