@extends('layouts.customer')

@section('title', $product->name)
@section('page-title', $product->name)
@section('page-subtitle', ($product->brand ?? '—').' · '.($product->category?->name ?? 'Uncategorized'))

@section('content')
@php
    $productImage = $product->getFirstMediaUrl('product_image');
    $thumbnails = $product->getMedia('thumbnails');
@endphp

<a href="{{ route('customer.catalog.index') }}" class="catalog-back-link">
    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-chevron-left"></use></svg>
    <span>Back to catalog</span>
</a>

<div class="content-card customer-product-detail">
    <div class="customer-product-detail-grid">
        <div class="customer-product-detail-media">
            @if($productImage)
                <img src="{{ $productImage }}" alt="{{ $product->name }}" class="customer-product-detail-image">
            @else
                <div class="customer-catalog-card-image-placeholder customer-product-detail-image-placeholder" aria-hidden="true"></div>
            @endif

            @if($thumbnails->isNotEmpty())
                <div class="customer-product-detail-thumbs">
                    @foreach ($thumbnails as $thumb)
                        <img src="{{ $thumb->getUrl() }}" alt="{{ $product->name }} thumbnail" class="customer-product-detail-thumb">
                    @endforeach
                </div>
            @endif
        </div>

        <div class="customer-product-detail-info">
            <div class="customer-catalog-card-header">
                <div>
                    <h2 class="customer-product-detail-title">{{ $product->name }}</h2>
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
            </div>

            <dl class="product-spec-list">
                <div class="product-spec-item">
                    <dt>Core type</dt>
                    <dd>{{ $product->core_type ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Plies</dt>
                    <dd>{{ $product->number_of_plies ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>IS standard</dt>
                    <dd>{{ $product->is_standard ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Warranty</dt>
                    <dd>{{ $product->warranty ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Finish</dt>
                    <dd>{{ $product->finish_surface ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Glue type</dt>
                    <dd>{{ $product->glue_type ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Density</dt>
                    <dd>{{ $product->density ? $product->density.' kg/m³' : '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Weight / sheet</dt>
                    <dd>{{ $product->weight_per_sheet ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Termite treatment</dt>
                    <dd>{{ $product->termite_borer_treatment ? 'Yes' : 'No' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Application</dt>
                    <dd>{{ $product->application ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Origin</dt>
                    <dd>{{ $product->country_of_origin ?? '—' }}</dd>
                </div>
                <div class="product-spec-item">
                    <dt>Min order</dt>
                    <dd>{{ $product->min_order_qty }} {{ $product->unit }}</dd>
                </div>
            </dl>

            @if($product->description)
                <p class="product-card-description">{{ $product->description }}</p>
            @endif

            <form
                method="POST"
                action="{{ route('customer.catalog.add-to-cart', $product) }}"
                class="customer-catalog-cart-form customer-product-detail-cart"
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
                <button type="submit" class="btn-add">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-shopping-cart"></use></svg>
                    <span>Add to cart</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
