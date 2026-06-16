@extends('layouts.distributor')

@section('title', 'Customer Orders')
@section('page-title', 'Customer Orders')
@section('page-subtitle', 'Orders placed by customers')

@section('content')
@php
    $fulfillmentStatuses = [
        'processing' => ['label' => 'Processing', 'class' => 'status-btn-pending'],
        'dispatched' => ['label' => 'Dispatched', 'class' => 'status-btn-dispatched'],
        'delivered' => ['label' => 'Delivered', 'class' => 'status-btn-approved'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'status-btn-cancelled'],
    ];
@endphp

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
            <p class="content-card-title">Customer orders</p>
            <span class="badge badge-gray">{{ $orders->total() }} total</span>
        </div>

        @if($orders->isEmpty())
            <x-admin.empty-state message="No orders yet. Customer orders will appear here after checkout." />
        @else
            <div class="table-responsive">
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
                            $currentStatus = $fulfillmentStatuses[$order->fulfillment_status] ?? [
                                'label' => ucfirst($order->fulfillment_status),
                                'class' => 'status-btn-pending',
                            ];
                        @endphp
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer?->name ?? '—' }}</td>
                            <td>{{ $productLabel ?: '—' }}</td>
                            <td>{{ format_inr($order->total_amount) }}</td>
                            <td><span class="badge badge-gray">{{ ucfirst($order->payment_status) }}</span></td>
                            <td>
                                <div class="status-dropdown">
                                    <button
                                        type="button"
                                        class="status-dropdown-trigger status-btn {{ $currentStatus['class'] }} is-active"
                                        aria-haspopup="listbox"
                                        aria-expanded="false"
                                    >
                                        <span>{{ $currentStatus['label'] }}</span>
                                        <svg class="icon-svg status-dropdown-chevron" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                                    </button>
                                    <div class="status-dropdown-menu" role="listbox" hidden>
                                        @foreach ($fulfillmentStatuses as $value => $status)
                                            <form method="POST" action="{{ route('distributor.orders.status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="fulfillment_status" value="{{ $value }}">
                                                <button
                                                    type="submit"
                                                    class="status-dropdown-option status-btn {{ $status['class'] }} {{ $order->fulfillment_status === $value ? 'is-selected' : '' }}"
                                                    {{ $order->fulfillment_status === $value ? 'disabled' : '' }}
                                                >
                                                    {{ $status['label'] }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <x-admin.pagination :paginator="$orders" />
        @endif
    </div>
@endunless
@endsection

@push('scripts')
<script>
(function () {
    function resetStatusMenu(menu) {
        menu.hidden = true;
        menu.style.position = '';
        menu.style.top = '';
        menu.style.left = '';
        menu.style.minWidth = '';
        menu.classList.remove('is-open-above');
    }

    function closeAllStatusDropdowns() {
        document.querySelectorAll('.status-dropdown.is-open').forEach(function (dropdown) {
            dropdown.classList.remove('is-open');
            var menu = dropdown.querySelector('.status-dropdown-menu');
            var trigger = dropdown.querySelector('.status-dropdown-trigger');
            if (menu) resetStatusMenu(menu);
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    }

    function positionStatusMenu(trigger, menu) {
        var rect = trigger.getBoundingClientRect();

        menu.hidden = false;
        menu.style.position = 'fixed';
        menu.style.left = rect.left + 'px';
        menu.style.minWidth = Math.max(rect.width, 152) + 'px';
        menu.style.top = (rect.bottom + 6) + 'px';

        var menuHeight = menu.offsetHeight;
        var spaceBelow = window.innerHeight - rect.bottom;
        var openAbove = spaceBelow < menuHeight + 12;

        menu.style.top = openAbove
            ? (rect.top - menuHeight - 6) + 'px'
            : (rect.bottom + 6) + 'px';
        menu.classList.toggle('is-open-above', openAbove);
    }

    document.querySelectorAll('.status-dropdown').forEach(function (dropdown) {
        var trigger = dropdown.querySelector('.status-dropdown-trigger');
        var menu = dropdown.querySelector('.status-dropdown-menu');
        if (!trigger || !menu) return;

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = dropdown.classList.contains('is-open');
            closeAllStatusDropdowns();
            if (!isOpen) {
                dropdown.classList.add('is-open');
                positionStatusMenu(trigger, menu);
                trigger.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.addEventListener('click', closeAllStatusDropdowns);
    window.addEventListener('resize', closeAllStatusDropdowns);
    window.addEventListener('scroll', closeAllStatusDropdowns, true);
})();
</script>
@endpush
