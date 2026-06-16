@extends('layouts.customer')

@section('title', 'Cart')
@section('page-title', 'Cart')
@section('page-subtitle', 'Review products before placing your order')

@section('content')
<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Cart items</p>
        <span class="badge badge-gray">{{ count($cartItems) }} items</span>
    </div>

    @if(empty($cartItems))
        <x-admin.empty-state message="Your cart is empty. Browse the catalog and add products to place an order." />
        <div style="padding: 0 1.5rem 1.5rem; text-align: center;">
            <a href="{{ route('customer.catalog.index') }}" class="btn-add">
                <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-layers"></use></svg>
                <span>Browse catalog</span>
            </a>
        </div>
    @else
        <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Distributor</th>
                    <th>Quantity</th>
                    <th>Notes</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cartItems as $item)
                    <tr>
                        <td>{{ $item['product_name'] ?? 'Product' }}</td>
                        <td>{{ $item['distributor'] ?? '—' }}</td>
                        <td>{{ $item['quantity'] ?? 1 }}</td>
                        <td>{{ $item['notes'] ?? '—' }}</td>
                        <td>
                            <div class="table-actions">
                                <button
                                    type="button"
                                    class="btn-action btn-action-edit btn-edit-cart-item"
                                    title="Edit item"
                                    data-action="{{ route('customer.cart.update', $item['product_id']) }}"
                                    data-name="{{ $item['product_name'] ?? 'Product' }}"
                                    data-quantity="{{ $item['quantity'] ?? 1 }}"
                                    data-notes="{{ $item['notes'] ?? '' }}"
                                    data-min-qty="{{ max(1, (int) ($item['min_order_qty'] ?? 1)) }}"
                                >
                                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-edit"></use></svg>
                                </button>
                                <form
                                    method="POST"
                                    action="{{ route('customer.cart.remove', $item['product_id']) }}"
                                    class="table-action-form"
                                    onsubmit="return confirm('Remove this product from your cart?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Remove from cart">
                                        <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-trash"></use></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <div class="cart-actions">
            <a href="{{ route('customer.catalog.index') }}" class="btn-modal btn-continue-shopping">
                <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-chevron-left"></use></svg>
                <span>Continue shopping</span>
            </a>
            <form method="POST" action="{{ route('customer.cart.proceed') }}" class="cart-proceed-form">
                @csrf
                <button type="submit" class="btn-submit btn-proceed-order">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-shopping-cart"></use></svg>
                    <span>Proceed to order</span>
                </button>
            </form>
        </div>
    @endif
</div>

@if(!empty($cartItems))
<div class="modal-backdrop" id="edit-cart-modal-backdrop" aria-hidden="true"></div>
<div class="modal" id="edit-cart-modal" role="dialog" aria-modal="true" aria-labelledby="edit-cart-modal-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title" id="edit-cart-modal-title">Edit cart item</h2>
            <button type="button" class="btn-close-modal" id="btn-close-edit-cart" aria-label="Close">
                <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
            </button>
        </div>
        <form method="POST" action="{{ old('_cart_update_action') }}" id="edit-cart-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="_cart_update_action" id="edit-cart-update-action" value="{{ old('_cart_update_action') }}">
            <input type="hidden" name="_cart_product_name" id="edit-cart-product-name-hidden" value="{{ old('_cart_product_name') }}">
            <input type="hidden" name="_cart_min_qty" id="edit-cart-min-qty-hidden" value="{{ old('_cart_min_qty', 1) }}">
            <div class="modal-body">
                <p class="form-helper" id="edit-cart-product-name"></p>
                <div class="form-group">
                    <label class="form-label" for="edit-cart-quantity">Quantity <span class="required">*</span></label>
                    <input
                        type="number"
                        id="edit-cart-quantity"
                        name="quantity"
                        class="form-input @error('quantity') is-invalid @enderror"
                        min="1"
                        value="{{ old('quantity', 1) }}"
                        required
                    >
                    @error('quantity')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-cart-notes">Notes</label>
                    <textarea
                        id="edit-cart-notes"
                        name="notes"
                        class="form-input @error('notes') is-invalid @enderror"
                        rows="3"
                        placeholder="Optional delivery or product notes"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btn-cancel-edit-cart">Cancel</button>
                <button type="submit" class="btn-submit">Save changes</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    var backdrop = document.getElementById('edit-cart-modal-backdrop');
    var modal = document.getElementById('edit-cart-modal');
    var form = document.getElementById('edit-cart-form');
    var productName = document.getElementById('edit-cart-product-name');
    var quantityInput = document.getElementById('edit-cart-quantity');
    var notesInput = document.getElementById('edit-cart-notes');
    var actionHidden = document.getElementById('edit-cart-update-action');
    var nameHidden = document.getElementById('edit-cart-product-name-hidden');
    var minQtyHidden = document.getElementById('edit-cart-min-qty-hidden');
    var btnClose = document.getElementById('btn-close-edit-cart');
    var btnCancel = document.getElementById('btn-cancel-edit-cart');

    if (!backdrop || !modal || !form) return;

    function openModal(action, name, quantity, notes, minQty) {
        form.action = action || '';
        if (actionHidden) actionHidden.value = action || '';
        if (nameHidden) nameHidden.value = name || '';
        if (minQtyHidden) minQtyHidden.value = String(minQty || 1);
        if (productName) {
            productName.textContent = 'Product: ' + (name || '');
        }
        if (quantityInput) {
            quantityInput.min = String(minQty || 1);
            quantityInput.value = String(quantity || minQty || 1);
        }
        if (notesInput) {
            notesInput.value = notes || '';
        }
        backdrop.classList.add('is-visible');
        modal.classList.add('is-open');
        backdrop.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (quantityInput) quantityInput.focus();
    }

    function closeModal() {
        backdrop.classList.remove('is-visible');
        modal.classList.remove('is-open');
        backdrop.setAttribute('aria-hidden', 'true');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-edit-cart-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openModal(
                btn.getAttribute('data-action'),
                btn.getAttribute('data-name'),
                btn.getAttribute('data-quantity'),
                btn.getAttribute('data-notes'),
                btn.getAttribute('data-min-qty')
            );
        });
    });

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    @if ($errors->any() && old('_method') === 'PUT')
        openModal(
            @json(old('_cart_update_action', '')),
            @json(old('_cart_product_name', '')),
            @json(old('quantity', 1)),
            @json(old('notes', '')),
            @json(old('_cart_min_qty', 1))
        );
    @endif
})();
</script>
@endpush
