<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Api\ApiController;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->distributorProfile;

        if (! $profile) {
            return $this->jsonError('Distributor profile not found.', 404);
        }

        $status = $request->query('status');

        $orders = Order::query()
            ->where('distributor_profile_id', $profile->id)
            ->with(['customer', 'inquiry.items.product'])
            ->when($status, fn ($query) => $query->where('fulfillment_status', $status))
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        $items = $orders->getCollection()
            ->map(fn (Order $order) => $this->orderPayload($order))
            ->values();

        $stats = [
            'total' => Order::where('distributor_profile_id', $profile->id)->count(),
            'completed' => Order::where('distributor_profile_id', $profile->id)
                ->where('fulfillment_status', 'delivered')
                ->count(),
            'pending' => Order::where('distributor_profile_id', $profile->id)
                ->where(function ($query) {
                    $query->where('payment_status', 'pending')
                        ->orWhere('fulfillment_status', 'processing');
                })
                ->count(),
        ];

        return $this->jsonSuccess([
            'orders' => $items,
            'stats' => $stats,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $profile = $request->user()->distributorProfile;

        if (! $profile || $order->distributor_profile_id !== $profile->id) {
            return $this->jsonError('Order not found.', 404);
        }

        $order->load(['customer', 'inquiry.items.product']);

        return $this->jsonSuccess([
            'order' => $this->orderPayload($order),
        ]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $profile = $request->user()->distributorProfile;

        if (! $profile || $order->distributor_profile_id !== $profile->id) {
            return $this->jsonError('Order not found.', 404);
        }

        $validated = $request->validate([
            'fulfillment_status' => ['required', 'in:processing,dispatched,delivered,cancelled'],
        ]);

        $order->update([
            'fulfillment_status' => $validated['fulfillment_status'],
        ]);

        return $this->jsonSuccess([
            'order' => $this->orderPayload($order->fresh(['customer', 'inquiry.items.product'])),
        ], "Order {$order->order_number} status updated.");
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Order $order): array
    {
        $items = $order->inquiry?->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'quantity' => $item->quantity,
                'notes' => $item->customer_remarks,
            ];
        }) ?? collect();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'total_amount' => (float) $order->total_amount,
            'total_formatted' => format_inr($order->total_amount),
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
            'customer_name' => $order->customer?->name,
            'customer_phone' => $order->customer?->phone,
            'delivery_address' => $order->delivery_address,
            'items' => $items,
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}
