@extends('layouts.customer')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Track inquiries, quotes, and orders')

@section('content')
<div class="customer-dashboard-page">
<div class="stat-cards stat-cards-4 customer-dashboard-stats">
    @foreach ($stats as $stat)
        <a href="{{ $stat['href'] }}" class="stat-card stat-card-link">
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

<div class="customer-dashboard-grid">
    <div class="customer-welcome-card">
        <div class="customer-welcome-header">
            <div>
                <p class="customer-welcome-eyebrow">Customer portal</p>
                <h2 class="customer-welcome-title">Welcome back, {{ $user->name }}</h2>
                <p class="customer-welcome-text">
                    Browse plywood products, add items to your inquiry cart, and request custom quotes from distributors.
                    Pricing is shared only after a distributor responds.
                </p>
            </div>
            <div class="customer-welcome-avatar" aria-hidden="true">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        </div>

        <div class="customer-profile-tags">
            @if($user->company_name)
                <span class="customer-profile-tag">
                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-users"></use></svg>
                    {{ $user->company_name }}
                </span>
            @endif
            @if($user->city)
                <span class="customer-profile-tag">
                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-map-pin"></use></svg>
                    {{ $user->city }}
                </span>
            @endif
            <span class="customer-profile-tag">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-user"></use></svg>
                {{ $user->email }}
            </span>
        </div>

        <div class="customer-welcome-actions">
            <a href="{{ route('customer.catalog.index') }}" class="btn-add">
                <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-layers"></use></svg>
                <span>Browse catalog</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="btn-modal">Update profile</a>
        </div>
    </div>

    <div class="content-card customer-quick-actions-card">
        <div class="content-card-header">
            <p class="content-card-title">Quick actions</p>
        </div>
        <div class="customer-quick-actions-grid">
            @foreach ($quickActions as $action)
                <a href="{{ $action['href'] }}" class="customer-action-card">
                    <span class="customer-action-icon customer-action-icon-{{ $action['color'] }}">
                        <svg class="icon-svg" aria-hidden="true"><use href="#{{ $action['icon'] }}"></use></svg>
                    </span>
                    <span class="customer-action-body">
                        <span class="customer-action-title">{{ $action['title'] }}</span>
                        <span class="customer-action-desc">{{ $action['desc'] }}</span>
                    </span>
                    <svg class="customer-action-chevron icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($inquiryCount === 0)
    <div class="content-card customer-getting-started">
        <div class="content-card-header">
            <p class="content-card-title">Getting started</p>
            <span class="badge badge-gray">3 steps</span>
        </div>
        <div class="customer-steps-grid">
            <div class="customer-step">
                <span class="customer-step-number">1</span>
                <div>
                    <p class="customer-step-title">Browse the catalog</p>
                    <p class="customer-step-desc">Find plywood by thickness, grade, and brand.</p>
                </div>
            </div>
            <div class="customer-step">
                <span class="customer-step-number">2</span>
                <div>
                    <p class="customer-step-title">Add to inquiry cart</p>
                    <p class="customer-step-desc">Select products and quantities for your project.</p>
                </div>
            </div>
            <div class="customer-step">
                <span class="customer-step-number">3</span>
                <div>
                    <p class="customer-step-title">Request a quote</p>
                    <p class="customer-step-desc">Distributors respond with custom pricing.</p>
                </div>
            </div>
        </div>
        <div class="customer-getting-started-cta">
            <a href="{{ route('customer.catalog.index') }}" class="btn-add">
                <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-layers"></use></svg>
                <span>Start browsing</span>
            </a>
        </div>
    </div>
@else
    <div class="content-card space-y customer-recent-inquiries">
        <div class="content-card-header">
            <p class="content-card-title">Recent inquiries</p>
            <a href="{{ route('customer.inquiries.index') }}" class="btn-link-table">View all</a>
        </div>

        @if($recentInquiries->isEmpty())
            <x-admin.empty-state message="No recent inquiries." />
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Inquiry #</th>
                        <th>Distributor</th>
                        <th>Status</th>
                        <th>Quote</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentInquiries as $inquiry)
                        <tr>
                            <td>{{ $inquiry->inquiry_number }}</td>
                            <td>{{ $inquiry->distributorProfile?->business_name ?? '—' }}</td>
                            <td><span class="badge badge-yellow">{{ ucfirst($inquiry->status) }}</span></td>
                            <td>
                                @if($inquiry->quote)
                                    <span class="badge badge-green">{{ format_inr($inquiry->quote->total) }}</span>
                                @else
                                    <span class="badge badge-gray">Pending</span>
                                @endif
                            </td>
                            <td>{{ $inquiry->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif
</div>
@endsection
