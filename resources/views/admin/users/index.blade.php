@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')
@section('page-subtitle', 'All registered accounts')

@section('page-header-actions')
    <button type="button" class="btn-add" id="btn-open-add-user" aria-controls="add-user-modal">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-plus"></use></svg>
        <span>Add User</span>
    </button>
@endsection

@section('content')
@php
    $hasActiveFilters = ($filters['search'] ?? '') !== '';
@endphp

<form class="filters-bar" method="GET" action="{{ route('admin.users.index') }}" id="user-filters-form">
    <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by name, email, role, phone or city..."
            value="{{ $filters['search'] ?? '' }}"
        >
    </div>
    <button type="submit" class="btn-search">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
        <span>Search</span>
    </button>
    @if($hasActiveFilters)
        <a href="{{ route('admin.users.index') }}" class="btn-clear-filters">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            <span>Clear</span>
        </a>
    @endif
</form>

<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">All Users</p>
        <span class="badge badge-gray">{{ $users->total() }} total</span>
    </div>

    @if($users->isEmpty())
        <x-admin.empty-state :message="$hasActiveFilters ? 'No users match your search. Try different keywords or clear search.' : 'No users found. Click Add User to create one.'" />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Joined</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $roleName = $user->roles->first()?->name ?? '';
                    @endphp
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($roleName)
                                <span class="badge badge-gray">{{ ucfirst($roleName) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>{{ $user->city ?? '—' }}</td>
                        <td>{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="table-actions">
                                <button
                                    type="button"
                                    class="btn-action btn-action-edit btn-quick-edit"
                                    title="Quick edit"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-role="{{ $roleName }}"
                                    data-phone="{{ $user->phone ?? '' }}"
                                    data-city="{{ $user->city ?? '' }}"
                                    data-action="{{ route('admin.users.update', $user) }}"
                                >
                                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-edit"></use></svg>
                                </button>
                                <form
                                    method="POST"
                                    action="{{ route('admin.users.destroy', $user) }}"
                                    class="table-action-form"
                                    onsubmit="return confirm('Delete this user? This cannot be undone.');"
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
        <x-admin.pagination :paginator="$users" />
    @endif
</div>

<div class="modal-backdrop" id="user-modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="add-user-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="user-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog" style="max-width: 32rem;">
        <div class="modal-header">
            <h2 class="modal-title" id="user-modal-title">Add User</h2>
            <button type="button" class="btn-close-modal" id="btn-close-add-user" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="add-user-form" method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="user-name">Name <span class="required">*</span></label>
                    <input
                        type="text"
                        id="user-name"
                        name="name"
                        class="form-input @error('name') form-input-error @enderror"
                        value="{{ old('name') }}"
                        placeholder="Enter name"
                        required
                    >
                    @error('name')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="user-email">Email <span class="required">*</span></label>
                    <input
                        type="email"
                        id="user-email"
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
                    <label class="form-label" for="user-role">Role <span class="required">*</span></label>
                    <select id="user-role" name="role" class="form-select @error('role') form-input-error @enderror" required>
                        <option value="">Select role</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        <option value="customer" @selected(old('role') === 'customer')>Customer</option>
                        <option value="distributor" @selected(old('role') === 'distributor')>Distributor</option>
                    </select>
                    @error('role')
                        <p class="form-helper form-helper-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="user-phone">Phone no <span class="required">*</span></label>
                    <input
                        type="text"
                        id="user-phone"
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
                    <label class="form-label" for="user-city">City <span class="required">*</span></label>
                    <input
                        type="text"
                        id="user-city"
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
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-add-user">Cancel</button>
            <button type="submit" form="add-user-form" class="btn-submit">Add User</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="edit-user-modal-backdrop" aria-hidden="true"></div>
<div
    class="modal"
    id="edit-user-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-user-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog" style="max-width: 28rem;">
        <div class="modal-header">
            <h2 class="modal-title" id="edit-user-modal-title">Quick Edit</h2>
            <button type="button" class="btn-close-modal" id="btn-close-edit-user" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="edit-user-form" method="POST" action="">
                @csrf
                @method('PUT')
                @if($hasActiveFilters)
                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                @endif

                <div class="form-group">
                    <label class="form-label" for="edit-user-name">Name <span class="required">*</span></label>
                    <input type="text" id="edit-user-name" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-user-email">Email <span class="required">*</span></label>
                    <input type="email" id="edit-user-email" name="email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-user-role">Role <span class="required">*</span></label>
                    <select id="edit-user-role" name="role" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                        <option value="distributor">Distributor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-user-phone">Phone no <span class="required">*</span></label>
                    <input type="text" id="edit-user-phone" name="phone" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-user-city">City <span class="required">*</span></label>
                    <input type="text" id="edit-user-city" name="city" class="form-input" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btn-cancel-edit-user">Cancel</button>
            <button type="submit" form="edit-user-form" class="btn-submit">Save changes</button>
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

    var addModal = bindModal('user-modal-backdrop', 'add-user-modal', 'btn-open-add-user', 'btn-close-add-user', 'btn-cancel-add-user');
    var editModal = bindModal('edit-user-modal-backdrop', 'edit-user-modal', null, 'btn-close-edit-user', 'btn-cancel-edit-user');

    document.querySelectorAll('.btn-quick-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById('edit-user-form');
            if (!form || !editModal) return;

            form.action = btn.getAttribute('data-action');
            document.getElementById('edit-user-name').value = btn.getAttribute('data-name') || '';
            document.getElementById('edit-user-email').value = btn.getAttribute('data-email') || '';
            document.getElementById('edit-user-role').value = btn.getAttribute('data-role') || 'customer';
            document.getElementById('edit-user-phone').value = btn.getAttribute('data-phone') || '';
            document.getElementById('edit-user-city').value = btn.getAttribute('data-city') || '';

            editModal.openModal();
        });
    });

    @if($errors->any() && old('name') && !old('_method'))
        if (addModal) addModal.openModal();
    @endif
})();
</script>
@endpush
