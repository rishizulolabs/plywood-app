@php
    use App\Support\ProductSpecs;
    $idPrefix = $idPrefix ?? 'add_';
    $values = $values ?? [];
    $id = fn (string $name) => $idPrefix.$name;
    $val = fn (string $name, mixed $default = '') => old($name, $values[$name] ?? $default);
@endphp

<div class="form-section">
    <p class="form-section-title">Basic details</p>

    <div class="form-group">
        <label class="form-label" for="{{ $id('name') }}">Product name <span class="required">*</span></label>
        <input type="text" id="{{ $id('name') }}" name="name" class="form-input" value="{{ $val('name') }}" placeholder="e.g. CenturyPly BWP 18mm" required>
    </div>

    <div class="form-group">
        <label class="form-label" for="{{ $id('category_id') }}">Category <span class="required">*</span></label>
        <select id="{{ $id('category_id') }}" name="category_id" class="form-select" required>
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $val('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" for="{{ $id('description') }}">Description</label>
        <textarea id="{{ $id('description') }}" name="description" class="form-input form-textarea" placeholder="Short product description">{{ $val('description') }}</textarea>
    </div>
</div>

<div class="form-section">
    <p class="form-section-title">Core specifications</p>

    <div class="form-row-2">
        <div class="form-group">
            <label class="form-label" for="{{ $id('thickness') }}">Thickness <span class="required">*</span></label>
            <select id="{{ $id('thickness') }}" name="thickness" class="form-select" required>
                <option value="">Select thickness</option>
                @foreach (ProductSpecs::THICKNESSES as $option)
                    <option value="{{ $option }}" @selected($val('thickness') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="{{ $id('size') }}">Size <span class="required">*</span></label>
            <select id="{{ $id('size') }}" name="size" class="form-select" required>
                <option value="">Select size</option>
                @foreach (ProductSpecs::SIZES as $option)
                    <option value="{{ $option }}" @selected($val('size') === $option)>
                        {{ $option }}@if($option === '8ft x 4ft') (2440×1220mm)@endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="{{ $id('grade') }}">Grade <span class="required">*</span></label>
        <select id="{{ $id('grade') }}" name="grade" class="form-select" required>
            <option value="">Select grade</option>
            @foreach (ProductSpecs::GRADES as $option)
                <option value="{{ $option }}" @selected($val('grade') === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-section">
    <p class="form-section-title">Images</p>

    <div class="form-group">
        <label class="form-label" for="{{ $id('product_image') }}">Product image</label>
        <input
            type="file"
            id="{{ $id('product_image') }}"
            name="product_image"
            class="form-input @error('product_image') form-input-error @enderror"
            accept="image/jpeg,image/png,image/webp,image/gif"
        >
        <p class="form-helper">Main product photo — JPG, PNG or WEBP up to 2MB</p>
        <div id="{{ $id('product_image_preview') }}" class="product-media-preview" hidden></div>
        @error('product_image')
            <p class="form-helper form-helper-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="{{ $id('thumbnails') }}">Product thumbnails</label>
        <input
            type="file"
            id="{{ $id('thumbnails') }}"
            name="thumbnails[]"
            class="form-input @error('thumbnails') form-input-error @enderror @error('thumbnails.*') form-input-error @enderror"
            accept="image/jpeg,image/png,image/webp,image/gif"
            multiple
        >
        <p class="form-helper">Select one or more images — JPG, PNG or WEBP up to 2MB each</p>
        <div id="{{ $id('thumbnails_preview') }}" class="product-media-preview" hidden></div>
        @error('thumbnails')
            <p class="form-helper form-helper-error">{{ $message }}</p>
        @enderror
        @error('thumbnails.*')
            <p class="form-helper form-helper-error">{{ $message }}</p>
        @enderror
    </div>

    @if($showMediaPreviews ?? false)
        <div id="edit-image-previews" class="product-media-previews"></div>
    @endif
</div>
