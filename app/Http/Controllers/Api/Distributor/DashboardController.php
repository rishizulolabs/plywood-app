<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Api\ApiController;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->distributorProfile;

        if (! $profile) {
            return $this->jsonError('Distributor profile not found.', 404);
        }

        $productCount = Product::query()->count();
        $orderCount = $profile->orders()->count();
        $processingCount = $profile->orders()
            ->where('fulfillment_status', 'processing')
            ->count();
        $deliveredCount = $profile->orders()
            ->where('fulfillment_status', 'delivered')
            ->count();
        $monthlyRevenue = (float) $profile->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $customerCount = User::role('customer')
            ->where('distributor_profile_id', $profile->id)
            ->count();

        $recentOrders = Order::query()
            ->where('distributor_profile_id', $profile->id)
            ->where('fulfillment_status', 'processing')
            ->with(['customer', 'inquiry.items.product'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => $this->orderPayload($order));

        return $this->jsonSuccess([
            'stats' => [
                'products_listed' => $productCount,
                'total_orders' => $orderCount,
                'processing_orders' => $processingCount,
                'delivered_orders' => $deliveredCount,
                'monthly_revenue' => $monthlyRevenue,
                'monthly_revenue_formatted' => format_inr($monthlyRevenue),
                'customers_connected' => $customerCount,
                'is_approved' => (bool) $profile->is_approved,
            ],
            'recent_orders' => $recentOrders,
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
