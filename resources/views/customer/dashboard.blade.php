@extends('layouts.customer')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Track inquiries, quotes, and orders')

@section('content')
<div class="stat-cards">
    <x-panel.stat-card label="Inquiries" :value="auth()->user()->inquiries()->count()" color="blue">
        <x-slot:iconSlot>
            <svg class="icon-svg" aria-hidden="true"><use href="#icon-file-text"></use></svg>
        </x-slot:iconSlot>
    </x-panel.stat-card>

    <x-panel.stat-card label="Orders" :value="auth()->user()->orders()->count()" color="green">
        <x-slot:iconSlot>
            <svg class="icon-svg" aria-hidden="true"><use href="#icon-package"></use></svg>
        </x-slot:iconSlot>
    </x-panel.stat-card>

    <x-panel.stat-card label="Company" :value="auth()->user()->company_name ?? '—'" color="purple">
        <x-slot:iconSlot>
            <svg class="icon-svg" aria-hidden="true"><use href="#icon-users"></use></svg>
        </x-slot:iconSlot>
    </x-panel.stat-card>

    <x-panel.stat-card label="City" :value="auth()->user()->city ?? '—'" color="amber">
        <x-slot:iconSlot>
            <svg class="icon-svg" aria-hidden="true"><use href="#icon-map-pin"></use></svg>
        </x-slot:iconSlot>
    </x-panel.stat-card>
</div>

<div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-top: 0;">
    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
        <x-panel.content-card>
            <h2 style="margin: 0 0 0.5rem; font-size: 1rem; font-weight: 600;">Welcome back, {{ auth()->user()->name }}</h2>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280; line-height: 1.6;">
                Browse plywood products, add items to your inquiry cart, and request custom quotes from distributors.
                Pricing is shared only after a distributor responds to your inquiry.
            </p>
            <div style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.75rem;">
                <a href="{{ route('home') }}" class="btn-add">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-layers"></use></svg>
                    <span>Browse catalog</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="btn-modal">Update profile</a>
            </div>
        </x-panel.content-card>

        <x-panel.content-card title="Quick actions">
            <ul style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 0.75rem;">
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; background: #f9fafb; border-radius: 0.5rem;">
                    <span style="font-size: 0.875rem; color: #6b7280;">Inquiry cart</span>
                    <span class="badge badge-gray">Soon</span>
                </li>
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; background: #f9fafb; border-radius: 0.5rem;">
                    <span style="font-size: 0.875rem; color: #6b7280;">View quotes</span>
                    <span class="badge badge-gray">Soon</span>
                </li>
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; background: #f9fafb; border-radius: 0.5rem;">
                    <span style="font-size: 0.875rem; color: #6b7280;">Track orders</span>
                    <span class="badge badge-gray">Soon</span>
                </li>
            </ul>
        </x-panel.content-card>
    </div>
</div>
@endsection
