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
            placeholder="Search by name, brand, grade, or category..."
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
        <div class="table-responsive table-responsive--products">
        <table class="data-table data-table-products">
            <thead>
                <tr>
                    <th class="th-image">Image</th>
                    <th>Name</th>
                    <th>Thickness</th>
                    <th class="th-size">Size</th>
                    <th>Grade</th>
                    <th>Category</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td class="td-image">
                            @if($productImage = $product->getFirstMediaUrl('product_image') ?: $product->getFirstMediaUrl('thumbnails'))
                                <img src="{{ $productImage }}" alt="{{ $product->name }}" class="table-product-image">
                            @else
                                <span class="table-product-image-placeholder" aria-hidden="true"></span>
                            @endif
                        </td>
                        <td>
                            <button
                                type="button"
                                class="btn-link-table btn-view-product"
                                data-product="{{ e(json_encode($product->only([
                                    'name', 'brand', 'thickness', 'size', 'grade', 'core_type',
                                    'number_of_plies', 'is_standard', 'warranty',
                                    'finish_surface', 'density', 'termite_borer_treatment',
                                    'weight_per_sheet', 'application', 'glue_type', 'country_of_origin',
                                    'min_order_qty', 'unit', 'in_stock', 'is_featured', 'description',
                                ]) + [
                                    'category' => $product->category?->name,
                                    'product_image' => $product->getFirstMediaUrl('product_image'),
                                    'thumbnails' => $product->getMedia('thumbnails')->map(fn ($media) => $media->getUrl())->values(),
                                ])) }}"
                            >
                                {{ $product->name }}
                            </button>
                        </td>
                        <td>{{ $product->thickness ?? '—' }}</td>
                        <td class="td-size"><x-admin.size-cell :size="$product->size" /></td>
                        <td>{{ $product->grade ?? '—' }}</td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>
                            <div class="table-actions">
                                <button
                                    type="button"
                                    class="btn-action btn-action-edit btn-quick-edit"
                                    title="Quick edit"
                                    data-action="{{ route('admin.products.update', $product) }}"
                                    data-name="{{ $product->name }}"
                                    data-category-id="{{ $product->category_id }}"
                                    data-description="{{ $product->description ?? '' }}"
                                    data-thickness="{{ $product->thickness ?? '' }}"
                                    data-size="{{ $product->size ?? '' }}"
                                    data-grade="{{ $product->grade ?? '' }}"
                                    data-product-image="{{ $product->getFirstMediaUrl('product_image') }}"
                                    data-thumbnails="{{ e(json_encode($product->getMedia('thumbnails')->map(fn ($media) => $media->getUrl())->values())) }}"
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
        </div>
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
            <form class="modal-form" id="add-product-form" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
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
            <form class="modal-form" id="edit-product-form" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if($hasActiveFilters)
                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                @endif
                @include('admin.products._form-fields', ['idPrefix' => 'edit_', 'values' => [], 'showMediaPreviews' => true])
                <input type="hidden" name="_edit_product_id" id="edit-product-id" value="{{ old('_edit_product_id') }}">
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
        if (!field) return;
        field.value = value ?? '';
    }

    function clearFilePreviews(prefix) {
        ['product_image_preview', 'thumbnails_preview'].forEach(function (suffix) {
            var el = document.getElementById(prefix + suffix);
            if (!el) return;
            el.innerHTML = '';
            el.hidden = true;
        });
    }

    function bindFilePreview(inputId, previewId, multiple) {
        var input = document.getElementById(inputId);
        var preview = document.getElementById(previewId);
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            preview.innerHTML = '';

            if (!input.files || !input.files.length) {
                preview.hidden = true;
                return;
            }

            if (multiple) {
                var grid = document.createElement('div');
                grid.className = 'product-media-preview-grid';

                Array.from(input.files).forEach(function (file) {
                    if (!file.type.startsWith('image/')) return;

                    var img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    img.className = 'product-media-preview-thumb';
                    img.onload = function () { URL.revokeObjectURL(img.src); };
                    grid.appendChild(img);
                });

                if (!grid.children.length) {
                    preview.hidden = true;
                    return;
                }

                var helper = document.createElement('p');
                helper.className = 'form-helper';
                helper.textContent = input.files.length > 1 ? 'Selected images' : 'Selected image';
                preview.appendChild(helper);
                preview.appendChild(grid);
            } else {
                var file = input.files[0];
                if (!file.type.startsWith('image/')) {
                    preview.hidden = true;
                    return;
                }

                var helper = document.createElement('p');
                helper.className = 'form-helper';
                helper.textContent = 'Selected image';

                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = file.name;
                img.className = 'product-media-preview-image';
                img.onload = function () { URL.revokeObjectURL(img.src); };

                preview.appendChild(helper);
                preview.appendChild(img);
            }

            preview.hidden = false;
        });
    }

    bindFilePreview('add_product_image', 'add_product_image_preview', false);
    bindFilePreview('add_thumbnails', 'add_thumbnails_preview', true);
    bindFilePreview('edit_product_image', 'edit_product_image_preview', false);
    bindFilePreview('edit_thumbnails', 'edit_thumbnails_preview', true);

    function populateEditForm(btn) {
        var form = document.getElementById('edit-product-form');
        if (!form || !editModal) return;

        form.action = btn.getAttribute('data-action') || '';
        var productIdInput = document.getElementById('edit-product-id');
        var actionMatch = (form.action || '').match(/\/products\/(\d+)\/?$/);
        if (productIdInput && actionMatch) {
            productIdInput.value = actionMatch[1];
        }
        setFieldValue('name', btn.getAttribute('data-name'));
        setFieldValue('category_id', btn.getAttribute('data-category-id'));
        setFieldValue('description', btn.getAttribute('data-description'));
        setFieldValue('thickness', btn.getAttribute('data-thickness'));
        setFieldValue('size', btn.getAttribute('data-size'));
        setFieldValue('grade', btn.getAttribute('data-grade'));
        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            input.value = '';
        });
        clearFilePreviews('edit_');
        setImagePreviews(btn);

        editModal.openModal();
    }

    function setImagePreviews(btn) {
        var wrap = document.getElementById('edit-image-previews');
        if (!wrap) return;

        var productImage = btn.getAttribute('data-product-image') || '';
        var thumbnails = [];

        try {
            thumbnails = JSON.parse(btn.getAttribute('data-thumbnails') || '[]');
        } catch (error) {
            thumbnails = [];
        }

        var html = '';

        if (productImage) {
            html += '<div class="product-media-preview">' +
                '<p class="form-helper">Current product image</p>' +
                '<img src="' + productImage + '" alt="Product image" class="product-media-preview-image">' +
                '</div>';
        }

        if (thumbnails.length) {
            html += '<div class="product-media-preview">' +
                '<p class="form-helper">Current thumbnails</p>' +
                '<div class="product-media-preview-grid">';

            thumbnails.forEach(function (url) {
                html += '<img src="' + url + '" alt="Thumbnail" class="product-media-preview-thumb">';
            });

            html += '</div></div>';
        }

        wrap.innerHTML = html;
    }

    document.querySelectorAll('.btn-quick-edit').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            populateEditForm(btn);
        });
    });

    document.querySelectorAll('#add-product-modal select, #edit-product-modal select').forEach(function (select) {
        select.addEventListener('mousedown', function () {
            var body = select.closest('.modal-body');
            if (!body) return;

            window.requestAnimationFrame(function () {
                select.scrollIntoView({ block: 'center', behavior: 'smooth' });
            });
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
                (data.product_image ? '<div class="product-media-preview"><img src="' + data.product_image + '" alt="' + (data.name || 'Product') + '" class="product-media-preview-image"></div>' : '') +
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
                        specRow('Warranty', data.warranty) +
                        specRow('Finish', data.finish_surface) +
                        specRow('Glue type', data.glue_type) +
                        specRow('Density', data.density ? data.density + ' kg/m³' : '—') +
                        specRow('Weight / sheet', data.weight_per_sheet) +
                        specRow('Termite treatment', data.termite_borer_treatment ? 'Yes' : 'No') +
                        specRow('Application', data.application) +
                        specRow('Origin', data.country_of_origin) +
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

    @if ($errors->any() && old('_method') === 'PUT' && old('_edit_product_id'))
        (function () {
            var form = document.getElementById('edit-product-form');
            if (form) {
                form.action = @json(route('admin.products.update', old('_edit_product_id')));
            }
            setFieldValue('name', @json(old('name', '')));
            setFieldValue('category_id', @json(old('category_id', '')));
            setFieldValue('description', @json(old('description', '')));
            setFieldValue('thickness', @json(old('thickness', '')));
            setFieldValue('size', @json(old('size', '')));
            setFieldValue('grade', @json(old('grade', '')));
            if (editModal) editModal.openModal();
        })();
    @elseif ($errors->any() && old('name') && old('_method') !== 'PUT')
        if (addModal) addModal.openModal();
    @endif
})();
</script>
@endpush
