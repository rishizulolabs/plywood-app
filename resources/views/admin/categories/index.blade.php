@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories')
@section('page-subtitle', 'Product categories')

@section('page-header-actions')
    <button type="button" class="btn-add" id="btn-open-add-category" aria-controls="add-category-modal">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-plus"></use></svg>
        <span>Add Category</span>
    </button>
@endsection

@section('content')
@php
    $hasActiveFilters = ($filters['search'] ?? '') !== '';
@endphp

<form class="filters-bar" method="GET" action="{{ route('admin.categories.index') }}">
    <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by name..."
            value="{{ $filters['search'] ?? '' }}"
        >
    </div>
    <button type="submit" class="btn-search">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
        <span>Search</span>
    </button>
    @if($hasActiveFilters)
        <a href="{{ route('admin.categories.index') }}" class="btn-clear-filters">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            <span>Clear</span>
        </a>
    @endif
</form>

<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Categories</p>
        <span class="badge badge-gray">{{ $categories->total() }} total</span>
    </div>

    @if($categories->isEmpty())
        <x-admin.empty-state :message="$hasActiveFilters ? 'No categories match your search. Try different keywords or clear search.' : 'No categories found. Click Add Category to create one.'" />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>
                            <div class="table-actions">
                                <button
                                    type="button"
                                    class="btn-action btn-action-edit btn-quick-edit"
                                    title="Quick edit"
                                    data-name="{{ $category->name }}"
                                    data-action="{{ route('admin.categories.update', $category) }}"
                                >
                                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-edit"></use></svg>
                                </button>
                                <form
                                    method="POST"
                                    action="{{ route('admin.categories.destroy', $category) }}"
                                    class="table-action-form"
                                    onsubmit="return confirm('Delete this category? This cannot be undone.');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    @if($hasActiveFilters)
                                        <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                    @endif
                                    <button
                                        type="submit"
                                        class="btn-action btn-action-delete"
                                        title="Delete"
                                        @disabled($category->products_count > 0)
                                    >
                                        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-trash"></use></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-admin.pagination :paginator="$categories" />
    @endif
</div>

<div class="modal-backdrop" id="category-modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="add-category-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="category-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog" style="max-width: 28rem;">
        <div class="modal-header">
            <h2 class="modal-title" id="category-modal-title">Add Category</h2>
            <button type="button" class="btn-close-modal" id="btn-close-add-category" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="add-category-form" method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="category-name">Name <span class="required">*</span></label>
                    <input
                        type="text"
                        id="category-name"
                        name="name"
                        class="form-input @error('name') form-input-error @enderror"
                        value="{{ old('name') }}"
                        placeholder="Enter category name"
                        required
                    >
                    @error('name')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-add-category">Cancel</button>
            <button type="submit" form="add-category-form" class="btn-submit">Add Category</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="edit-category-modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="edit-category-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-category-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog" style="max-width: 28rem;">
        <div class="modal-header">
            <h2 class="modal-title" id="edit-category-modal-title">Quick Edit</h2>
            <button type="button" class="btn-close-modal" id="btn-close-edit-category" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="edit-category-form" method="POST" action="">
                @csrf
                @method('PUT')
                @if($hasActiveFilters)
                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                @endif

                <div class="form-group">
                    <label class="form-label" for="edit-category-name">Name <span class="required">*</span></label>
                    <input type="text" id="edit-category-name" name="name" class="form-input" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-edit-category">Cancel</button>
            <button type="submit" form="edit-category-form" class="btn-submit">Save changes</button>
        </div>
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

    var addModal = bindModal('category-modal-backdrop', 'add-category-modal', 'btn-open-add-category', 'btn-close-add-category', 'btn-cancel-add-category');
    var editModal = bindModal('edit-category-modal-backdrop', 'edit-category-modal', null, 'btn-close-edit-category', 'btn-cancel-edit-category');

    document.querySelectorAll('.btn-quick-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById('edit-category-form');
            if (!form || !editModal) return;

            form.action = btn.getAttribute('data-action');
            document.getElementById('edit-category-name').value = btn.getAttribute('data-name') || '';

            editModal.openModal();
        });
    });

    @if($errors->any() && old('name') && !old('_method'))
        if (addModal) addModal.openModal();
    @endif
})();
</script>
@endpush
