@extends('layouts.distributor')

@section('title', 'Orders')
@section('page-title', 'Orders')
@section('page-subtitle', 'Confirmed orders from your quotes')

@section('content')
@unless($profile)
    <div class="alert alert-warning" role="alert">
        <svg class="alert-icon" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg>
        <p class="alert-message">Complete your business profile to manage orders.</p>
    </div>
@else
    @if(! empty($stats))
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
    @endif

    <div class="content-card space-y" style="margin-top: 1.5rem;">
        <div class="content-card-header">
            <p class="content-card-title">Orders</p>
            <span class="badge badge-gray">{{ $orders->total() }} total</span>
        </div>

        @if($orders->isEmpty())
            <x-admin.empty-state message="No orders yet. Confirmed inquiries will appear here." />
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
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
                            <td>{{ $order->customer?->name ?? '—' }}</td>
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
@endunless
@endsection
