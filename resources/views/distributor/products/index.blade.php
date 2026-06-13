@extends('layouts.distributor')

@section('title', 'Products')
@section('page-title', 'Products')
@section('page-subtitle', 'Your plywood catalog listings')

@section('content')
@unless($profile)
    <div class="alert alert-warning" role="alert">
        <svg class="alert-icon" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg>
        <p class="alert-message">Complete your business profile before managing products.</p>
    </div>
@else
    <form class="filters-bar" method="GET" action="{{ route('distributor.products.index') }}">
        <div class="search-wrap">
            <svg class="search-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search by name, brand, grade or category..."
                value="{{ $search }}"
            >
        </div>
        <button type="submit" class="btn-search">
            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-search"></use></svg>
            <span>Search</span>
        </button>
        @if($search !== '')
            <a href="{{ route('distributor.products.index') }}" class="btn-clear-filters">
                <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
                <span>Clear</span>
            </a>
        @endif
    </form>

    <div class="content-card space-y" style="margin-top: 1.5rem;">
        <div class="content-card-header">
            <p class="content-card-title">Your products</p>
            <span class="badge badge-gray">{{ $products->total() }} total</span>
        </div>

        @if($products->isEmpty())
            <x-admin.empty-state :message="$search !== '' ? 'No products match your search.' : 'No products listed yet.'" />
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Thickness</th>
                        <th>Size</th>
                        <th>Grade</th>
                        <th>Category</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->brand ?? '—' }}</td>
                            <td>{{ $product->thickness ?? '—' }}</td>
                            <td>{{ $product->size ?? '—' }}</td>
                            <td>{{ $product->grade ?? '—' }}</td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>
                                @if($product->in_stock)
                                    <span class="badge badge-green">In stock</span>
                                @else
                                    <span class="badge badge-gray">Out of stock</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <x-admin.pagination :paginator="$products" />
        @endif
    </div>
@endunless
@endsection
