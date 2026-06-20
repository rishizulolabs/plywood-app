<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Api\ApiController;
use App\Models\RestockRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->distributorProfile;

        if (! $profile) {
            return $this->jsonError('Distributor profile not found.', 404);
        }

        $status = $request->query('status');

        $purchaseOrders = RestockRequest::query()
            ->where('distributor_profile_id', $profile->id)
            ->with(['product.category'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        $items = $purchaseOrders->getCollection()
            ->map(fn (RestockRequest $order) => $this->purchaseOrderPayload($order))
            ->values();

        $baseQuery = RestockRequest::query()->where('distributor_profile_id', $profile->id);

        $fulfilledQuery = (clone $baseQuery)->where('status', 'fulfilled');
        $fulfilledValue = (float) $fulfilledQuery->sum('total_amount');

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'fulfilled' => (clone $fulfilledQuery)->count(),
            'total_value' => (float) (clone $baseQuery)->sum('total_amount'),
            'total_value_formatted' => format_inr_compact((float) (clone $baseQuery)->sum('total_amount')),
            'fulfilled_value' => $fulfilledValue,
            'fulfilled_value_formatted' => format_inr_compact($fulfilledValue),
        ];

        return $this->jsonSuccess([
            'purchase_orders' => $items,
            'stats' => $stats,
            'meta' => [
                'current_page' => $purchaseOrders->currentPage(),
                'last_page' => $purchaseOrders->lastPage(),
                'per_page' => $purchaseOrders->perPage(),
                'total' => $purchaseOrders->total(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function purchaseOrderPayload(RestockRequest $order): array
    {
        return [
            'id' => $order->id,
            'request_number' => $order->request_number,
            'product_id' => $order->product_id,
            'product_name' => $order->product?->name,
            'category_name' => $order->product?->category?->name,
            'quantity' => $order->quantity,
            'unit_price' => (float) $order->unit_price,
            'unit_price_formatted' => format_inr($order->unit_price),
            'total_amount' => (float) $order->total_amount,
            'total_formatted' => format_inr($order->total_amount),
            'status' => $order->status,
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}
