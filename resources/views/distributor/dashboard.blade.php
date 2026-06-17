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
</div>
@endsection
