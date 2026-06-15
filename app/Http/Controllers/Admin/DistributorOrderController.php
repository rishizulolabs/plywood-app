<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestockRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistributorOrderController extends Controller
{
    public function index(): View
    {
        $restockRequests = RestockRequest::query()
            ->with(['distributorProfile.user', 'product'])
            ->latest()
            ->paginate(15);

        $stats = [
            [
                'label' => 'Total requests',
                'value' => RestockRequest::count(),
                'desc' => 'All distributor restock orders',
                'color' => 'blue',
                'icon' => 'icon-file-text',
            ],
            [
                'label' => 'Pending',
                'value' => RestockRequest::where('status', 'pending')->count(),
                'desc' => 'Awaiting admin action',
                'color' => 'amber',
                'icon' => 'icon-activity',
            ],
            [
                'label' => 'Fulfilled',
                'value' => RestockRequest::where('status', 'fulfilled')->count(),
                'desc' => 'Completed restock orders',
                'color' => 'green',
                'icon' => 'icon-check-circle',
            ],
        ];

        return view('admin.orders.distributors', compact('restockRequests', 'stats'));
    }

    public function updateStatus(Request $request, RestockRequest $restockRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,fulfilled,cancelled'],
        ]);

        $restockRequest->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.distributor-orders.index')
            ->with('success', 'Restock order status updated.');
    }
}
