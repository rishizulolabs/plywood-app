<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\InquiryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with(['customer', 'distributorProfile.user', 'inquiry.items.product'])
            ->latest()
            ->paginate(15);

        $stats = [
            [
                'label' => 'Total orders',
                'value' => Order::count(),
                'desc' => 'All confirmed orders',
                'color' => 'blue',
                'icon' => 'icon-file-text',
            ],
            [
                'label' => 'Completed',
                'value' => Order::where('fulfillment_status', 'delivered')->count(),
                'desc' => 'Delivered orders',
                'color' => 'green',
                'icon' => 'icon-check-circle',
            ],
            [
                'label' => 'Pending',
                'value' => Order::query()
                    ->where(function ($query) {
                        $query->where('payment_status', 'pending')
                            ->orWhere('fulfillment_status', 'processing');
                    })
                    ->count(),
                'desc' => 'Awaiting payment or dispatch',
                'color' => 'amber',
                'icon' => 'icon-activity',
            ],
        ];

        return view('admin.orders.customers', compact('orders', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'distributor_profile_id' => ['required', 'exists:distributor_profiles,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'payment_status' => ['required', 'in:pending,partial,paid'],
            'fulfillment_status' => ['required', 'in:processing,dispatched,delivered,cancelled'],
        ]);

        $customer = User::role('customer')->whereKey($validated['customer_id'])->firstOrFail();

        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->where('distributor_profile_id', $validated['distributor_profile_id'])
            ->firstOrFail();

        DB::transaction(function () use ($validated, $customer, $product) {
            $inquiry = Inquiry::create([
                'customer_id' => $customer->id,
                'distributor_profile_id' => $validated['distributor_profile_id'],
                'status' => 'converted',
                'delivery_city' => $customer->city ?? 'N/A',
                'delivery_pincode' => $customer->pincode ?? '000000',
            ]);

            InquiryItem::create([
                'inquiry_id' => $inquiry->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
            ]);

            Order::create([
                'inquiry_id' => $inquiry->id,
                'customer_id' => $customer->id,
                'distributor_profile_id' => $validated['distributor_profile_id'],
                'total_amount' => $validated['total_amount'],
                'payment_status' => $validated['payment_status'],
                'fulfillment_status' => $validated['fulfillment_status'],
                'delivery_address' => $validated['delivery_address'],
            ]);
        });

        return redirect()
            ->route('admin.customer-orders.index')
            ->with('success', 'Order added successfully.');
    }
}
