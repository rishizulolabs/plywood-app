@extends('layouts.distributor')

@section('title', 'Overview')
@section('page-title', 'Overview')
@section('page-subtitle', 'Manage products and orders')

@section('content')
<div class="portal-dashboard-page">
    <div class="stat-cards stat-cards-4 portal-dashboard-stats">
        @foreach ($stats as $stat)
            <a href="{{ $stat['href'] }}" class="stat-card stat-card-link portal-stat-card portal-stat-card-{{ $stat['color'] }}">
                <div class="stat-card-icon stat-card-icon-{{ $stat['color'] }}">
                    <svg class="icon-svg" aria-hidden="true"><use href="#{{ $stat['icon'] }}"></use></svg>
                </div>
                <div class="stat-card-content">
                    <p class="stat-label">{{ $stat['label'] }}</p>
                    <p class="stat-value">{{ $stat['value'] }}</p>
                    <p class="stat-desc">{{ $stat['desc'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    <div class="portal-dashboard-grid">
        <div class="portal-welcome-card">
            <div class="portal-welcome-header">
                <div>
                    <p class="portal-welcome-eyebrow">Distributor portal</p>
                    <h2 class="portal-welcome-title">{{ $profile?->business_name ?? 'Your business' }}</h2>
                    <p class="portal-welcome-text">
                        @if($isApproved)
                            Manage your plywood catalog and fulfill customer orders from one place.
                        @else
                            Your account is pending approval. The admin team is reviewing your application.
                        @endif
                    </p>
                </div>
                <div class="portal-welcome-avatar" aria-hidden="true">
                    {{ strtoupper(substr($profile?->business_name ?? $user->name, 0, 1)) }}
                </div>
            </div>

            <div class="portal-profile-tags">
                <span class="portal-profile-tag">
                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-user"></use></svg>
                    {{ $user->email }}
                </span>
                @if($user->phone)
                    <span class="portal-profile-tag">
                        <svg class="icon-svg" aria-hidden="true"><use href="#icon-activity"></use></svg>
                        {{ $user->phone }}
                    </span>
                @endif
                @if($isApproved)
                    <span class="portal-profile-tag portal-profile-tag-green">Active</span>
                @else
                    <span class="portal-profile-tag portal-profile-tag-amber">Awaiting approval</span>
                @endif
            </div>

            @unless($isApproved)
                <div class="alert alert-warning portal-approval-alert" role="alert">
                    <svg class="alert-icon" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg>
                    <p class="alert-message">You cannot list products or receive orders until your account is approved.</p>
                </div>
            @endunless

            <div class="portal-welcome-actions">
                <a href="{{ route('distributor.products.index') }}" class="btn-add">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-database"></use></svg>
                    <span>My products</span>
                </a>
                <a href="{{ route('distributor.orders.index') }}" class="btn-modal">Customer orders</a>
            </div>
        </div>

        <div class="content-card portal-quick-actions-card">
            <div class="content-card-header">
                <p class="content-card-title">Quick actions</p>
            </div>
            <div class="portal-quick-actions-grid">
                @foreach ($quickActions as $action)
                    <a href="{{ $action['href'] }}" class="portal-action-card">
                        <span class="portal-action-icon portal-action-icon-{{ $action['color'] }}">
                            <svg class="icon-svg" aria-hidden="true"><use href="#{{ $action['icon'] }}"></use></svg>
                        </span>
                        <span class="portal-action-body">
                            <span class="portal-action-title">{{ $action['title'] }}</span>
                            <span class="portal-action-desc">{{ $action['desc'] }}</span>
                        </span>
                        <svg class="portal-action-chevron icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="content-card portal-details-card">
        <div class="content-card-header">
            <p class="content-card-title">Business details</p>
        </div>
        <dl class="portal-details-grid">
            <div class="portal-detail-item">
                <dt>GST number</dt>
                <dd>{{ $profile?->gst_number ?? '—' }}</dd>
            </div>
            <div class="portal-detail-item">
                <dt>Service cities</dt>
                <dd>{{ $profile?->service_cities ? implode(', ', $profile->service_cities) : '—' }}</dd>
            </div>
            <div class="portal-detail-item">
                <dt>Rating</dt>
                <dd>{{ $profile?->rating ?? '—' }}</dd>
            </div>
            <div class="portal-detail-item">
                <dt>Account status</dt>
                <dd>
                    @if($isApproved)
                        <span class="badge badge-green">Approved</span>
                    @else
                        <span class="badge badge-yellow">Pending</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
@endsection
