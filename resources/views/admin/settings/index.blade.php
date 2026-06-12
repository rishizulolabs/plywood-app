@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-subtitle', 'Platform configuration and preferences')

@section('content')
<div class="content-card" style="margin-bottom: 1.5rem;">
    <div class="content-card-header">
        <h2 class="content-card-title">Platform info</h2>
    </div>
    <div class="content-card-body" style="padding: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border: 1px solid #f3f4f6;">
                <p style="margin: 0 0 0.25rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">App name</p>
                <p style="margin: 0; font-size: 0.875rem; color: #374151;">{{ config('app.name') }}</p>
            </div>
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border: 1px solid #f3f4f6;">
                <p style="margin: 0 0 0.25rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Environment</p>
                <p style="margin: 0; font-size: 0.875rem; color: #374151;">{{ config('app.env') }}</p>
            </div>
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border: 1px solid #f3f4f6;">
                <p style="margin: 0 0 0.25rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Database</p>
                <p style="margin: 0; font-size: 0.875rem; color: #374151;">{{ config('database.default') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h2 class="content-card-title">General settings</h2>
    </div>
    <div class="content-card-body" style="padding: 1.5rem;">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="support_email">Support email <span class="required">*</span></label>
                <input
                    type="email"
                    id="support_email"
                    name="support_email"
                    class="form-input @error('support_email') form-input-error @enderror"
                    value="{{ old('support_email', $settings['support_email']) }}"
                    required
                >
                @error('support_email')
                    <p class="form-helper" style="color: #dc2626;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="support_phone">Support phone</label>
                <input
                    type="text"
                    id="support_phone"
                    name="support_phone"
                    class="form-input @error('support_phone') form-input-error @enderror"
                    value="{{ old('support_phone', $settings['support_phone']) }}"
                    placeholder="+91 98765 43210"
                >
                @error('support_phone')
                    <p class="form-helper" style="color: #dc2626;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label-checkbox">
                    <input
                        type="checkbox"
                        name="require_distributor_approval"
                        value="1"
                        {{ old('require_distributor_approval', $settings['require_distributor_approval']) ? 'checked' : '' }}
                    >
                    <span>Require admin approval for new distributors</span>
                </label>
                <p class="form-helper">When enabled, new distributor accounts stay pending until reviewed.</p>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="submit" class="btn-add">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-check-circle"></use></svg>
                    <span>Save settings</span>
                </button>
                <a href="{{ route('profile.edit') }}" class="btn-modal">My account</a>
                <a href="{{ url('/manage') }}" class="btn-modal">System panel</a>
            </div>
        </form>
    </div>
</div>
@endsection
