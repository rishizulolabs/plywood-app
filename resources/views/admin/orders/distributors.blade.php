@extends('layouts.admin')

@section('title', 'Distributor Orders')
@section('page-title', 'Distributor Orders')
@section('page-subtitle', 'Restock requests from distributors')

@section('content')
@php
    $restockStatusLabels = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'fulfilled' => 'Fulfilled',
        'cancelled' => 'Cancelled',
    ];
    $restockStatusClasses = [
        'pending' => 'status-btn-pending',
        'approved' => 'status-btn-dispatched',
        'fulfilled' => 'status-btn-approved',
        'cancelled' => 'status-btn-cancelled',
    ];
@endphp

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
        <p class="content-card-title">Distributor restock orders</p>
        <span class="badge badge-gray">{{ $restockRequests->total() }} total</span>
    </div>

    @if($restockRequests->isEmpty())
        <x-admin.empty-state message="No distributor restock orders yet." />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Distributor</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit price</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Requested</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($restockRequests as $restockRequest)
                    @php
                        $currentStatusClass = $restockStatusClasses[$restockRequest->status] ?? 'status-btn-pending';
                    @endphp
                    <tr>
                        <td>{{ $restockRequest->request_number }}</td>
                        <td>{{ $restockRequest->distributorProfile?->user?->name ?? $restockRequest->distributorProfile?->business_name ?? '—' }}</td>
                        <td>{{ $restockRequest->product?->name ?? '—' }}</td>
                        <td>{{ $restockRequest->quantity }}</td>
                        <td>{{ format_inr($restockRequest->unit_price) }}</td>
                        <td>{{ format_inr($restockRequest->total_amount) }}</td>
                        <td>
                            <div class="status-dropdown">
                                <button
                                    type="button"
                                    class="status-dropdown-trigger status-btn {{ $currentStatusClass }} is-active"
                                    aria-haspopup="listbox"
                                    aria-expanded="false"
                                >
                                    <span>{{ $restockStatusLabels[$restockRequest->status] ?? ucfirst($restockRequest->status) }}</span>
                                    <svg class="icon-svg status-dropdown-chevron" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                                </button>
                                <div class="status-dropdown-menu" role="listbox" hidden>
                                    @foreach ($restockStatusLabels as $statusValue => $statusLabel)
                                        <form method="POST" action="{{ route('admin.distributor-orders.status', $restockRequest) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $statusValue }}">
                                            <button
                                                type="submit"
                                                class="status-dropdown-option status-btn {{ $restockStatusClasses[$statusValue] }} {{ $restockRequest->status === $statusValue ? 'is-selected' : '' }}"
                                                {{ $restockRequest->status === $statusValue ? 'disabled' : '' }}
                                            >
                                                {{ $statusLabel }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                        <td>{{ $restockRequest->created_at?->format('d M Y') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-admin.pagination :paginator="$restockRequests" />
    @endif
</div>
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
