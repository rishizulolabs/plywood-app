@extends('layouts.admin')

@section('title', 'Warranty Claims')
@section('page-title', 'Warranty Claims')
@section('page-subtitle', 'Customer warranty requests from the mobile app')

@section('content')
<div class="stat-cards">
    @foreach ($stats as $stat)
        <div class="stat-card">
            <div class="stat-card-icon stat-card-icon-{{ $stat['color'] }}">
                <svg class="icon-svg" aria-hidden="true"><use href="#{{ $stat['icon'] }}"></use></svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-label">{{ $stat['label'] }}</p>
                <p class="stat-value">{{ $stat['value'] }}</p>
                <p class="stat-desc">{{ $stat['desc'] }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="content-card space-y" style="margin-top: 1.5rem;">
    <div class="content-card-header">
        <p class="content-card-title">Warranty claims</p>
        <span class="badge badge-gray">{{ $claims->total() }} total</span>
    </div>

    @if($claims->isEmpty())
        <x-admin.empty-state message="No warranty claims submitted yet." />
    @else
        <div class="table-responsive table-responsive-inset">
        <table class="data-table data-table-bordered">
            <thead>
                <tr>
                    <th>Claim #</th>
                    <th>Customer</th>
                    <th>Order</th>
                    <th>Product</th>
                    <th>Complaint</th>
                    <th>Photos</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($claims as $claim)
                    <tr>
                        <td>{{ $claim->claim_number }}</td>
                        <td>
                            <div>{{ $claim->user?->name ?? '—' }}</div>
                            <div class="text-muted text-sm">{{ $claim->user?->email ?? '' }}</div>
                        </td>
                        <td>{{ $claim->order_number }}</td>
                        <td>{{ $claim->product_name ?? '—' }}</td>
                        <td style="max-width: 260px; white-space: normal;">{{ $claim->complaint }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                @forelse ($claim->getMedia('claim_photos') as $photo)
                                    <a href="{{ $photo->getUrl() }}" target="_blank" rel="noopener">
                                        <img
                                            src="{{ $photo->getUrl() }}"
                                            alt="Claim photo"
                                            style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid #e8e8e8;"
                                        >
                                    </a>
                                @empty
                                    —
                                @endforelse
                            </div>
                        </td>
                        <td>
                            @php
                                $badge = match ($claim->status) {
                                    'approved', 'resolved' => 'badge-green',
                                    'rejected' => 'badge-gray',
                                    'reviewing' => 'badge-blue',
                                    default => 'badge-yellow',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($claim->status) }}</span>
                        </td>
                        <td>{{ $claim->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.warranty-claims.status', $claim) }}" class="space-y-sm">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-input" style="min-width: 130px;">
                                    @foreach (['pending', 'reviewing', 'approved', 'rejected', 'resolved'] as $status)
                                        <option value="{{ $status }}" @selected($claim->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <textarea
                                    name="admin_notes"
                                    class="form-input"
                                    rows="2"
                                    placeholder="Admin notes"
                                    style="min-width: 180px;"
                                >{{ old('admin_notes', $claim->admin_notes) }}</textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <x-admin.pagination :paginator="$claims" />
    @endif
</div>
@endsection
