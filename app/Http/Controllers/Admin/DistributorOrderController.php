<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestockRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DistributorOrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => (string) $request->input('status', ''),
        ];

        $restockRequests = RestockRequest::query()
            ->with(['distributorProfile.user', 'product.category'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($builder) use ($search) {
                    $builder->where('request_number', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('distributorProfile', function ($profileQuery) use ($search) {
                            $profileQuery->where('business_name', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                        });
                });
            })
            ->when(
                in_array($filters['status'], ['pending', 'approved', 'fulfilled', 'cancelled'], true),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            [
                'label' => 'Total requests',
                'value' => RestockRequest::count(),
                'desc' => 'All distributor restock orders',
                'color' => 'blue',
                'icon' => 'icon-file-text',
                'href' => route('admin.distributor-orders.index', array_filter([
                    'search' => $filters['search'] !== '' ? $filters['search'] : null,
                ])),
            ],
            [
                'label' => 'Pending',
                'value' => RestockRequest::where('status', 'pending')->count(),
                'desc' => 'Awaiting admin action',
                'color' => 'amber',
                'icon' => 'icon-activity',
                'href' => route('admin.distributor-orders.index', array_filter([
                    'status' => 'pending',
                    'search' => $filters['search'] !== '' ? $filters['search'] : null,
                ])),
            ],
            [
                'label' => 'Approved',
                'value' => RestockRequest::where('status', 'approved')->count(),
                'desc' => 'Ready to fulfill',
                'color' => 'purple',
                'icon' => 'icon-package',
                'href' => route('admin.distributor-orders.index', array_filter([
                    'status' => 'approved',
                    'search' => $filters['search'] !== '' ? $filters['search'] : null,
                ])),
            ],
            [
                'label' => 'Fulfilled',
                'value' => RestockRequest::where('status', 'fulfilled')->count(),
                'desc' => 'Completed restock orders',
                'color' => 'green',
                'icon' => 'icon-check-circle',
                'href' => route('admin.distributor-orders.index', array_filter([
                    'status' => 'fulfilled',
                    'search' => $filters['search'] !== '' ? $filters['search'] : null,
                ])),
            ],
        ];

        return view('admin.orders.distributors', compact('restockRequests', 'stats', 'filters'));
    }

    public function updateStatus(Request $request, RestockRequest $restockRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,fulfilled,cancelled'],
        ]);

        $previousStatus = $restockRequest->status;
        $newStatus = $validated['status'];

        DB::transaction(function () use ($restockRequest, $previousStatus, $newStatus) {
            $restockRequest->update([
                'status' => $newStatus,
            ]);

            if ($previousStatus !== 'fulfilled' && $newStatus === 'fulfilled') {
                $this->incrementDistributorStock($restockRequest);
            }
        });

        return redirect()
            ->route('admin.distributor-orders.index', array_filter([
                'search' => $request->input('search') ?: $request->query('search'),
                'status' => $request->input('filter_status') ?: $request->query('status'),
            ]))
            ->with('success', 'Restock order status updated.');
    }

    private function incrementDistributorStock(RestockRequest $restockRequest): void
    {
        $assignmentExists = DB::table('distributor_product')
            ->where('distributor_profile_id', $restockRequest->distributor_profile_id)
            ->where('product_id', $restockRequest->product_id)
            ->exists();

        if (! $assignmentExists) {
            DB::table('distributor_product')->insert([
                'distributor_profile_id' => $restockRequest->distributor_profile_id,
                'product_id' => $restockRequest->product_id,
                'price' => $restockRequest->unit_price,
                'stock_quantity' => $restockRequest->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('distributor_product')
            ->where('distributor_profile_id', $restockRequest->distributor_profile_id)
            ->where('product_id', $restockRequest->product_id)
            ->increment('stock_quantity', $restockRequest->quantity);
    }
}
