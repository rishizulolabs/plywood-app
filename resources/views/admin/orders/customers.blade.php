@extends('layouts.admin')

@section('title', 'Customer Orders')
@section('page-title', 'Customer Orders')
@section('page-subtitle', 'Orders placed by customers')

@section('content')
<div class="stat-cards">
    @foreach ($stats as $stat)
        <div class="stat-card">
            <div class="stat-card-icon stat-card-icon-{{ $stat['color'] }}">
                <svg class="icon-svg" aria-hidden="true"><use href="#{{ $stat['icon'] }}"></use></svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-label">{{ $stat['label'] }}</p>
                <p class="stat-value">{{ $stat['value'] }}</p>
                <p class="stat-desc">{{ $stat['desc'] }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="content-card space-y" style="margin-top: 1.5rem;">
    <div class="content-card-header">
        <p class="content-card-title">Customer orders</p>
        <span class="badge badge-gray">{{ $orders->total() }} total</span>
    </div>

    @if($orders->isEmpty())
        <x-admin.empty-state message="No customer orders found." />
    @else
        <div class="table-responsive table-responsive-inset table-responsive--orders">
        <table class="data-table data-table-bordered data-table-orders">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Distributor</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    @php
                        $items = $order->inquiry?->items ?? collect();
                        $itemCount = $items->count();
                    @endphp
                    @foreach ($items as $item)
                        <tr>
                            @if ($loop->first)
                                <td rowspan="{{ $itemCount }}">{{ $order->order_number }}</td>
                                <td rowspan="{{ $itemCount }}">{{ $order->customer?->name ?? '—' }}</td>
                            @endif
                            <td>{{ $item->product?->name ?? 'Product' }}</td>
                            <td>{{ $item->quantity }}</td>
                            @if ($loop->first)
                                <td rowspan="{{ $itemCount }}">{{ $order->distributorProfile?->user?->name ?? $order->distributorProfile?->business_name ?? '—' }}</td>
                                <td rowspan="{{ $itemCount }}"><span class="badge badge-gray">{{ ucfirst($order->payment_status) }}</span></td>
                                <td rowspan="{{ $itemCount }}">
                                    @if($order->fulfillment_status === 'delivered')
                                        <span class="badge badge-green">{{ ucfirst($order->fulfillment_status) }}</span>
                                    @elseif($order->fulfillment_status === 'cancelled')
                                        <span class="badge badge-gray">{{ ucfirst($order->fulfillment_status) }}</span>
                                    @else
                                        <span class="badge badge-yellow">{{ ucfirst($order->fulfillment_status) }}</span>
                                    @endif
                                </td>
                                <td class="cell-nowrap" rowspan="{{ $itemCount }}">{{ $order->created_at?->format('d M Y') ?? '—' }}</td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
        </div>
        <x-admin.pagination :paginator="$orders" />
    @endif
</div>
@endsection
