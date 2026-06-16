@extends('layouts.distributor')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')
@section('page-subtitle', 'Restock orders placed with admin')

@section('content')
@unless($profile)
    <div class="alert alert-warning" role="alert">
        <svg class="alert-icon" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg>
        <p class="alert-message">Your business account is not set up yet. Contact admin for assistance.</p>
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
            <p class="content-card-title">Purchase orders</p>
            <span class="badge badge-gray">{{ $purchaseOrders->total() }} total</span>
        </div>

        @if($purchaseOrders->isEmpty())
            <x-admin.empty-state message="No purchase orders yet. Use Restock on the Products page to place an order with admin." />
        @else
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit price</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Placed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseOrders as $order)
                        <tr>
                            <td>{{ $order->request_number }}</td>
                            <td>{{ $order->product?->name ?? '—' }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>{{ format_inr($order->unit_price) }}</td>
                            <td>{{ format_inr($order->total_amount) }}</td>
                            <td>
                                @if($order->status === 'fulfilled')
                                    <span class="badge badge-green">{{ ucfirst($order->status) }}</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge badge-gray">{{ ucfirst($order->status) }}</span>
                                @else
                                    <span class="badge badge-yellow">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <x-admin.pagination :paginator="$purchaseOrders" />
        @endif
    </div>
@endunless
@endsection
