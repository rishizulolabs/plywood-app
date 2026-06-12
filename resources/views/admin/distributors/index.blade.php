@extends('layouts.admin')

@section('title', 'Distributors')
@section('page-title', 'Distributors')
@section('page-subtitle', 'Business accounts and approval status')

@section('page-header-actions')
    <button type="button" class="btn-add" id="btn-open-add-distributor" aria-controls="add-distributor-modal">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-plus"></use></svg>
        <span>Add Distributor</span>
    </button>
@endsection

@section('content')
@php
    $hasActiveFilters = ($filters['search'] ?? '') !== '';
@endphp

<form class="filters-bar" method="GET" action="{{ route('admin.distributors.index') }}" id="distributor-filters-form">
    <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by name, phone or status..."
            value="{{ $filters['search'] ?? '' }}"
        >
    </div>
    <button type="submit" class="btn-search">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
        <span>Search</span>
    </button>
    @if($hasActiveFilters)
        <a href="{{ route('admin.distributors.index') }}" class="btn-clear-filters">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            <span>Clear</span>
        </a>
    @endif
</form>

<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Distributors</p>
        <span class="badge badge-gray">{{ $distributors->total() }} total</span>
    </div>

    @if($distributors->isEmpty())
        <x-admin.empty-state :message="$hasActiveFilters ? 'No distributors match your search. Try different filters or clear search.' : 'No distributors found. Click Add Distributor to create one.'" />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($distributors as $distributor)
                    <tr>
                        <td>{{ $distributor->user?->name ?? $distributor->business_name ?? '—' }}</td>
                        <td>{{ $distributor->user?->email ?? '—' }}</td>
                        <td>{{ $distributor->user?->phone ?? '—' }}</td>
                        <td>{{ $distributor->user?->city ?? ($distributor->service_cities[0] ?? '—') }}</td>
                        <td>
                            <div class="status-dropdown">
                                <button
                                    type="button"
                                    class="status-dropdown-trigger status-btn {{ $distributor->is_approved ? 'status-btn-approved is-active' : 'status-btn-pending is-active' }}"
                                    aria-haspopup="listbox"
                                    aria-expanded="false"
                                >
                                    <span>{{ $distributor->is_approved ? 'Approved' : 'Not approved' }}</span>
                                    <svg class="icon-svg status-dropdown-chevron" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                                </button>
                                <div class="status-dropdown-menu" role="listbox" hidden>
                                    <form method="POST" action="{{ route('admin.distributors.status', $distributor) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button
                                            type="submit"
                                            class="status-dropdown-option status-btn status-btn-approved {{ $distributor->is_approved ? 'is-selected' : '' }}"
                                            {{ $distributor->is_approved ? 'disabled' : '' }}
                                        >
                                            Approved
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.distributors.status', $distributor) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="not_approved">
                                        <button
                                            type="submit"
                                            class="status-dropdown-option status-btn status-btn-pending {{ ! $distributor->is_approved ? 'is-selected' : '' }}"
                                            {{ ! $distributor->is_approved ? 'disabled' : '' }}
                                        >
                                            Not approved
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                        <td>{{ $distributor->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="table-actions">
                                <button
                                    type="button"
                                    class="btn-action btn-action-edit btn-quick-edit"
                                    title="Quick edit"
                                    data-name="{{ $distributor->user?->name ?? $distributor->business_name }}"
                                    data-phone="{{ $distributor->user?->phone ?? '' }}"
                                    data-location="{{ $distributor->user?->city ?? ($distributor->service_cities[0] ?? '') }}"
                                    data-status="{{ $distributor->is_approved ? 'approved' : 'not_approved' }}"
                                    data-action="{{ route('admin.distributors.update', $distributor) }}"
                                >
                                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-edit"></use></svg>
                                </button>
                                <form
                                    method="POST"
                                    action="{{ route('admin.distributors.destroy', $distributor) }}"
                                    class="table-action-form"
                                    onsubmit="return confirm('Delete this distributor? This cannot be undone.');"
                                >
                                    @csrf
                                    @method('DELETE')
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
        <x-admin.pagination :paginator="$distributors" />
    @endif
</div>

<div class="modal-backdrop" id="modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="add-distributor-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog" style="max-width: 32rem;">
        <div class="modal-header">
            <h2 class="modal-title" id="modal-title">Add Distributor</h2>
            <button type="button" class="btn-close-modal" id="btn-close-add-distributor" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form
                class="modal-form"
                id="add-distributor-form"
                method="POST"
                action="{{ route('admin.distributors.store') }}"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="form-group">
                    <label class="form-label" for="distributor-name">Distributor name <span class="required">*</span></label>
                    <input
                        type="text"
                        id="distributor-name"
                        name="name"
                        class="form-input @error('name') form-input-error @enderror"
                        value="{{ old('name') }}"
                        placeholder="Enter distributor name"
                        required
                    >
                    @error('name')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="distributor-email">Email <span class="required">*</span></label>
                    <input
                        type="email"
                        id="distributor-email"
                        name="email"
                        class="form-input @error('email') form-input-error @enderror"
                        value="{{ old('email') }}"
                        placeholder="Enter email address"
                        required
                    >
                    @error('email')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="distributor-phone">Phone no <span class="required">*</span></label>
                    <input
                        type="text"
                        id="distributor-phone"
                        name="phone"
                        class="form-input @error('phone') form-input-error @enderror"
                        value="{{ old('phone') }}"
                        placeholder="Enter phone number"
                        required
                    >
                    @error('phone')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="distributor-location">Location <span class="required">*</span></label>
                    <input
                        type="text"
                        id="distributor-location"
                        name="location"
                        class="form-input @error('location') form-input-error @enderror"
                        value="{{ old('location') }}"
                        placeholder="City or service area"
                        required
                    >
                    @error('location')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="distributor-status">Status <span class="required">*</span></label>
                    <select
                        id="distributor-status"
                        name="status"
                        class="form-select @error('status') form-input-error @enderror"
                        required
                    >
                        <option value="">Select status</option>
                        <option value="approved" @selected(old('status') === 'approved')>Approved</option>
                        <option value="not_approved" @selected(old('status', 'not_approved') === 'not_approved')>Not approved</option>
                    </select>
                    @error('status')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="distributor-image">Image</label>
                    <input
                        type="file"
                        id="distributor-image"
                        name="image"
                        class="form-input @error('image') form-input-error @enderror"
                        accept="image/*"
                    >
                    <p class="form-helper">JPG, PNG or WEBP up to 2MB</p>
                    @error('image')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-add-distributor">Cancel</button>
            <button type="submit" form="add-distributor-form" class="btn-submit">Add Distributor</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="edit-modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="edit-distributor-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog" style="max-width: 28rem;">
        <div class="modal-header">
            <h2 class="modal-title" id="edit-modal-title">Quick Edit</h2>
            <button type="button" class="btn-close-modal" id="btn-close-edit-distributor" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="edit-distributor-form" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="edit-distributor-name">Name <span class="required">*</span></label>
                    <input type="text" id="edit-distributor-name" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-distributor-phone">Phone no <span class="required">*</span></label>
                    <input type="text" id="edit-distributor-phone" name="phone" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-distributor-location">Location <span class="required">*</span></label>
                    <input type="text" id="edit-distributor-location" name="location" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-distributor-status">Status <span class="required">*</span></label>
                    <select id="edit-distributor-status" name="status" class="form-select" required>
                        <option value="approved">Approved</option>
                        <option value="not_approved">Not approved</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-edit-distributor">Cancel</button>
            <button type="submit" form="edit-distributor-form" class="btn-submit">Save changes</button>
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

    var addModal = bindModal('modal-backdrop', 'add-distributor-modal', 'btn-open-add-distributor', 'btn-close-add-distributor', 'btn-cancel-add-distributor');
    var editModal = bindModal('edit-modal-backdrop', 'edit-distributor-modal', null, 'btn-close-edit-distributor', 'btn-cancel-edit-distributor');

    document.querySelectorAll('.btn-quick-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById('edit-distributor-form');
            if (!form || !editModal) return;

            form.action = btn.getAttribute('data-action');
            document.getElementById('edit-distributor-name').value = btn.getAttribute('data-name') || '';
            document.getElementById('edit-distributor-phone').value = btn.getAttribute('data-phone') || '';
            document.getElementById('edit-distributor-location').value = btn.getAttribute('data-location') || '';
            document.getElementById('edit-distributor-status').value = btn.getAttribute('data-status') || 'not_approved';

            editModal.openModal();
        });
    });

    @if($errors->any() && old('name') && !old('_method'))
        if (addModal) addModal.openModal();
    @endif
})();

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
