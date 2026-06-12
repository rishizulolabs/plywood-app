@extends('layouts.admin')

@section('title', 'Inquiries')
@section('page-title', 'Inquiries')
@section('page-subtitle', 'Customer quote requests')

@section('content')
<div class="content-card space-y">
    <div class="content-card-header">
        <p class="content-card-title">Inquiries</p>
        <span class="badge badge-gray">{{ $inquiries->total() }} total</span>
    </div>

    @if($inquiries->isEmpty())
        <x-admin.empty-state message="No inquiries found." />
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Inquiry #</th>
                    <th>Customer</th>
                    <th>Distributor</th>
                    <th>Status</th>
                    <th>Delivery City</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inquiries as $inquiry)
                    <tr>
                        <td>{{ $inquiry->inquiry_number }}</td>
                        <td>{{ $inquiry->customer?->name ?? '—' }}</td>
                        <td>{{ $inquiry->distributorProfile?->business_name ?? '—' }}</td>
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
@endsection
