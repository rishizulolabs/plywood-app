@extends('layouts.admin')

@section('title', 'Analytics')

@section('page-heading')
<div class="admin-page-heading">
    <p class="admin-page-eyebrow">Reports</p>
    <h1>Analytics</h1>
    <p class="admin-page-subtitle">Distributor purchase orders, product stock, and recent activity.</p>
</div>
@endsection

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

    $distributorName = fn ($profile) => $profile?->business_name ?: ($profile?->user?->name ?? '—');
@endphp

<div class="analytics-page">
    <div class="content-card analytics-selector-card">
        <div class="analytics-selector-header">
            <div>
                <p class="analytics-selector-eyebrow">Filter by distributor</p>
                <p class="analytics-selector-scope">{{ $scopeLabel }}</p>
            </div>
            <span class="badge badge-gray">{{ $distributors->count() }} distributors</span>
        </div>
        <form class="analytics-selector-form" method="GET" action="{{ route('admin.analytics.index') }}" id="analytics-distributor-form">
            <div class="analytics-selector-row">
                <div class="analytics-select-wrap">
                    <svg class="analytics-select-icon icon-svg" aria-hidden="true"><use href="#icon-users"></use></svg>
                    <select id="analytics-distributor" name="distributor" class="form-select analytics-distributor-select">
                        <option value="all" @selected($selectedFilter === 'all')>All distributors</option>
                        @foreach ($distributors as $distributor)
                            <option value="{{ $distributor['id'] }}" @selected($selectedFilter === (string) $distributor['id'])>
                                {{ $distributor['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="analytics-summary-grid">
        @if($isAll)
            <div class="analytics-summary-card analytics-summary-card-blue">
                <span class="analytics-summary-icon analytics-summary-icon-blue">
                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-users"></use></svg>
                </span>
                <div>
                    <span class="analytics-summary-label">Distributors</span>
                    <span class="analytics-summary-value">{{ $summary['distributors_count'] }}</span>
                    <span class="analytics-summary-meta">Included in this view</span>
                </div>
            </div>
        @endif
        <div class="analytics-summary-card analytics-summary-card-amber">
            <span class="analytics-summary-icon analytics-summary-icon-amber">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-package"></use></svg>
            </span>
            <div>
                <span class="analytics-summary-label">{{ $isAll ? 'Total purchase value' : 'Purchase value' }}</span>
                <span class="analytics-summary-value analytics-summary-value-currency">{{ format_inr($summary['restock_orders_total']) }}</span>
                <span class="analytics-summary-meta">
                    @if($isAll)
                        {{ $summary['restock_orders_count'] }} purchase order{{ $summary['restock_orders_count'] === 1 ? '' : 's' }} · all distributors
                    @else
                        {{ $summary['restock_orders_count'] }} purchase order{{ $summary['restock_orders_count'] === 1 ? '' : 's' }} · {{ $scopeLabel }}
                    @endif
                </span>
            </div>
        </div>
        <div class="analytics-summary-card analytics-summary-card-purple">
            <span class="analytics-summary-icon analytics-summary-icon-purple">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-database"></use></svg>
            </span>
            <div>
                <span class="analytics-summary-label">Products</span>
                <span class="analytics-summary-value">{{ $summary['products_count'] }}</span>
                <span class="analytics-summary-meta">{{ $summary['total_stock'] }} units in stock</span>
            </div>
        </div>
        <div class="analytics-summary-card analytics-summary-card-green">
            <span class="analytics-summary-icon analytics-summary-icon-green">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-shopping-cart"></use></svg>
            </span>
            <div>
                <span class="analytics-summary-label">Customer orders</span>
                <span class="analytics-summary-value">{{ $summary['customer_orders_count'] }}</span>
                <span class="analytics-summary-meta">Placed by customers</span>
            </div>
        </div>
    </div>

    <div class="content-card analytics-section-card">
        <div class="analytics-section-header">
            <div>
                <p class="analytics-section-title">Products &amp; quantity</p>
                <p class="analytics-section-subtitle">Allotted products for {{ $scopeLabel }}</p>
            </div>
            <span class="badge badge-gray">{{ $products->count() }} products</span>
        </div>

        @if($products->isEmpty())
            <p class="analytics-panel-empty">No products found for {{ $scopeLabel }}.</p>
        @else
            <div class="table-responsive">
                <table class="data-table analytics-data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            @if($isAll)
                                <th>Distributor</th>
                            @endif
                            <th>Category</th>
                            <th>Grade</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th class="th-price">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td><span class="analytics-product-name">{{ $product->name }}</span></td>
                                @if($isAll)
                                    <td>{{ $distributorName($product->distributorProfile ?? null) }}</td>
                                @endif
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>{{ $product->grade ?? '—' }}</td>
                                <td class="cell-nowrap">{{ $product->size ?? '—' }}</td>
                                <td>{{ (int) ($product->pivot->stock_quantity ?? 0) }}</td>
                                <td class="analytics-price">{{ format_inr($product->pivot->price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="content-card analytics-section-card">
        <div class="analytics-section-header">
            <div>
                <p class="analytics-section-title">Recent customer orders</p>
                <p class="analytics-section-subtitle">Last 5 orders placed by customers for {{ $scopeLabel }}</p>
            </div>
            @if($customerOrdersCount > 5)
                <a href="{{ $viewAllCustomerOrdersUrl }}" class="btn-modal analytics-view-all-btn">
                    View all ({{ $customerOrdersCount }})
                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                </a>
            @elseif($customerOrdersCount > 0)
                <a href="{{ $viewAllCustomerOrdersUrl }}" class="btn-modal analytics-view-all-btn">
                    View all
                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                </a>
            @endif
        </div>

        @if($recentCustomerOrders->isEmpty())
            <p class="analytics-panel-empty">No customer orders found for {{ $scopeLabel }}.</p>
        @else
            <div class="table-responsive">
                <table class="data-table analytics-data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Product</th>
                            @if($isAll)
                                <th>Distributor</th>
                            @endif
                            <th class="th-price">Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentCustomerOrders as $customerOrder)
                            @php
                                $items = $customerOrder->inquiry?->items ?? collect();
                                $productLabel = $items->map(fn ($item) => ($item->product?->name ?? 'Product').' × '.$item->quantity)->join(', ');
                            @endphp
                            <tr>
                                <td class="cell-nowrap">{{ $customerOrder->order_number }}</td>
                                <td>{{ $customerOrder->customer?->name ?? '—' }}</td>
                                <td>{{ $productLabel ?: '—' }}</td>
                                @if($isAll)
                                    <td>{{ $distributorName($customerOrder->distributorProfile) }}</td>
                                @endif
                                <td class="analytics-price">{{ format_inr($customerOrder->total_amount) }}</td>
                                <td><span class="badge badge-gray">{{ ucfirst($customerOrder->payment_status) }}</span></td>
                                <td>
                                    <span class="status-btn {{ $customerOrder->fulfillment_status === 'delivered' ? 'status-btn-approved' : ($customerOrder->fulfillment_status === 'cancelled' ? 'status-btn-cancelled' : 'status-btn-pending') }}">
                                        {{ ucfirst($customerOrder->fulfillment_status) }}
                                    </span>
                                </td>
                                <td class="cell-nowrap">{{ $customerOrder->created_at?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="content-card analytics-section-card">
        <div class="analytics-section-header">
            <div>
                <p class="analytics-section-title">Recent purchase orders</p>
                <p class="analytics-section-subtitle">Last 5 restock orders for {{ $scopeLabel }}</p>
            </div>
            @if($restockOrdersCount > 5)
                <a href="{{ $viewAllOrdersUrl }}" class="btn-modal analytics-view-all-btn">
                    View all ({{ $restockOrdersCount }})
                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                </a>
            @elseif($restockOrdersCount > 0)
                <a href="{{ $viewAllOrdersUrl }}" class="btn-modal analytics-view-all-btn">
                    View all
                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                </a>
            @endif
        </div>

        @if($recentRestockOrders->isEmpty())
            <p class="analytics-panel-empty">No purchase orders found for {{ $scopeLabel }}.</p>
        @else
            <div class="table-responsive">
                <table class="data-table analytics-data-table">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            @if($isAll)
                                <th>Distributor</th>
                            @endif
                            <th>Product</th>
                            <th>Qty</th>
                            <th class="th-price">Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentRestockOrders as $restockOrder)
                            @php
                                $restockDistributor = $distributorName($restockOrder->distributorProfile);
                                $restockOrderDetail = [
                                    'type' => 'restock_order',
                                    'title' => $restockOrder->request_number,
                                    'fields' => array_values(array_filter([
                                        $isAll ? ['label' => 'Distributor', 'value' => $restockDistributor] : null,
                                        ['label' => 'Product', 'value' => $restockOrder->product?->name],
                                        ['label' => 'Category', 'value' => $restockOrder->product?->category?->name],
                                        ['label' => 'Quantity', 'value' => (string) $restockOrder->quantity],
                                        ['label' => 'Unit price', 'value' => format_inr($restockOrder->unit_price)],
                                        ['label' => 'Total amount', 'value' => format_inr($restockOrder->total_amount)],
                                        ['label' => 'Status', 'value' => $restockStatusLabels[$restockOrder->status] ?? ucfirst($restockOrder->status)],
                                        ['label' => 'Requested on', 'value' => $restockOrder->created_at?->format('d M Y, h:i A')],
                                    ], fn ($field) => $field !== null)),
                                ];
                            @endphp
                            <tr>
                                <td class="cell-nowrap">{{ $restockOrder->request_number }}</td>
                                @if($isAll)
                                    <td>{{ $restockDistributor }}</td>
                                @endif
                                <td>{{ $restockOrder->product?->name ?? '—' }}</td>
                                <td>{{ $restockOrder->quantity }}</td>
                                <td class="analytics-price">{{ format_inr($restockOrder->total_amount) }}</td>
                                <td>
                                    <span class="status-btn {{ $restockStatusClasses[$restockOrder->status] ?? 'status-btn-pending' }}">
                                        {{ $restockStatusLabels[$restockOrder->status] ?? ucfirst($restockOrder->status) }}
                                    </span>
                                </td>
                                <td class="cell-nowrap">{{ $restockOrder->created_at?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="btn-action btn-action-view analytics-view-order-btn"
                                            title="View order details"
                                            data-detail='@json($restockOrderDetail)'
                                        >
                                            <svg class="icon-svg" aria-hidden="true"><use href="#icon-eye"></use></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="modal-backdrop" id="analytics-order-modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="analytics-order-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="analytics-order-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-wide">
        <div class="modal-header">
            <h2 class="modal-title" id="analytics-order-modal-title">Purchase order details</h2>
            <button type="button" class="btn-close-modal" id="btn-close-analytics-order-modal" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body" id="analytics-order-modal-content"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('analytics-distributor-form');
    var select = document.getElementById('analytics-distributor');

    if (form && select) {
        select.addEventListener('change', function () {
            form.submit();
        });
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function isStatusField(label) {
        var normalized = String(label || '').toLowerCase();
        return normalized === 'status';
    }

    function isAmountField(label) {
        var normalized = String(label || '').toLowerCase();
        return normalized.indexOf('amount') !== -1 || normalized === 'price' || normalized === 'unit price';
    }

    function statusBadgeClass(value) {
        var normalized = String(value || '').toLowerCase();

        if (normalized.indexOf('pending') !== -1) {
            return 'analytics-status-pending';
        }
        if (normalized.indexOf('cancel') !== -1) {
            return 'analytics-status-danger';
        }
        if (normalized.indexOf('fulfilled') !== -1) {
            return 'analytics-status-success';
        }
        if (normalized.indexOf('approved') !== -1) {
            return 'analytics-status-info';
        }

        return 'analytics-status-neutral';
    }

    function renderFieldValue(field) {
        var value = field.value === null || field.value === undefined || field.value === ''
            ? '—'
            : field.value;

        if (value === '—') {
            return '<span class="analytics-detail-value-empty">—</span>';
        }

        if (isStatusField(field.label)) {
            return '<span class="analytics-status-badge ' + statusBadgeClass(value) + '">' + escapeHtml(value) + '</span>';
        }

        if (isAmountField(field.label)) {
            return '<span class="analytics-detail-value-amount">' + escapeHtml(value) + '</span>';
        }

        return '<span class="analytics-detail-value">' + escapeHtml(value) + '</span>';
    }

    function renderOrderDetail(data) {
        var fields = data.fields || [];
        var highlightField = fields.find(function (field) {
            return isAmountField(field.label) && String(field.label).toLowerCase().indexOf('total') !== -1;
        }) || fields.find(function (field) {
            return isAmountField(field.label);
        });

        var detailFields = fields.filter(function (field) {
            return !highlightField || field.label !== highlightField.label;
        });

        var fieldsHtml = detailFields.map(function (field) {
            return '<div class="analytics-detail-field">' +
                '<dt>' + escapeHtml(field.label) + '</dt>' +
                '<dd>' + renderFieldValue(field) + '</dd>' +
                '</div>';
        }).join('');

        var highlightHtml = highlightField
            ? '<div class="analytics-detail-highlight">' +
                '<span class="analytics-detail-highlight-label">' + escapeHtml(highlightField.label) + '</span>' +
                '<span class="analytics-detail-highlight-value">' + escapeHtml(highlightField.value || '—') + '</span>' +
              '</div>'
            : '';

        return '<div class="analytics-detail-hero analytics-detail-hero-amber">' +
                '<span class="analytics-detail-hero-icon">' +
                    '<svg class="icon-svg" aria-hidden="true"><use href="#icon-package"></use></svg>' +
                '</span>' +
                '<div class="analytics-detail-hero-body">' +
                    '<span class="analytics-detail-type">Purchase order</span>' +
                    '<h3 class="analytics-detail-title">' + escapeHtml(data.title || 'Details') + '</h3>' +
                '</div>' +
            '</div>' +
            highlightHtml +
            '<dl class="analytics-detail-grid">' + fieldsHtml + '</dl>';
    }

    var backdrop = document.getElementById('analytics-order-modal-backdrop');
    var modal = document.getElementById('analytics-order-modal');
    var modalContent = document.getElementById('analytics-order-modal-content');
    var modalTitle = document.getElementById('analytics-order-modal-title');
    var btnClose = document.getElementById('btn-close-analytics-order-modal');

    function openOrderModal(data) {
        if (!backdrop || !modal || !modalContent) return;

        if (modalTitle) {
            modalTitle.textContent = data.title ? data.title + ' — Details' : 'Purchase order details';
        }

        modalContent.innerHTML = renderOrderDetail(data);
        backdrop.classList.add('is-visible');
        modal.classList.add('is-open');
        backdrop.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeOrderModal() {
        if (!backdrop || !modal) return;

        backdrop.classList.remove('is-visible');
        modal.classList.remove('is-open');
        backdrop.setAttribute('aria-hidden', 'true');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (btnClose) btnClose.addEventListener('click', closeOrderModal);
    if (backdrop) backdrop.addEventListener('click', closeOrderModal);

    document.querySelectorAll('.analytics-view-order-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var rawDetail = button.getAttribute('data-detail') || '{}';

            try {
                openOrderModal(JSON.parse(rawDetail));
            } catch (error) {
                openOrderModal({ title: 'Error', fields: [] });
            }
        });
    });
})();
</script>
@endpush
