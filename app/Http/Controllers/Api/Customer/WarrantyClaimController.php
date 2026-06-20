<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Order;
use App\Models\WarrantyClaim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarrantyClaimController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $claims = WarrantyClaim::query()
            ->where('user_id', $request->user()->id)
            ->with(['order', 'media'])
            ->latest()
            ->paginate((int) $request->query('per_page', 20));

        $items = $claims->getCollection()
            ->map(fn (WarrantyClaim $claim) => $this->claimPayload($claim))
            ->values();

        return $this->jsonSuccess([
            'claims' => $items,
            'meta' => [
                'current_page' => $claims->currentPage(),
                'last_page' => $claims->lastPage(),
                'per_page' => $claims->perPage(),
                'total' => $claims->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'complaint' => ['required', 'string', 'min:10', 'max:2000'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $order = Order::query()
            ->where('id', $validated['order_id'])
            ->where('customer_id', $request->user()->id)
            ->with(['inquiry.items.product'])
            ->first();

        if (! $order) {
            return $this->jsonError('Order not found.', 404);
        }

        if ($order->fulfillment_status !== 'delivered') {
            return $this->jsonError('Warranty claims can only be submitted for delivered orders.', 422);
        }

        $existing = WarrantyClaim::query()
            ->where('user_id', $request->user()->id)
            ->where('order_id', $order->id)
            ->whereIn('status', ['pending', 'reviewing', 'approved'])
            ->exists();

        if ($existing) {
            return $this->jsonError('A warranty claim for this order is already in progress.', 422);
        }

        $firstItem = $order->inquiry?->items->first();
        $productName = $firstItem?->product?->name;

        $claim = WarrantyClaim::create([
            'user_id' => $request->user()->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'product_name' => $productName,
            'complaint' => $validated['complaint'],
            'status' => 'pending',
        ]);

        foreach ($request->file('photos', []) as $photo) {
            $claim->addMedia($photo)->toMediaCollection('claim_photos');
        }

        $claim->load(['order', 'media']);

        return $this->jsonSuccess([
            'claim' => $this->claimPayload($claim),
        ], 'Warranty claim submitted successfully.', 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function claimPayload(WarrantyClaim $claim): array
    {
        return [
            'id' => $claim->id,
            'claim_number' => $claim->claim_number,
            'order_id' => $claim->order_id,
            'order_number' => $claim->order_number,
            'product_name' => $claim->product_name,
            'complaint' => $claim->complaint,
            'status' => $claim->status,
            'photo_urls' => $claim->getMedia('claim_photos')
                ->map(fn ($media) => $this->absoluteUrl($media->getUrl()))
                ->filter()
                ->values()
                ->all(),
            'created_at' => $claim->created_at?->toIso8601String(),
        ];
    }
}
