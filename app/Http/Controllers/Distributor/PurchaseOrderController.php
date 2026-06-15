<?php

namespace App\Http\Controllers\Distributor;

use App\Models\RestockRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class PurchaseOrderController extends DistributorController
{
    public function index(): View
    {
        $profile = $this->distributorProfile();

        $purchaseOrders = $profile
            ? RestockRequest::query()
                ->where('distributor_profile_id', $profile->id)
                ->with('product')
                ->latest()
                ->paginate(15)
            : new LengthAwarePaginator([], 0, 15);

        $stats = $profile ? [
            [
                'label' => 'Total orders',
                'value' => RestockRequest::where('distributor_profile_id', $profile->id)->count(),
                'color' => 'blue',
                'icon' => 'icon-file-text',
            ],
            [
                'label' => 'Pending',
                'value' => RestockRequest::where('distributor_profile_id', $profile->id)
                    ->where('status', 'pending')
                    ->count(),
                'color' => 'amber',
                'icon' => 'icon-activity',
            ],
            [
                'label' => 'Fulfilled',
                'value' => RestockRequest::where('distributor_profile_id', $profile->id)
                    ->where('status', 'fulfilled')
                    ->count(),
                'color' => 'green',
                'icon' => 'icon-check-circle',
            ],
        ] : [];

        return view('distributor.purchase-orders.index', compact('profile', 'purchaseOrders', 'stats'));
    }
}
