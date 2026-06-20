<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $customerId = $request->user()->id;

        $orders = Order::query()
            ->where('customer_id', $customerId)
            ->with(['distributorProfile', 'inquiry.items.product'])
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        $items = $orders->getCollection()
            ->map(fn (Order $order) => $this->orderPayload($order))
            ->values();

        $stats = [
            'total' => Order::where('customer_id', $customerId)->count(),
            'completed' => Order::where('customer_id', $customerId)
                ->where('fulfillment_status', 'delivered')
                ->count(),
            'pending' => Order::where('customer_id', $customerId)
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
        if ($order->customer_id !== $request->user()->id) {
            return $this->jsonError('Order not found.', 404);
        }

        $order->load(['distributorProfile', 'inquiry.items.product']);

        return $this->jsonSuccess([
            'order' => $this->orderPayload($order),
        ]);
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
            'delivery_address' => $order->delivery_address,
            'distributor' => $order->distributorProfile?->business_name,
            'items' => $items,
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}
