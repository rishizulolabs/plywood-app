@php
    $idPrefix = $idPrefix ?? 'add_';
    $selectedProducts = $selectedProducts ?? [];
    $oldProducts = old('products', []);
@endphp

<div class="form-section">
    <p class="form-section-title">Products &amp; pricing</p>
    <p class="form-helper">Select products this distributor sells and set a price for each.</p>

    @if($products->isEmpty())
        <p class="form-helper">No products available yet. Add products first.</p>
    @else
        <div class="distributor-product-list">
            @foreach ($products as $product)
                @php
                    $isSelected = in_array((string) $product->id, $oldProducts, true)
                        || array_key_exists($product->id, $selectedProducts)
                        || array_key_exists((string) $product->id, $selectedProducts);
                    $priceValue = old('prices.'.$product->id, $selectedProducts[$product->id] ?? $selectedProducts[(string) $product->id] ?? '');
                @endphp
                <div class="distributor-product-row">
                    <label class="distributor-product-check-label">
                        <input
                            type="checkbox"
                            name="products[]"
                            value="{{ $product->id }}"
                            class="distributor-product-check"
                            @checked($isSelected)
                        >
                        <span class="distributor-product-text">
                            <span class="distributor-product-name">{{ $product->name }}</span>
                            <span class="distributor-product-meta">
                                {{ $product->grade ?? '—' }} · {{ $product->size ?? '—' }} · {{ $product->category?->name ?? 'Uncategorized' }}
                            </span>
                        </span>
                    </label>
                    <div class="distributor-product-price-wrap">
                        <label class="sr-only" for="{{ $idPrefix }}distributor-price-{{ $product->id }}">Price for {{ $product->name }}</label>
                        <input
                            type="number"
                            id="{{ $idPrefix }}distributor-price-{{ $product->id }}"
                            name="prices[{{ $product->id }}]"
                            class="form-input distributor-product-price @error('prices.'.$product->id) form-input-error @enderror"
                            placeholder="Price (₹)"
                            min="0"
                            step="0.01"
                            value="{{ $priceValue }}"
                            @disabled(! $isSelected)
                        >
                        @error('prices.'.$product->id)
                            <p class="form-helper form-helper-error">{{ $message }}</p>
                        @enderror
                        @if($isSelected && is_numeric($priceValue))
                            <p class="form-helper distributor-current-price">Current price: {{ format_inr($priceValue) }}</p>
                        @else
                            <p class="form-helper distributor-current-price" hidden></p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
