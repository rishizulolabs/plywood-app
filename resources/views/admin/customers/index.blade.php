@extends('layouts.admin')

@section('title', 'Customers')
@section('page-title', 'Customers')
@section('page-subtitle', 'Registered buyer accounts')

@section('page-header-actions')
    <button type="button" class="btn-add" id="btn-open-add-customer" aria-controls="add-customer-modal">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-plus"></use></svg>
        <span>Add Customer</span>
    </button>
@endsection

@section('content')
@php
    $hasActiveFilters = ($filters['search'] ?? '') !== '';
@endphp

<form class="filters-bar" method="GET" action="{{ route('admin.customers.index') }}" id="customer-filters-form">
    <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by customer or distributor name..."
            value="{{ $filters['search'] ?? '' }}"
        >
    </div>
    <button type="submit" class="btn-search">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
        <span>Search</span>
    </button>
    @if($hasActiveFilters)
        <a href="{{ route('admin.customers.index') }}" class="btn-clear-filters">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            <span>Clear</span>
        </a>
    @endif
</form>

<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Customers</p>
        <span class="badge badge-gray">{{ $customers->total() }} total</span>
    </div>

    @if($customers->isEmpty())
        <x-admin.empty-state :message="$hasActiveFilters ? 'No customers match your search. Try different keywords or clear search.' : 'No customers found. Click Add Customer to create one.'" />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Distributor</th>
                    <th>Joined</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td>{{ $customer->city ?? '—' }}</td>
                        <td>{{ $customer->assignedDistributor?->user?->name ?? $customer->assignedDistributor?->business_name ?? '—' }}</td>
                        <td>{{ $customer->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="table-actions">
                                <button
                                    type="button"
                                    class="btn-action btn-action-edit btn-quick-edit"
                                    title="Quick edit"
                                    data-name="{{ $customer->name }}"
                                    data-phone="{{ $customer->phone ?? '' }}"
                                    data-city="{{ $customer->city ?? '' }}"
                                    data-distributor="{{ $customer->distributor_profile_id ?? '' }}"
                                    data-action="{{ route('admin.customers.update', $customer) }}"
                                >
                                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-edit"></use></svg>
                                </button>
                                <form
                                    method="POST"
                                    action="{{ route('admin.customers.destroy', $customer) }}"
                                    class="table-action-form"
                                    onsubmit="return confirm('Delete this customer? This cannot be undone.');"
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
        <x-admin.pagination :paginator="$customers" />
    @endif
</div>

<div class="modal-backdrop" id="customer-modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="add-customer-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="customer-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog" style="max-width: 32rem;">
        <div class="modal-header">
            <h2 class="modal-title" id="customer-modal-title">Add Customer</h2>
            <button type="button" class="btn-close-modal" id="btn-close-add-customer" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form
                class="modal-form"
                id="add-customer-form"
                method="POST"
                action="{{ route('admin.customers.store') }}"
            >
                @csrf

                <div class="form-group">
                    <label class="form-label" for="customer-name">Name <span class="required">*</span></label>
                    <input
                        type="text"
                        id="customer-name"
                        name="name"
                        class="form-input @error('name') form-input-error @enderror"
                        value="{{ old('name') }}"
                        placeholder="Enter customer name"
                        required
                    >
                    @error('name')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-email">Email <span class="required">*</span></label>
                    <input
                        type="email"
                        id="customer-email"
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
                    <label class="form-label" for="customer-phone">Phone no <span class="required">*</span></label>
                    <input
                        type="text"
                        id="customer-phone"
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
                    <label class="form-label" for="customer-city">City <span class="required">*</span></label>
                    <input
                        type="text"
                        id="customer-city"
                        name="city"
                        class="form-input @error('city') form-input-error @enderror"
                        value="{{ old('city') }}"
                        placeholder="Enter city"
                        required
                    >
                    @error('city')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-distributor">Distributor <span class="required">*</span></label>
                    <select
                        id="customer-distributor"
                        name="distributor_profile_id"
                        class="form-select @error('distributor_profile_id') form-input-error @enderror"
                        required
                    >
                        <option value="">Select distributor</option>
                        @foreach ($distributors as $distributor)
                            <option
                                value="{{ $distributor->id }}"
                                @selected((string) old('distributor_profile_id') === (string) $distributor->id)
                            >
                                {{ $distributor->user?->name ?? $distributor->business_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('distributor_profile_id')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-add-customer">Cancel</button>
            <button type="submit" form="add-customer-form" class="btn-submit">Add Customer</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="edit-customer-modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="edit-customer-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-customer-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog" style="max-width: 28rem;">
        <div class="modal-header">
            <h2 class="modal-title" id="edit-customer-modal-title">Quick Edit</h2>
            <button type="button" class="btn-close-modal" id="btn-close-edit-customer" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="edit-customer-form" method="POST" action="">
                @csrf
                @method('PUT')
                @if($hasActiveFilters)
                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                @endif

                <div class="form-group">
                    <label class="form-label" for="edit-customer-name">Name <span class="required">*</span></label>
                    <input type="text" id="edit-customer-name" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-customer-phone">Phone no <span class="required">*</span></label>
                    <input type="text" id="edit-customer-phone" name="phone" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-customer-city">City <span class="required">*</span></label>
                    <input type="text" id="edit-customer-city" name="city" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-customer-distributor">Distributor <span class="required">*</span></label>
                    <select id="edit-customer-distributor" name="distributor_profile_id" class="form-select" required>
                        <option value="">Select distributor</option>
                        @foreach ($distributors as $distributor)
                            <option value="{{ $distributor->id }}">
                                {{ $distributor->user?->name ?? $distributor->business_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-edit-customer">Cancel</button>
            <button type="submit" form="edit-customer-form" class="btn-submit">Save changes</button>
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

    var addModal = bindModal('customer-modal-backdrop', 'add-customer-modal', 'btn-open-add-customer', 'btn-close-add-customer', 'btn-cancel-add-customer');
    var editModal = bindModal('edit-customer-modal-backdrop', 'edit-customer-modal', null, 'btn-close-edit-customer', 'btn-cancel-edit-customer');

    document.querySelectorAll('.btn-quick-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById('edit-customer-form');
            if (!form || !editModal) return;

            form.action = btn.getAttribute('data-action');
            document.getElementById('edit-customer-name').value = btn.getAttribute('data-name') || '';
            document.getElementById('edit-customer-phone').value = btn.getAttribute('data-phone') || '';
            document.getElementById('edit-customer-city').value = btn.getAttribute('data-city') || '';
            document.getElementById('edit-customer-distributor').value = btn.getAttribute('data-distributor') || '';

            editModal.openModal();
        });
    });

    @if($errors->any() && old('name') && !old('_method'))
        if (addModal) addModal.openModal();
    @endif
})();
</script>
@endpush
