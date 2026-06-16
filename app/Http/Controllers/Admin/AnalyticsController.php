<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $distributors = DistributorProfile::query()
            ->with('user')
            ->orderBy('business_name')
            ->get()
            ->map(fn (DistributorProfile $profile) => [
                'id' => $profile->id,
                'label' => $this->distributorLabel($profile),
            ]);

        $selectedFilter = (string) $request->input('distributor', 'all');
        $isAll = $selectedFilter === 'all';
        $selectedId = (! $isAll && $selectedFilter !== '') ? (int) $selectedFilter : null;
        $selected = null;
        $scopeLabel = 'All distributors';

        if ($selectedId) {
            $selected = DistributorProfile::query()
                ->with(['user', 'offeredProducts.category'])
                ->find($selectedId);

            if ($selected) {
                $scopeLabel = $this->distributorLabel($selected);
            }
        }

        $customerOrdersQuery = $this->customerOrdersQuery($selectedId);
        $restockOrdersBaseQuery = $this->restockOrdersBaseQuery($selectedId);
        $products = $this->productsForScope($selectedId);

        $customerOrdersCount = (clone $customerOrdersQuery)->count();
        $recentCustomerOrders = (clone $customerOrdersQuery)
            ->limit(5)
            ->get();

        $restockOrdersCount = (clone $restockOrdersBaseQuery)->count();
        $restockOrdersTotal = (float) (clone $restockOrdersBaseQuery)->sum('total_amount');
        $recentRestockOrders = (clone $restockOrdersBaseQuery)
            ->with(['distributorProfile.user', 'product.category'])
            ->latest()
            ->limit(5)
            ->get();

        $summary = [
            'distributors_count' => $isAll ? $distributors->count() : 1,
            'customer_orders_count' => $customerOrdersCount,
            'restock_orders_count' => $restockOrdersCount,
            'products_count' => $products->count(),
            'restock_orders_total' => $restockOrdersTotal,
            'total_stock' => (int) $products->sum(fn ($product) => (int) ($product->pivot->stock_quantity ?? 0)),
        ];

        $viewAllOrdersUrl = route('admin.distributor-orders.index', array_filter([
            'search' => $selected ? $this->distributorLabel($selected) : null,
        ]));

        $viewAllCustomerOrdersUrl = route('admin.customer-orders.index');

        return view('admin.analytics.index', compact(
            'distributors',
            'selectedFilter',
            'selectedId',
            'selected',
            'isAll',
            'scopeLabel',
            'summary',
            'products',
            'recentRestockOrders',
            'restockOrdersCount',
            'recentCustomerOrders',
            'customerOrdersCount',
            'viewAllOrdersUrl',
            'viewAllCustomerOrdersUrl',
        ));
    }

    private function distributorLabel(DistributorProfile $profile): string
    {
        return $profile->business_name ?: ($profile->user?->name ?? 'Distributor #'.$profile->id);
    }

    private function restockOrdersBaseQuery(?int $selectedId)
    {
        return RestockRequest::query()
            ->when($selectedId, fn ($query) => $query->where('distributor_profile_id', $selectedId));
    }

    private function customerOrdersQuery(?int $selectedId)
    {
        return Order::query()
            ->when($selectedId, fn ($query) => $query->where('distributor_profile_id', $selectedId))
            ->with(['customer', 'distributorProfile.user', 'inquiry.items.product'])
            ->latest();
    }

    private function productsForScope(?int $selectedId): Collection
    {
        if ($selectedId) {
            $profile = DistributorProfile::query()
                ->with(['offeredProducts.category'])
                ->find($selectedId);

            return $profile?->offeredProducts ?? collect();
        }

        return DistributorProfile::query()
            ->with(['offeredProducts.category', 'user'])
            ->get()
            ->flatMap(function (DistributorProfile $profile) {
                return $profile->offeredProducts->map(function (Product $product) use ($profile) {
                    $product->setRelation('distributorProfile', $profile);

                    return $product;
                });
            })
            ->values();
    }
}
