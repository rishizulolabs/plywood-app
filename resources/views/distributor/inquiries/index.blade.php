@extends('layouts.distributor')

@section('title', 'Inquiries')
@section('page-title', 'Inquiries')
@section('page-subtitle', 'Customer quote requests assigned to you')

@section('content')
@unless($profile)
    <div class="alert alert-warning" role="alert">
        <svg class="alert-icon" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg>
        <p class="alert-message">Complete your business profile to receive inquiries.</p>
    </div>
@else
    <div class="content-card space-y">
        <div class="content-card-header">
            <p class="content-card-title">Inquiries</p>
            <span class="badge badge-gray">{{ $inquiries->total() }} total</span>
        </div>

        @if($inquiries->isEmpty())
            <x-admin.empty-state message="No inquiries yet. They will appear here when customers request quotes." />
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Inquiry #</th>
                        <th>Customer</th>
                        <th>Products</th>
                        <th>Status</th>
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
                            <td>{{ $inquiry->customer?->name ?? '—' }}</td>
                            <td>{{ $productLabel ?: '—' }}</td>
                            <td><span class="badge badge-yellow">{{ ucfirst($inquiry->status) }}</span></td>
                            <td>{{ $inquiry->delivery_city ?? '—' }}</td>
                            <td>{{ $inquiry->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <x-admin.pagination :paginator="$inquiries" />
        @endif
    </div>
@endunless
@endsection
