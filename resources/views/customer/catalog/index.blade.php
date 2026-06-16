@extends('layouts.customer')

@section('title', 'Browse Catalog')
@section('page-title', 'Browse Catalog')
@section('page-subtitle')
    @if($distributor)
        Products offered by {{ $distributor->business_name ?: ($distributor->user?->name ?? 'your distributor') }}
    @else
        Products appear here once your account is linked to an approved distributor.
    @endif
@endsection

@section('content')
@php
    $hasActiveFilters = $search !== '' || ! empty($categoryId);
@endphp

<form class="filters-bar" method="GET" action="{{ route('customer.catalog.index') }}">
    <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by name, brand, grade, thickness or category..."
            value="{{ $search }}"
        >
    </div>
    <select name="category" class="form-select catalog-category-select" aria-label="Filter by category">
        <option value="">All categories</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="btn-search">
        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
        <span>Search</span>
    </button>
    @if($hasActiveFilters)
        <a href="{{ route('customer.catalog.index') }}" class="btn-clear-filters">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            <span>Clear</span>
        </a>
    @endif
</form>

<div class="content-card customer-catalog-panel">
    <div class="content-card-header">
        <p class="content-card-title">Available products</p>
        <span class="badge badge-gray">{{ $products->total() }} listed</span>
    </div>

    @if($products->isEmpty())
        <x-admin.empty-state :message="$hasActiveFilters ? 'No products match your search.' : ($distributor ? 'No products have been assigned to your distributor yet.' : 'Your account is not linked to a distributor yet. Contact admin to get access to products.')" />
    @else
        <div class="customer-catalog-grid">
            @foreach ($products as $product)
                <x-catalog.product-card :product="$product" :show-cart="true" :link-to-detail="true" />
            @endforeach
        </div>

        <x-admin.pagination :paginator="$products" />
    @endif
</div>
@endsection
