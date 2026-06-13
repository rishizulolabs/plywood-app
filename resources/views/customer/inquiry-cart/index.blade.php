@extends('layouts.customer')

@section('title', 'Inquiry Cart')
@section('page-title', 'Inquiry Cart')
@section('page-subtitle', 'Products you want to request quotes for')

@section('content')
<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Cart items</p>
        <span class="badge badge-gray">{{ count($cartItems) }} items</span>
    </div>

    @if(empty($cartItems))
        <x-admin.empty-state message="Your inquiry cart is empty. Browse the catalog and add products to request a quote." />
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
                </tr>
            </thead>
            <tbody>
                @foreach ($cartItems as $item)
                    <tr>
                        <td>{{ $item['product_name'] ?? 'Product' }}</td>
                        <td>{{ $item['distributor'] ?? '—' }}</td>
                        <td>{{ $item['quantity'] ?? 1 }}</td>
                        <td>{{ $item['notes'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
