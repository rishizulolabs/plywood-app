@extends('layouts.customer')

@section('title', 'My Inquiries')
@section('page-title', 'My Inquiries')
@section('page-subtitle', 'Track your quote requests and distributor responses')

@section('content')
<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Inquiries</p>
        <span class="badge badge-gray">{{ $inquiries->total() }} total</span>
    </div>

    @if($inquiries->isEmpty())
        <x-admin.empty-state message="No inquiries yet. Add products to your cart and submit a quote request." />
        <div style="padding: 0 1.5rem 1.5rem; text-align: center;">
            <a href="{{ route('customer.inquiry-cart.index') }}" class="btn-add">
                <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-shopping-cart"></use></svg>
                <span>Go to inquiry cart</span>
            </a>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Inquiry #</th>
                    <th>Distributor</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Quote</th>
                    <th>Delivery City</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inquiries as $inquiry)
                    @php
                        $productLabel = $inquiry->items
                            ->map(fn ($item) => ($item->product?->name ?? 'Product').' × '.$item->quantity)
                            ->join(', ');
                    @endphp
                    <tr>
                        <td>{{ $inquiry->inquiry_number }}</td>
                        <td>{{ $inquiry->distributorProfile?->business_name ?? '—' }}</td>
                        <td>{{ $productLabel ?: '—' }}</td>
                        <td><span class="badge badge-yellow">{{ ucfirst($inquiry->status) }}</span></td>
                        <td>
                            @if($inquiry->quote)
                                <span class="badge badge-green">{{ format_inr($inquiry->quote->total) }}</span>
                            @else
                                <span class="badge badge-gray">Pending</span>
                            @endif
                        </td>
                        <td>{{ $inquiry->delivery_city ?? '—' }}</td>
                        <td>{{ $inquiry->created_at?->format('d M Y') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-admin.pagination :paginator="$inquiries" />
    @endif
</div>
@endsection
