<?php

namespace App\Http\Controllers\Customer;

use App\Models\Order;
use Illuminate\View\View;

class OrderController extends CustomerController
{
    public function index(): View
    {
        $customerId = $this->customer()->id;

        $orders = Order::query()
            ->where('customer_id', $customerId)
            ->with(['distributorProfile', 'inquiry.items.product'])
            ->latest()
            ->paginate(15);

        $stats = [
            [
                'label' => 'Total orders',
                'value' => Order::where('customer_id', $customerId)->count(),
                'color' => 'blue',
                'icon' => 'icon-file-text',
            ],
            [
                'label' => 'Completed',
                'value' => Order::where('customer_id', $customerId)
                    ->where('fulfillment_status', 'delivered')
                    ->count(),
                'color' => 'green',
                'icon' => 'icon-check-circle',
            ],
            [
                'label' => 'Pending',
                'value' => Order::where('customer_id', $customerId)
                    ->where(function ($query) {
                        $query->where('payment_status', 'pending')
                            ->orWhere('fulfillment_status', 'processing');
                    })
                    ->count(),
                'color' => 'amber',
                'icon' => 'icon-activity',
            ],
        ];

        return view('customer.orders.index', compact('orders', 'stats'));
    }
}
