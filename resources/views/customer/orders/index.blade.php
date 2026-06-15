@extends('layouts.customer')

@section('title', 'Orders')
@section('page-title', 'Orders')
@section('page-subtitle', 'Orders placed from your cart')

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
            </div>
        </div>
    @endforeach
</div>

<div class="content-card space-y" style="margin-top: 1.5rem;">
    <div class="content-card-header">
        <p class="content-card-title">Orders</p>
        <span class="badge badge-gray">{{ $orders->total() }} total</span>
    </div>

    @if($orders->isEmpty())
        <x-admin.empty-state message="No orders yet. Add products to your cart and click Proceed to place an order." />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Distributor</th>
                    <th>Products</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    @php
                        $items = $order->inquiry?->items ?? collect();
                        $productLabel = $items->map(fn ($item) => ($item->product?->name ?? 'Product').' × '.$item->quantity)->join(', ');
                    @endphp
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->distributorProfile?->business_name ?? '—' }}</td>
                        <td>{{ $productLabel ?: '—' }}</td>
                        <td>{{ format_inr($order->total_amount) }}</td>
                        <td><span class="badge badge-gray">{{ ucfirst($order->payment_status) }}</span></td>
                        <td>
                            @if($order->fulfillment_status === 'delivered')
                                <span class="badge badge-green">{{ ucfirst($order->fulfillment_status) }}</span>
                            @elseif($order->fulfillment_status === 'cancelled')
                                <span class="badge badge-gray">{{ ucfirst($order->fulfillment_status) }}</span>
                            @else
                                <span class="badge badge-yellow">{{ ucfirst($order->fulfillment_status) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-admin.pagination :paginator="$orders" />
    @endif
</div>
@endsection
