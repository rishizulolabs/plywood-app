@props(['product', 'showCart' => false, 'linkToDetail' => false])

@php
    $productImage = $product->getFirstMediaUrl('product_image')
        ?: $product->getFirstMediaUrl('thumbnails');
@endphp

<article class="customer-catalog-card">
    @if($linkToDetail)
        <a href="{{ route('customer.catalog.show', $product) }}" class="customer-catalog-card-link">
    @endif

    @if($productImage)
        <img src="{{ $productImage }}" alt="{{ $product->name }}" class="customer-catalog-card-image">
    @else
        <div class="customer-catalog-card-image-placeholder" aria-hidden="true"></div>
    @endif

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

    @if($linkToDetail)
        </a>
    @endif

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

    <dl class="customer-catalog-specs">
        <div>
            <dt>Min order</dt>
            <dd>{{ $product->min_order_qty }} {{ $product->unit }}</dd>
        </div>
    </dl>

    @if($product->description)
        <p class="customer-catalog-description">{{ Str::limit($product->description, 120) }}</p>
    @endif

    @if($showCart)
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
            <button type="submit" class="btn-add btn-add-sm">
                <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-shopping-cart"></use></svg>
                <span>Add to cart</span>
            </button>
        </form>
    @endif
</article>
