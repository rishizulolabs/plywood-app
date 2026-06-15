<?php

namespace App\Http\Controllers\Distributor;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class OrderController extends DistributorController
{
    public function index(): View
    {
        $profile = $this->distributorProfile();

        $orders = $profile
            ? Order::query()
                ->where('distributor_profile_id', $profile->id)
                ->with(['customer', 'inquiry.items.product'])
                ->latest()
                ->paginate(15)
            : new LengthAwarePaginator([], 0, 15);

        $stats = $profile ? [
            [
                'label' => 'Total orders',
                'value' => Order::where('distributor_profile_id', $profile->id)->count(),
                'color' => 'blue',
                'icon' => 'icon-file-text',
            ],
            [
                'label' => 'Completed',
                'value' => Order::where('distributor_profile_id', $profile->id)
                    ->where('fulfillment_status', 'delivered')
                    ->count(),
                'color' => 'green',
                'icon' => 'icon-check-circle',
            ],
            [
                'label' => 'Pending',
                'value' => Order::where('distributor_profile_id', $profile->id)
                    ->where(function ($query) {
                        $query->where('payment_status', 'pending')
                            ->orWhere('fulfillment_status', 'processing');
                    })
                    ->count(),
                'color' => 'amber',
                'icon' => 'icon-activity',
            ],
        ] : [];

        return view('distributor.orders.index', compact('profile', 'orders', 'stats'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $profile = $this->distributorProfile();

        if (! $profile || $order->distributor_profile_id !== $profile->id) {
            abort(403);
        }

        $validated = $request->validate([
            'fulfillment_status' => ['required', 'in:processing,dispatched,delivered,cancelled'],
        ]);

        $order->update([
            'fulfillment_status' => $validated['fulfillment_status'],
        ]);

        return redirect()
            ->route('distributor.orders.index')
            ->with('success', "Order {$order->order_number} status updated.");
    }
}
