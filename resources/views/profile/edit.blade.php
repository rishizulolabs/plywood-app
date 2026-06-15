@extends('layouts.panel')

@section('portal-label', 'Account')
@section('title', 'Profile')
@section('page-title', 'Profile')
@section('page-subtitle', 'Update your account information')

@section('content')
<div class="content-card profile-form-card">
    <div class="content-card-header">
        <p class="content-card-title">Account details</p>
    </div>

    <form class="modal-form profile-form" method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label class="form-label" for="profile-name">Name <span class="required">*</span></label>
            <input type="text" id="profile-name" name="name" class="form-input @error('name') form-input-error @enderror" value="{{ old('name', $user->name) }}" required>
            @error('name')
                <p class="form-helper form-helper-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="profile-email">Email <span class="required">*</span></label>
            <input type="email" id="profile-email" name="email" class="form-input @error('email') form-input-error @enderror" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <p class="form-helper form-helper-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="profile-form-actions">
            <button type="submit" class="btn-submit">Save profile</button>
        </div>
    </form>
</div>
@endsection
