@extends('layouts.distributor')

@section('title', 'Overview')
@section('page-title', 'Overview')
@section('page-subtitle', 'Manage products, inquiries, and orders')

@section('content')
<div class="stat-cards">
    <x-panel.stat-card label="Products Listed" :value="$profile?->products()->count() ?? 0" color="blue">
        <x-slot:iconSlot>
            <svg class="icon-svg" aria-hidden="true"><use href="#icon-database"></use></svg>
        </x-slot:iconSlot>
    </x-panel.stat-card>

    <x-panel.stat-card label="Inquiries" :value="$profile?->inquiries()->count() ?? 0" color="amber">
        <x-slot:iconSlot>
            <svg class="icon-svg" aria-hidden="true"><use href="#icon-file-text"></use></svg>
        </x-slot:iconSlot>
    </x-panel.stat-card>

    <x-panel.stat-card label="Active Orders" :value="$profile?->orders()->count() ?? 0" color="green">
        <x-slot:iconSlot>
            <svg class="icon-svg" aria-hidden="true"><use href="#icon-package"></use></svg>
        </x-slot:iconSlot>
    </x-panel.stat-card>

    <x-panel.stat-card
        label="Approval"
        :value="$profile?->is_approved ? 'Approved' : 'Pending'"
        :color="$profile?->is_approved ? 'green' : 'amber'"
    >
        <x-slot:iconSlot>
            <svg class="icon-svg" aria-hidden="true"><use href="#icon-check-circle"></use></svg>
        </x-slot:iconSlot>
    </x-panel.stat-card>
</div>

<x-panel.content-card>
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h2 style="margin: 0 0 0.25rem; font-size: 1rem; font-weight: 600;">{{ $profile?->business_name ?? 'Your business' }}</h2>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">{{ auth()->user()->email }}</p>
        </div>
        @if($profile?->is_approved)
            <span class="badge badge-green">Active</span>
        @else
            <span class="badge badge-yellow">Awaiting approval</span>
        @endif
    </div>

    @unless($profile?->is_approved)
        <div class="alert alert-warning" role="alert" style="margin-top: 1rem;">
            <svg class="alert-icon" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg>
            <p class="alert-message">Your distributor account is pending admin approval. You cannot list products or receive inquiries until approved.</p>
        </div>
    @else
        <p style="margin: 1rem 0 0; font-size: 0.875rem; color: #6b7280; line-height: 1.6;">
            Manage your plywood catalog, respond to customer inquiries with custom quotes, and fulfill orders from your dashboard.
        </p>
    @endunless

    <div style="margin-top: 1.5rem;">
        <a href="{{ route('profile.edit') }}" class="btn-add">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-user"></use></svg>
            <span>Business profile</span>
        </a>
    </div>
</x-panel.content-card>

<x-panel.content-card title="Business details">
    <dl style="margin: 0; display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.875rem;">
        <div style="display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f3f4f6;">
            <dt style="color: #6b7280;">GST</dt>
            <dd style="margin: 0; font-weight: 500;">{{ $profile?->gst_number ?? '—' }}</dd>
        </div>
        <div style="display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f3f4f6;">
            <dt style="color: #6b7280;">Service cities</dt>
            <dd style="margin: 0; font-weight: 500; text-align: right;">
                {{ $profile?->service_cities ? implode(', ', $profile->service_cities) : '—' }}
            </dd>
        </div>
        <div style="display: flex; justify-content: space-between; gap: 1rem;">
            <dt style="color: #6b7280;">Rating</dt>
            <dd style="margin: 0; font-weight: 500;">{{ $profile?->rating ?? '—' }}</dd>
        </div>
    </dl>
</x-panel.content-card>
@endsection
