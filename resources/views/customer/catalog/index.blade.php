@extends('layouts.customer')

@section('title', 'Browse Catalog')
@section('page-title', 'Browse Catalog')
@section('page-subtitle', 'Plywood products added by admin — request quotes, no public pricing')

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
        <x-admin.empty-state :message="$hasActiveFilters ? 'No products match your search.' : 'No products available yet. Admin will add products to the catalog.'" />
    @else
        <div class="customer-catalog-grid">
            @foreach ($products as $product)
                <article class="customer-catalog-card">
                    <div class="customer-catalog-card-header">
                        <div>
                            <h3 class="customer-catalog-card-title">{{ $product->name }}</h3>
                            <p class="customer-catalog-card-subtitle">
                                {{ $product->brand ?? '—' }} · {{ $product->category?->name ?? 'Uncategorized' }}
                            </p>
                        </div>
                        @if($product->is_featured)
                            <span class="badge badge-yellow">Featured</span>
                        @endif
                    </div>

                    <div class="customer-catalog-card-badges">
                        @if($product->thickness)
                            <span class="badge badge-gray">{{ $product->thickness }}</span>
                        @endif
                        @if($product->size)
                            <span class="badge badge-gray">{{ $product->size }}</span>
                        @endif
                        @if($product->grade)
                            <span class="badge badge-gray">{{ $product->grade }}</span>
                        @endif
                        @if($product->in_stock)
                            <span class="badge badge-green">In stock</span>
                        @else
                            <span class="badge badge-gray">Out of stock</span>
                        @endif
                    </div>

                    <dl class="customer-catalog-specs">
                        <div>
                            <dt>Core type</dt>
                            <dd>{{ $product->core_type ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Distributor</dt>
                            <dd>{{ $product->distributorProfile?->business_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Min order</dt>
                            <dd>{{ $product->min_order_qty }} {{ $product->unit }}</dd>
                        </div>
                        <div>
                            <dt>ISI marked</dt>
                            <dd>{{ $product->is_isi_marked ? 'Yes' : 'No' }}</dd>
                        </div>
                    </dl>

                    @if($product->description)
                        <p class="customer-catalog-description">{{ Str::limit($product->description, 120) }}</p>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('customer.catalog.add-to-cart', $product) }}"
                        class="customer-catalog-cart-form"
                    >
                        @csrf
                        <label class="customer-catalog-qty-label">
                            <span>Qty</span>
                            <input
                                type="number"
                                name="quantity"
                                class="customer-catalog-qty-input"
                                min="{{ max(1, $product->min_order_qty) }}"
                                value="{{ max(1, $product->min_order_qty) }}"
                                required
                            >
                        </label>
                        <button type="submit" class="btn-add btn-add-sm" @disabled(! $product->in_stock)>
                            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-shopping-cart"></use></svg>
                            <span>Add to cart</span>
                        </button>
                    </form>
                </article>
            @endforeach
        </div>

        <x-admin.pagination :paginator="$products" />
    @endif
</div>
@endsection
