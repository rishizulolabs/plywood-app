@extends('layouts.admin')

@section('title', 'Edit Distributor')
@section('page-title', 'Edit Distributor')
@section('page-subtitle', 'Update distributor and reassign area')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.distributors.update', $distributor) }}" method="POST">
            @csrf
            @method('PUT')
            <h6 class="text-muted mb-3">Login Details</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $distributor->user->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $distributor->user->email) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $distributor->user->phone) }}">
                </div>
            </div>

            <hr>
            <h6 class="text-muted mb-3">Company & Area Assignment</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $distributor->company_name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Assign Area</label>
                    <select name="area_id" class="form-select">
                        <option value="">Select Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id', $distributor->area_id) == $area->id ? 'selected' : '' }}>
                                {{ $area->name }} @if($area->state)({{ $area->state }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $distributor->gst_number) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $distributor->address) }}</textarea>
                </div>
                <div class="col-12 mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', $distributor->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Distributor</button>
                <a href="{{ route('admin.distributors.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
