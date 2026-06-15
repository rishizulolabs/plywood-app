@extends('layouts.admin')

@section('title', 'Platform Overview')
@section('page-title', 'Platform Overview')
@section('page-subtitle', 'Manage customers, distributors, products, and orders')

@section('content')
<div class="stat-cards">
    @foreach ($stats as $stat)
        <div class="stat-card">
            <div class="stat-card-icon stat-card-icon-{{ $stat['color'] }}">
                <svg class="icon-svg" aria-hidden="true"><use href="#{{ $stat['icon'] }}"></use></svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-label">{{ $stat['label'] }}</p>
                <p class="stat-value">{{ $stat['value'] }}</p>
                <p class="stat-desc">{{ $stat['desc'] }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="content-card" style="margin-top: 1.5rem;">
    <div class="content-card-header">
        <h2 class="content-card-title">Welcome, {{ auth()->user()->name }}</h2>
        <span class="badge badge-gray">Admin Panel</span>
    </div>
    <div class="content-card-body" style="padding: 1.5rem;">
        <p style="margin: 0 0 1.5rem; font-size: 0.875rem; color: #6b7280; line-height: 1.6;">
            Manage your B2B plywood marketplace — distributors, customers, products, and orders.
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border: 1px solid #f3f4f6;">
                <p style="margin: 0 0 0.25rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Workflow</p>
                <p style="margin: 0; font-size: 0.875rem; color: #374151;">Customer cart → Order → Fulfillment</p>
            </div>
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border: 1px solid #f3f4f6;">
                <p style="margin: 0 0 0.25rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Pricing</p>
                <p style="margin: 0; font-size: 0.875rem; color: #374151;">No public prices — quotes only</p>
            </div>
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border: 1px solid #f3f4f6;">
                <p style="margin: 0 0 0.25rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Database</p>
                <p style="margin: 0; font-size: 0.875rem; color: #374151;">MySQL · plywood</p>
            </div>
        </div>
    </div>
</div>

@if($pendingProfiles->isNotEmpty())
    <div class="content-card space-y" style="margin-top: 1.5rem;">
        <div class="content-card-header">
            <p class="content-card-title">Pending distributor approvals</p>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Business</th>
                    <th>Owner</th>
                    <th>Status</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingProfiles as $profile)
                    <tr>
                        <td>{{ $profile->business_name }}</td>
                        <td>{{ $profile->user?->name ?? '—' }}</td>
                        <td><span class="badge badge-yellow">Pending</span></td>
                        <td>{{ $profile->user?->email ?? '—' }}</td>
                        <td>{{ $profile->user?->phone ?? '—' }}</td>
                        <td>{{ $profile->created_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="table-pagination">
            <p class="pagination-info">Showing <strong>1</strong> to <strong>{{ $pendingProfiles->count() }}</strong> pending approvals</p>
        </div>
    </div>
@endif
@endsection
