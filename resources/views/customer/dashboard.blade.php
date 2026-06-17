@extends('layouts.customer')

@section('title', 'Dashboard')

@section('page-heading')
<div class="admin-page-heading">
    <h1>Dashboard</h1>
    <p class="admin-page-subtitle">Browse products, manage your cart, and track orders</p>
</div>
@endsection

@section('content')
<div class="customer-dashboard-page">
<div class="stat-cards stat-cards-3 customer-dashboard-stats">
    @foreach ($stats as $stat)
        <a href="{{ $stat['href'] }}" class="stat-card stat-card-link">
            <div class="stat-card-icon stat-card-icon-{{ $stat['color'] }}">
                <svg class="icon-svg" aria-hidden="true"><use href="#{{ $stat['icon'] }}"></use></svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-label">{{ $stat['label'] }}</p>
                <p class="stat-value">{{ $stat['value'] }}</p>
                <p class="stat-desc">{{ $stat['desc'] }}</p>
            </div>
        </a>
    @endforeach
</div>

@if($orderCount === 0)
    <div class="content-card customer-getting-started">
        <div class="content-card-header">
            <p class="content-card-title">Getting started</p>
            <span class="badge badge-gray">3 steps</span>
        </div>
        <div class="customer-steps-grid">
            <div class="customer-step">
                <span class="customer-step-number">1</span>
                <div>
                    <p class="customer-step-title">Browse the catalog</p>
                    <p class="customer-step-desc">Find plywood by thickness, grade, and brand.</p>
                </div>
            </div>
            <div class="customer-step">
                <span class="customer-step-number">2</span>
                <div>
                    <p class="customer-step-title">Add to cart</p>
                    <p class="customer-step-desc">Select products and quantities for your project.</p>
                </div>
            </div>
            <div class="customer-step">
                <span class="customer-step-number">3</span>
                <div>
                    <p class="customer-step-title">Place your order</p>
                    <p class="customer-step-desc">Proceed from your cart to send the order to a distributor.</p>
                </div>
            </div>
        </div>
        <div class="customer-getting-started-cta">
            <a href="{{ route('customer.catalog.index') }}" class="btn-add">
                <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-layers"></use></svg>
                <span>Start browsing</span>
            </a>
        </div>
    </div>
@else
    <div class="content-card space-y customer-recent-orders">
        <div class="content-card-header">
            <p class="content-card-title">Recent orders</p>
            <a href="{{ route('customer.orders.index') }}" class="btn-link-table">View all</a>
        </div>

        @if($recentOrders->isEmpty())
            <x-admin.empty-state message="No recent orders." />
        @else
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Distributor</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentOrders as $order)
                        @php
                            $items = $order->inquiry?->items ?? collect();
                            $productLabel = $items
                                ->map(fn ($item) => ($item->product?->name ?? 'Product').' * '.$item->quantity)
                                ->join(', ');
                        @endphp
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->distributorProfile?->business_name ?? '—' }}</td>
                            <td>{{ $productLabel ?: '—' }}</td>
                            <td><span class="badge badge-yellow">{{ ucfirst($order->fulfillment_status) }}</span></td>
                            <td>{{ $order->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endif
</div>
@endsection
