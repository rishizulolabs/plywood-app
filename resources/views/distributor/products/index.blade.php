@extends('layouts.distributor')

@section('title', 'Products')
@section('page-title', 'Products')
@section('page-subtitle', 'Products assigned to your account with pricing')

@section('content')
<form class="filters-bar" method="GET" action="{{ route('distributor.products.index') }}">
    <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by name, brand, grade, thickness or category..."
            value="{{ $search }}"
        >
    </div>
    <button type="submit" class="btn-search">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
        <span>Search</span>
    </button>
    @if($search !== '')
        <a href="{{ route('distributor.products.index') }}" class="btn-clear-filters">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            <span>Clear</span>
        </a>
    @endif
</form>

<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Assigned products</p>
        <span class="badge badge-gray">{{ $products->total() }} assigned</span>
    </div>

    @if($products->isEmpty())
        <x-admin.empty-state :message="$search !== '' ? 'No assigned products match your search.' : 'No products assigned yet. Contact admin to add products and pricing to your account.'" />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th-image">Image</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Total quantity</th>
                    <th>Customer order</th>
                    <th>Balance</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    @php
                        $totalQuantity = (int) ($product->pivot->stock_quantity ?? 0);
                        $customerOrder = (int) ($customerOrderTotals[$product->id] ?? 0);
                        $balance = $totalQuantity - $customerOrder;
                    @endphp
                    <tr>
                        <td class="td-image">
                            @if($productImage = $product->getFirstMediaUrl('product_image') ?: $product->getFirstMediaUrl('thumbnails'))
                                <img src="{{ $productImage }}" alt="{{ $product->name }}" class="table-product-image">
                            @else
                                <span class="table-product-image-placeholder" aria-hidden="true"></span>
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->brand ?? '—' }}</td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>{{ format_inr($product->pivot->price) }}</td>
                        <td>{{ $totalQuantity }}</td>
                        <td>{{ $customerOrder }}</td>
                        <td>
                            @if($balance < 0)
                                <span class="badge badge-yellow">{{ $balance }}</span>
                            @else
                                {{ $balance }}
                            @endif
                        </td>
                        <td>
                            <button
                                type="button"
                                class="btn-restock btn-open-restock"
                                data-product-name="{{ $product->name }}"
                                data-action="{{ route('distributor.products.restock', $product) }}"
                                data-min-qty="{{ max(1, $product->min_order_qty) }}"
                            >
                                Restock
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <x-admin.pagination :paginator="$products" />
    @endif
</div>

<div class="modal-backdrop" id="restock-modal-backdrop" aria-hidden="true"></div>
<div class="modal" id="restock-modal" role="dialog" aria-modal="true" aria-labelledby="restock-modal-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title" id="restock-modal-title">Restock product</h2>
            <button type="button" class="btn-close-modal" id="btn-close-restock" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <form method="POST" action="" id="restock-form">
            @csrf
            @if($search !== '')
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <div class="modal-body">
                <p class="form-helper" id="restock-product-name"></p>
                <div class="form-group">
                    <label class="form-label" for="restock-quantity">Quantity <span class="required">*</span></label>
                    <input
                        type="number"
                        id="restock-quantity"
                        name="quantity"
                        class="form-input"
                        min="1"
                        value="1"
                        required
                    >
                    <p class="form-helper">Enter how many units you need. Admin will receive your restock order.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btn-cancel-restock">Cancel</button>
                <button type="submit" class="btn-submit">Place order</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var backdrop = document.getElementById('restock-modal-backdrop');
    var modal = document.getElementById('restock-modal');
    var form = document.getElementById('restock-form');
    var productName = document.getElementById('restock-product-name');
    var quantityInput = document.getElementById('restock-quantity');
    var btnClose = document.getElementById('btn-close-restock');
    var btnCancel = document.getElementById('btn-cancel-restock');

    if (!backdrop || !modal || !form) return;

    function openModal(action, name, minQty) {
        form.action = action;
        if (productName) {
            productName.textContent = 'Product: ' + (name || '');
        }
        if (quantityInput) {
            quantityInput.min = String(minQty || 1);
            quantityInput.value = String(minQty || 1);
        }
        backdrop.classList.add('is-visible');
        modal.classList.add('is-open');
        backdrop.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (quantityInput) quantityInput.focus();
    }

    function closeModal() {
        backdrop.classList.remove('is-visible');
        modal.classList.remove('is-open');
        backdrop.setAttribute('aria-hidden', 'true');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-open-restock').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openModal(
                btn.getAttribute('data-action') || '',
                btn.getAttribute('data-product-name') || '',
                parseInt(btn.getAttribute('data-min-qty') || '1', 10)
            );
        });
    });

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
})();
</script>
@endpush
