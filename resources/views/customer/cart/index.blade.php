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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cart-actions">
            <a href="{{ route('customer.catalog.index') }}" class="btn-cancel">Continue shopping</a>
            <form method="POST" action="{{ route('customer.cart.proceed') }}">
                @csrf
                <button type="submit" class="btn-submit">Proceed</button>
            </form>
        </div>
    @endif
</div>
@endsection
