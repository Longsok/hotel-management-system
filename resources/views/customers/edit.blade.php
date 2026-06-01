@extends('layouts.app')

@section('title', 'Edit ' . $customer->name)
@section('page-title', 'Edit Customer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('header-actions')
    <a href="{{ route('customers.show', $customer) }}"
       style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@push('styles')
<style>
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    @media (max-width:700px) { .form-grid { grid-template-columns:1fr; } }

    .form-section-title {
        font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
        color:#9ca3af; margin-bottom:16px; padding-bottom:10px;
        border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px;
    }
    .field-group { margin-bottom:18px; }
    .field-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; display:block; }
    .field-label .required { color:#ef4444; margin-left:2px; }
    .field-input, .field-select, .field-textarea {
        width:100%; height:40px; padding:0 12px;
        border:1.5px solid #e5e7eb; border-radius:8px;
        font-family:inherit; font-size:13.5px; color:#111827;
        background:#fff; outline:none; transition:border-color .2s, box-shadow .2s;
    }
    .field-textarea { height:auto; padding:10px 12px; resize:vertical; }
    .field-input:focus, .field-select:focus, .field-textarea:focus {
        border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08);
    }
    .field-input.is-invalid, .field-select.is-invalid, .field-textarea.is-invalid {
        border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.08);
    }
    .field-error { font-size:12px; color:#ef4444; margin-top:5px; }

    .btn-submit {
        padding:10px 24px; border-radius:9px; border:none;
        background:#1a56db; color:#fff; font-family:inherit;
        font-size:14px; font-weight:600; cursor:pointer;
        display:inline-flex; align-items:center; gap:8px; transition:background .15s;
    }
    .btn-submit:hover { background:#1447b5; }
    .btn-cancel {
        padding:10px 20px; border-radius:9px; border:1.5px solid #e5e7eb;
        background:#fff; color:#374151; font-family:inherit;
        font-size:14px; font-weight:600; text-decoration:none;
        display:inline-flex; align-items:center; gap:8px;
    }
    .btn-cancel:hover { border-color:#d1d5db; background:#f9fafb; color:#374151; }
</style>
@endpush

@section('content')

<div style="max-width:800px;">
    <div class="card" style="border-radius:12px;">
        <div class="card-body" style="padding:28px;">

            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf @method('PUT')

                {{-- ── Personal information ── --}}
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Personal Information
                </div>

                <div class="form-grid">
                    <div class="field-group">
                        <label class="field-label" for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name"
                               class="field-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name', $customer->name) }}" autofocus>
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email"
                               class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               value="{{ old('email', $customer->email) }}">
                        @error('email')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone"
                               class="field-input {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                               value="{{ old('phone', $customer->phone) }}">
                        @error('phone')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="nationality">Nationality <span class="required">*</span></label>
                        <input type="text" id="nationality" name="nationality"
                               class="field-input {{ $errors->has('nationality') ? 'is-invalid' : '' }}"
                               value="{{ old('nationality', $customer->nationality) }}">
                        @error('nationality')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="status">Status</label>
                        <select id="status" name="status"
                                class="field-select {{ $errors->has('status') ? 'is-invalid' : '' }}">
                            <option value="active"   {{ old('status', $customer->status) === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="address">Address</label>
                    <textarea id="address" name="address" rows="2"
                              class="field-textarea {{ $errors->has('address') ? 'is-invalid' : '' }}"
                              placeholder="Street, city, country…">{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── Identity documents ── --}}
                <div class="form-section-title" style="margin-top:8px;">
                    <i class="fas fa-id-card"></i> Identity Documents
                </div>

                <div class="form-grid">
                    <div class="field-group">
                        <label class="field-label" for="id_card">National ID Card</label>
                        <input type="text" id="id_card" name="id_card"
                               class="field-input {{ $errors->has('id_card') ? 'is-invalid' : '' }}"
                               value="{{ old('id_card', $customer->id_card) }}">
                        @error('id_card')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="passport">Passport Number</label>
                        <input type="text" id="passport" name="passport"
                               class="field-input {{ $errors->has('passport') ? 'is-invalid' : '' }}"
                               value="{{ old('passport', $customer->passport) }}">
                        @error('passport')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ── Actions ── --}}
                <div style="display:flex; gap:10px; align-items:center; margin-top:8px;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('customers.show', $customer) }}" class="btn-cancel">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection