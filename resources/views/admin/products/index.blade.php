@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products')
@section('page-subtitle', 'Plywood catalog with full specifications')

@section('page-header-actions')
    <button type="button" class="btn-add" id="btn-open-add-product" aria-controls="add-product-modal">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-plus"></use></svg>
        <span>Add Product</span>
    </button>
@endsection

@section('content')
@php
    $hasActiveFilters = ($filters['search'] ?? '') !== '';
@endphp

<form class="filters-bar" method="GET" action="{{ route('admin.products.index') }}">
    <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by name, brand, grade, category or distributor..."
            value="{{ $filters['search'] ?? '' }}"
        >
    </div>
    <button type="submit" class="btn-search">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
        <span>Search</span>
    </button>
    @if($hasActiveFilters)
        <a href="{{ route('admin.products.index') }}" class="btn-clear-filters">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            <span>Clear</span>
        </a>
    @endif
</form>

<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Products</p>
        <span class="badge badge-gray">{{ $products->total() }} total</span>
    </div>

    @if($products->isEmpty())
        <x-admin.empty-state :message="$hasActiveFilters ? 'No products match your search.' : 'No products found. Click Add Product to create one.'" />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Thickness</th>
                    <th>Size</th>
                    <th>Grade</th>
                    <th>Category</th>
                    <th>Distributor</th>
                    <th>Stock</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>
                            <button
                                type="button"
                                class="btn-link-table btn-view-product"
                                data-product="{{ e(json_encode($product->only([
                                    'name', 'brand', 'thickness', 'size', 'grade', 'core_type',
                                    'number_of_plies', 'is_standard', 'is_isi_marked', 'warranty',
                                    'finish_surface', 'density', 'termite_borer_treatment',
                                    'weight_per_sheet', 'application', 'glue_type', 'country_of_origin',
                                    'min_order_qty', 'unit', 'in_stock', 'is_featured', 'description',
                                ]) + [
                                    'category' => $product->category?->name,
                                    'distributor' => $product->distributorProfile?->user?->name ?? $product->distributorProfile?->business_name,
                                ])) }}"
                            >
                                {{ $product->name }}
                            </button>
                        </td>
                        <td>{{ $product->brand ?? '—' }}</td>
                        <td>{{ $product->thickness ?? '—' }}</td>
                        <td>{{ $product->size ?? '—' }}</td>
                        <td>{{ $product->grade ?? '—' }}</td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>{{ $product->distributorProfile?->user?->name ?? $product->distributorProfile?->business_name ?? '—' }}</td>
                        <td>
                            @if($product->in_stock)
                                <span class="badge badge-green">In stock</span>
                            @else
                                <span class="badge badge-gray">Out of stock</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <button
                                    type="button"
                                    class="btn-action btn-action-edit btn-quick-edit"
                                    title="Quick edit"
                                    data-product="{{ e(json_encode([
                                        'name' => $product->name,
                                        'category_id' => $product->category_id,
                                        'distributor_profile_id' => $product->distributor_profile_id,
                                        'description' => $product->description,
                                        'thickness' => $product->thickness,
                                        'size' => $product->size,
                                        'grade' => $product->grade,
                                        'core_type' => $product->core_type,
                                        'number_of_plies' => $product->number_of_plies,
                                        'is_standard' => $product->is_standard,
                                        'is_isi_marked' => $product->is_isi_marked ? '1' : '0',
                                        'brand' => $product->brand,
                                        'warranty' => $product->warranty,
                                        'finish_surface' => $product->finish_surface,
                                        'density' => $product->density,
                                        'termite_borer_treatment' => $product->termite_borer_treatment ? '1' : '0',
                                        'weight_per_sheet' => $product->weight_per_sheet,
                                        'application' => $product->application,
                                        'glue_type' => $product->glue_type,
                                        'country_of_origin' => $product->country_of_origin,
                                        'min_order_qty' => $product->min_order_qty,
                                        'unit' => $product->unit,
                                        'in_stock' => $product->in_stock ? '1' : '0',
                                        'is_featured' => $product->is_featured ? '1' : '0',
                                    ])) }}"
                                    data-action="{{ route('admin.products.update', $product) }}"
                                >
                                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-edit"></use></svg>
                                </button>
                                <form
                                    method="POST"
                                    action="{{ route('admin.products.destroy', $product) }}"
                                    class="table-action-form"
                                    onsubmit="return confirm('Delete this product? This cannot be undone.');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    @if($hasActiveFilters)
                                        <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                    @endif
                                    <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-trash"></use></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-admin.pagination :paginator="$products" />
    @endif
</div>

<div class="modal-backdrop" id="product-modal-backdrop" aria-hidden="true"></div>
<div class="modal" id="add-product-modal" role="dialog" aria-modal="true" aria-labelledby="product-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-wide">
        <div class="modal-header">
            <h2 class="modal-title" id="product-modal-title">Add Product</h2>
            <button type="button" class="btn-close-modal" id="btn-close-add-product" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="add-product-form" method="POST" action="{{ route('admin.products.store') }}">
                @csrf
                @include('admin.products._form-fields', ['idPrefix' => 'add_', 'values' => []])
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-add-product">Cancel</button>
            <button type="submit" form="add-product-form" class="btn-submit">Add Product</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="edit-product-modal-backdrop" aria-hidden="true"></div>
<div class="modal" id="edit-product-modal" role="dialog" aria-modal="true" aria-labelledby="edit-product-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-wide">
        <div class="modal-header">
            <h2 class="modal-title" id="edit-product-modal-title">Edit Product</h2>
            <button type="button" class="btn-close-modal" id="btn-close-edit-product" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="edit-product-form" method="POST" action="">
                @csrf
                @method('PUT')
                @if($hasActiveFilters)
                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                @endif
                @include('admin.products._form-fields', ['idPrefix' => 'edit_', 'values' => []])
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-edit-product">Cancel</button>
            <button type="submit" form="edit-product-form" class="btn-submit">Save changes</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="view-product-modal-backdrop" aria-hidden="true"></div>
<div class="modal" id="view-product-modal" role="dialog" aria-modal="true" aria-labelledby="view-product-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-wide">
        <div class="modal-header">
            <h2 class="modal-title" id="view-product-modal-title">Product details</h2>
            <button type="button" class="btn-close-modal" id="btn-close-view-product" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body" id="view-product-card-wrap"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    function bindModal(backdropId, modalId, openBtnId, closeBtnId, cancelBtnId) {
        var backdrop = document.getElementById(backdropId);
        var modal = document.getElementById(modalId);
        var btnOpen = openBtnId ? document.getElementById(openBtnId) : null;
        var btnClose = document.getElementById(closeBtnId);
        var btnCancel = cancelBtnId ? document.getElementById(cancelBtnId) : null;
        if (!backdrop || !modal) return null;

        function openModal() {
            backdrop.classList.add('is-visible');
            modal.classList.add('is-open');
            backdrop.setAttribute('aria-hidden', 'false');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            backdrop.classList.remove('is-visible');
            modal.classList.remove('is-open');
            backdrop.setAttribute('aria-hidden', 'true');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (btnOpen) btnOpen.addEventListener('click', openModal);
        if (btnClose) btnClose.addEventListener('click', closeModal);
        if (btnCancel) btnCancel.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);

        return { openModal: openModal, closeModal: closeModal };
    }

    var addModal = bindModal('product-modal-backdrop', 'add-product-modal', 'btn-open-add-product', 'btn-close-add-product', 'btn-cancel-add-product');
    var editModal = bindModal('edit-product-modal-backdrop', 'edit-product-modal', null, 'btn-close-edit-product', 'btn-cancel-edit-product');
    var viewModal = bindModal('view-product-modal-backdrop', 'view-product-modal', null, 'btn-close-view-product', null);

    function setFieldValue(name, value) {
        var field = document.querySelector('#edit-product-form [name="' + name + '"]');
        if (field) field.value = value ?? '';
    }

    document.querySelectorAll('.btn-quick-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById('edit-product-form');
            if (!form || !editModal) return;

            var data = JSON.parse(btn.getAttribute('data-product') || '{}');
            form.action = btn.getAttribute('data-action');

            Object.keys(data).forEach(function (key) {
                setFieldValue(key, data[key]);
            });

            editModal.openModal();
        });
    });

    document.querySelectorAll('.btn-view-product').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = document.getElementById('view-product-card-wrap');
            if (!wrap || !viewModal) return;

            var data = JSON.parse(btn.getAttribute('data-product') || '{}');
            var title = document.getElementById('view-product-modal-title');
            if (title) title.textContent = data.name || 'Product details';

            wrap.innerHTML =
                '<div class="product-card">' +
                    '<div class="product-card-header"><div><h3 class="product-card-title">' + (data.name || '') + '</h3>' +
                    '<p class="product-card-subtitle">' + (data.brand || '') + ' · ' + (data.category || '') + '</p></div>' +
                    (data.is_featured ? '<span class="badge badge-yellow">Featured</span>' : '') + '</div>' +
                    '<div class="product-card-badges">' +
                        '<span class="badge badge-gray">' + (data.grade || '—') + '</span>' +
                        '<span class="badge badge-gray">' + (data.thickness || '—') + '</span>' +
                        '<span class="badge badge-gray">' + (data.size || '—') + '</span>' +
                        '<span class="badge ' + (data.in_stock ? 'badge-green' : 'badge-gray') + '">' + (data.in_stock ? 'In stock' : 'Out of stock') + '</span>' +
                    '</div>' +
                    '<dl class="product-spec-list">' +
                        specRow('Core type', data.core_type) +
                        specRow('Plies', data.number_of_plies) +
                        specRow('IS standard', data.is_standard) +
                        specRow('ISI marked', data.is_isi_marked ? 'Yes' : 'No') +
                        specRow('Warranty', data.warranty) +
                        specRow('Finish', data.finish_surface) +
                        specRow('Glue type', data.glue_type) +
                        specRow('Density', data.density ? data.density + ' kg/m³' : '—') +
                        specRow('Weight / sheet', data.weight_per_sheet) +
                        specRow('Termite treatment', data.termite_borer_treatment ? 'Yes' : 'No') +
                        specRow('Application', data.application) +
                        specRow('Origin', data.country_of_origin) +
                        specRow('Distributor', data.distributor) +
                        specRow('Min order', (data.min_order_qty || '—') + ' ' + (data.unit || '')) +
                    '</dl>' +
                    (data.description ? '<p class="product-card-description">' + data.description + '</p>' : '') +
                '</div>';

            viewModal.openModal();
        });
    });

    function specRow(label, value) {
        return '<div class="product-spec-item"><dt>' + label + '</dt><dd>' + (value || '—') + '</dd></div>';
    }

    @if($errors->any() && old('name') && !old('_method'))
        if (addModal) addModal.openModal();
    @endif
})();
</script>
@endpush
