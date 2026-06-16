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
    $hasActiveFilters = ($filters['search'] ?? '') !== '' || ($filters['status'] ?? '') !== '';
@endphp

<div class="stat-cards stat-cards-4">
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

<form class="filters-bar" method="GET" action="{{ route('admin.distributor-orders.index') }}">
    <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by request #, distributor or product..."
            value="{{ $filters['search'] ?? '' }}"
        >
    </div>
    <div class="filter-group">
        <label class="filter-label" for="restock-status-filter">Status</label>
        <select id="restock-status-filter" name="status" class="form-select form-select-compact">
            <option value="">All statuses</option>
            @foreach ($restockStatusLabels as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(($filters['status'] ?? '') === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-search">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
        <span>Search</span>
    </button>
    @if($hasActiveFilters)
        <a href="{{ route('admin.distributor-orders.index') }}" class="btn-clear-filters">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            <span>Clear</span>
        </a>
    @endif
</form>

<div class="content-card space-y">
    <div class="content-card-header">
        <div class="content-card-heading">
            <p class="content-card-title">Distributor restock orders</p>
            @if($hasActiveFilters)
                <p class="content-card-subtitle">Filtered results</p>
            @endif
        </div>
        <span class="badge badge-gray">{{ $restockRequests->total() }} total</span>
    </div>

    @if($restockRequests->isEmpty())
        <x-admin.empty-state :message="$hasActiveFilters ? 'No restock orders match your filters. Try different search terms or clear filters.' : 'No distributor restock orders yet.'" />
    @else
        <div class="table-responsive table-responsive--wide">
            <table class="data-table data-table-restock-orders">
                <thead>
                    <tr>
                        <th>Request #</th>
                        <th>Distributor</th>
                        <th>Product</th>
                        <th>Qty</th>
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
                            $distributorName = $restockRequest->distributorProfile?->user?->name
                                ?? $restockRequest->distributorProfile?->business_name
                                ?? '—';
                            $distributorEmail = $restockRequest->distributorProfile?->user?->email;
                        @endphp
                        <tr>
                            <td class="cell-nowrap">
                                <span class="cell-request-id">{{ $restockRequest->request_number }}</span>
                            </td>
                            <td>
                                <div class="table-cell-stack">
                                    <span class="table-cell-primary">{{ $distributorName }}</span>
                                    @if($distributorEmail)
                                        <span class="table-cell-meta">{{ $distributorEmail }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-stack">
                                    <span class="table-cell-primary">{{ $restockRequest->product?->name ?? '—' }}</span>
                                    @if($restockRequest->product?->category?->name)
                                        <span class="table-cell-meta">{{ $restockRequest->product->category->name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="cell-nowrap cell-qty">{{ $restockRequest->quantity }}</td>
                            <td class="cell-nowrap">{{ format_inr($restockRequest->unit_price) }}</td>
                            <td class="cell-nowrap cell-total">{{ format_inr($restockRequest->total_amount) }}</td>
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
                                                @if(($filters['search'] ?? '') !== '')
                                                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                                @endif
                                                @if(($filters['status'] ?? '') !== '')
                                                    <input type="hidden" name="filter_status" value="{{ $filters['status'] }}">
                                                @endif
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
                            <td class="cell-nowrap">
                                <div class="table-cell-stack">
                                    <span class="table-cell-primary">{{ $restockRequest->created_at?->format('d M Y') ?? '—' }}</span>
                                    @if($restockRequest->created_at)
                                        <span class="table-cell-meta">{{ $restockRequest->created_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
