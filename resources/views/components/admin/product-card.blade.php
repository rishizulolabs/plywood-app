@props(['product'])

<div class="product-card">
    <div class="product-card-header">
        <div>
            <h3 class="product-card-title">{{ $product->name }}</h3>
            <p class="product-card-subtitle">{{ $product->brand }} · {{ $product->category?->name ?? 'Uncategorized' }}</p>
        </div>
        @if($product->is_featured)
            <span class="badge badge-yellow">Featured</span>
        @endif
    </div>

    <div class="product-card-badges">
        <span class="badge badge-gray">{{ $product->grade }}</span>
        <span class="badge badge-gray">{{ $product->thickness }}</span>
        <span class="badge badge-gray">{{ $product->size }}</span>
        @if($product->in_stock)
            <span class="badge badge-green">In stock</span>
        @else
            <span class="badge badge-gray">Out of stock</span>
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
            <dt>ISI marked</dt>
            <dd>{{ $product->is_isi_marked ? 'Yes' : 'No' }}</dd>
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
            <dt>Distributor</dt>
            <dd>{{ $product->distributorProfile?->user?->name ?? $product->distributorProfile?->business_name ?? '—' }}</dd>
        </div>
        <div class="product-spec-item">
            <dt>Min order</dt>
            <dd>{{ $product->min_order_qty }} {{ $product->unit }}</dd>
        </div>
    </dl>

    @if($product->description)
        <p class="product-card-description">{{ $product->description }}</p>
    @endif
</div>
