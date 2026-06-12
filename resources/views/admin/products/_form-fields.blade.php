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
        <label class="form-label" for="{{ $id('distributor_profile_id') }}">Distributor <span class="required">*</span></label>
        <select id="{{ $id('distributor_profile_id') }}" name="distributor_profile_id" class="form-select" required>
            <option value="">Select distributor</option>
            @foreach ($distributors as $distributor)
                <option value="{{ $distributor->id }}" @selected((string) $val('distributor_profile_id') === (string) $distributor->id)>
                    {{ $distributor->user?->name ?? $distributor->business_name }}
                </option>
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

    <div class="form-row-2">
        <div class="form-group">
            <label class="form-label" for="{{ $id('grade') }}">Grade <span class="required">*</span></label>
            <select id="{{ $id('grade') }}" name="grade" class="form-select" required>
                <option value="">Select grade</option>
                @foreach (ProductSpecs::GRADES as $option)
                    <option value="{{ $option }}" @selected($val('grade') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="{{ $id('core_type') }}">Core type <span class="required">*</span></label>
            <select id="{{ $id('core_type') }}" name="core_type" class="form-select" required>
                <option value="">Select core type</option>
                @foreach (ProductSpecs::CORE_TYPES as $option)
                    <option value="{{ $option }}" @selected($val('core_type') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-row-2">
        <div class="form-group">
            <label class="form-label" for="{{ $id('number_of_plies') }}">Number of plies <span class="required">*</span></label>
            <select id="{{ $id('number_of_plies') }}" name="number_of_plies" class="form-select" required>
                <option value="">Select plies</option>
                @foreach (ProductSpecs::NUMBER_OF_PLIES as $option)
                    <option value="{{ $option }}" @selected($val('number_of_plies') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="{{ $id('is_standard') }}">IS standard <span class="required">*</span></label>
            <select id="{{ $id('is_standard') }}" name="is_standard" class="form-select" required>
                <option value="">Select standard</option>
                @foreach (ProductSpecs::IS_STANDARDS as $option)
                    <option value="{{ $option }}" @selected($val('is_standard') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-row-2">
        <div class="form-group">
            <label class="form-label" for="{{ $id('brand') }}">Brand <span class="required">*</span></label>
            <select id="{{ $id('brand') }}" name="brand" class="form-select" required>
                <option value="">Select brand</option>
                @foreach (ProductSpecs::BRANDS as $option)
                    <option value="{{ $option }}" @selected($val('brand') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="{{ $id('warranty') }}">Warranty <span class="required">*</span></label>
            <select id="{{ $id('warranty') }}" name="warranty" class="form-select" required>
                <option value="">Select warranty</option>
                @foreach (ProductSpecs::WARRANTIES as $option)
                    <option value="{{ $option }}" @selected($val('warranty') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="{{ $id('is_isi_marked') }}">ISI marked <span class="required">*</span></label>
        <select id="{{ $id('is_isi_marked') }}" name="is_isi_marked" class="form-select" required>
            <option value="1" @selected((string) $val('is_isi_marked', '0') === '1')>Yes</option>
            <option value="0" @selected((string) $val('is_isi_marked', '0') === '0')>No</option>
        </select>
    </div>
</div>

<div class="form-section">
    <p class="form-section-title">Additional specifications</p>

    <div class="form-row-2">
        <div class="form-group">
            <label class="form-label" for="{{ $id('finish_surface') }}">Finish / surface</label>
            <select id="{{ $id('finish_surface') }}" name="finish_surface" class="form-select">
                <option value="">Select finish</option>
                @foreach (ProductSpecs::FINISH_SURFACES as $option)
                    <option value="{{ $option }}" @selected($val('finish_surface') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="{{ $id('glue_type') }}">Glue type</label>
            <select id="{{ $id('glue_type') }}" name="glue_type" class="form-select">
                <option value="">Select glue type</option>
                @foreach (ProductSpecs::GLUE_TYPES as $option)
                    <option value="{{ $option }}" @selected($val('glue_type') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-row-2">
        <div class="form-group">
            <label class="form-label" for="{{ $id('density') }}">Density (kg/m³)</label>
            <input type="text" id="{{ $id('density') }}" name="density" class="form-input" value="{{ $val('density') }}" placeholder="e.g. 650">
        </div>
        <div class="form-group">
            <label class="form-label" for="{{ $id('weight_per_sheet') }}">Weight per sheet</label>
            <input type="text" id="{{ $id('weight_per_sheet') }}" name="weight_per_sheet" class="form-input" value="{{ $val('weight_per_sheet') }}" placeholder="e.g. 28–32 kg">
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="{{ $id('termite_borer_treatment') }}">Termite & borer treatment</label>
        <select id="{{ $id('termite_borer_treatment') }}" name="termite_borer_treatment" class="form-select">
            <option value="0" @selected((string) $val('termite_borer_treatment', '0') === '0')>No</option>
            <option value="1" @selected((string) $val('termite_borer_treatment', '0') === '1')>Yes</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" for="{{ $id('application') }}">Application / best for</label>
        <input type="text" id="{{ $id('application') }}" name="application" class="form-input" value="{{ $val('application') }}" placeholder="e.g. Furniture, kitchen cabinets, wardrobes">
    </div>

    <div class="form-group">
        <label class="form-label" for="{{ $id('country_of_origin') }}">Country of origin</label>
        <input type="text" id="{{ $id('country_of_origin') }}" name="country_of_origin" class="form-input" value="{{ $val('country_of_origin') }}" placeholder="e.g. India">
    </div>
</div>

<div class="form-section">
    <p class="form-section-title">Listing</p>

    <div class="form-row-2">
        <div class="form-group">
            <label class="form-label" for="{{ $id('min_order_qty') }}">Min order qty <span class="required">*</span></label>
            <input type="number" id="{{ $id('min_order_qty') }}" name="min_order_qty" class="form-input" value="{{ $val('min_order_qty', 1) }}" min="1" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="{{ $id('unit') }}">Unit <span class="required">*</span></label>
            <select id="{{ $id('unit') }}" name="unit" class="form-select" required>
                @foreach (ProductSpecs::UNITS as $option)
                    <option value="{{ $option }}" @selected($val('unit', 'sheet') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-row-2">
        <div class="form-group">
            <label class="form-label" for="{{ $id('in_stock') }}">Stock status <span class="required">*</span></label>
            <select id="{{ $id('in_stock') }}" name="in_stock" class="form-select" required>
                <option value="1" @selected((string) $val('in_stock', '1') === '1')>In stock</option>
                <option value="0" @selected((string) $val('in_stock', '1') === '0')>Out of stock</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="{{ $id('is_featured') }}">Featured</label>
            <select id="{{ $id('is_featured') }}" name="is_featured" class="form-select">
                <option value="0" @selected((string) $val('is_featured', '0') === '0')>No</option>
                <option value="1" @selected((string) $val('is_featured', '0') === '1')>Yes</option>
            </select>
        </div>
    </div>
</div>
