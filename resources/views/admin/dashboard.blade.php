@extends('layouts.admin')

@section('title', 'Platform Overview')
@section('page-title', 'Platform Overview')
@section('page-subtitle', 'Manage customers, distributors, products, and orders')

@section('content')
<div class="admin-dashboard-page">
    <div class="admin-dashboard-stats">
        @foreach ($stats as $stat)
            <a
                href="{{ $stat['href'] }}"
                class="admin-stat-card admin-stat-card-{{ $stat['color'] }}"
            >
                <div class="admin-stat-card-head">
                    <span class="admin-stat-card-icon admin-stat-card-icon-{{ $stat['color'] }}">
                        <svg class="icon-svg" aria-hidden="true"><use href="#{{ $stat['icon'] }}"></use></svg>
                    </span>
                    <span class="admin-stat-card-label">{{ $stat['label'] }}</span>
                    <svg class="admin-stat-card-chevron icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                </div>
                <p @class([
                    'admin-stat-card-value',
                    'admin-stat-card-value-currency' => ! empty($stat['is_currency']),
                ])>{{ $stat['value'] }}</p>
                @if(! empty($stat['desc']))
                    <p class="admin-stat-card-desc">{{ $stat['desc'] }}</p>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endsection
