@extends('layouts.app')

@section('title', 'Edit ' . $extraService->name)
@section('page-title', 'Edit Service')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('extra-services.index') }}">Extra Services</a></li>
    <li class="breadcrumb-item active">{{ $extraService->name }}</li>
@endsection

@section('header-actions')
    <a href="{{ route('extra-services.index') }}"
       style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@push('styles')
<style>
    .field-group { margin-bottom:20px; }
    .field-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; display:block; }
    .field-label .required { color:#ef4444; margin-left:2px; }
    .field-label .hint { font-weight:400; color:#9ca3af; font-size:12px; margin-left:6px; }
    .field-input, .field-textarea {
        width:100%; height:40px; padding:0 12px;
        border:1.5px solid #e5e7eb; border-radius:8px;
        font-family:inherit; font-size:13.5px; color:#111827;
        background:#fff; outline:none; transition:border-color .2s, box-shadow .2s;
    }
    .field-textarea { height:auto; padding:10px 12px; resize:vertical; }
    .field-input:focus, .field-textarea:focus {
        border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08);
    }
    .field-input.is-invalid, .field-textarea.is-invalid {
        border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.08);
    }
    .field-error { font-size:12px; color:#ef4444; margin-top:5px; }

    .price-wrap { position:relative; }
    .price-prefix {
        position:absolute; left:12px; top:50%; transform:translateY(-50%);
        font-size:14px; font-weight:600; color:#6b7280; pointer-events:none;
    }
    .price-wrap .field-input { padding-left:24px; }

    .toggle-wrap {
        display:flex; align-items:center; gap:12px;
        padding:14px 16px; border:1.5px solid #e5e7eb; border-radius:8px;
        background:#f9fafb; cursor:pointer; user-select:none;
    }
    .toggle-wrap:hover { border-color:#d1d5db; }
    .toggle-switch {
        width:40px; height:22px; border-radius:11px;
        position:relative; flex-shrink:0; transition:background .2s;
    }
    .toggle-label { font-size:13.5px; font-weight:600; color:#374151; }
    .toggle-desc  { font-size:12px; color:#9ca3af; }

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

    .form-section-title {
        font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
        color:#9ca3af; margin-bottom:16px; padding-bottom:10px;
        border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px;
    }

    .usage-info {
        display:flex; align-items:center; gap:10px;
        padding:12px 16px; border-radius:8px; background:#fffbeb;
        border:1px solid #fde68a; font-size:13px; color:#92400e; margin-bottom:20px;
    }
</style>
@endpush

@section('content')

<div style="max-width:640px;">

    {{-- Usage warning if service has been used in bookings --}}
    @if($extraService->bookingServices()->exists())
    <div class="usage-info mb-4">
        <i class="fas fa-exclamation-triangle" style="color:#f59e0b; flex-shrink:0;"></i>
        This service has been used in bookings. Changing the price will only affect future bookings.
    </div>
    @endif

    <div class="card" style="border-radius:12px;">
        <div class="card-body" style="padding:28px;">

            <form action="{{ route('extra-services.update', $extraService) }}" method="POST">
                @csrf @method('PUT')

                <div class="form-section-title">
                    <i class="fas fa-concierge-bell"></i> Service Details
                </div>

                {{-- Name --}}
                <div class="field-group">
                    <label class="field-label" for="name">
                        Service Name <span class="required">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           class="field-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name', $extraService->name) }}"
                           autofocus>
                    @error('name')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="field-group">
                    <label class="field-label" for="description">
                        Description <span class="hint">(optional)</span>
                    </label>
                    <textarea id="description" name="description" rows="3"
                              class="field-textarea {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              placeholder="Briefly describe what this service includes…">{{ old('description', $extraService->description) }}</textarea>
                    @error('description')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Price --}}
                <div class="field-group">
                    <label class="field-label" for="price">
                        Price (per use) <span class="required">*</span>
                    </label>
                    <div class="price-wrap">
                        <span class="price-prefix">$</span>
                        <input type="number" id="price" name="price"
                               class="field-input {{ $errors->has('price') ? 'is-invalid' : '' }}"
                               value="{{ old('price', $extraService->price) }}"
                               min="0" step="0.01">
                    </div>
                    @error('price')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Active toggle --}}
                <div class="field-group">
                    <label class="field-label">Status</label>
                    <label style="display:block; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1"
                               id="is_active" style="display:none;"
                               {{ old('is_active', $extraService->is_active) ? 'checked' : '' }}
                               onchange="updateToggle(this)">
                        @php $active = old('is_active', $extraService->is_active); @endphp
                        <div class="toggle-wrap">
                            <div class="toggle-switch" id="toggle-switch"
                                 style="background: {{ $active ? '#1a56db' : '#d1d5db' }};">
                                <span style="position:absolute; top:3px; left:{{ $active ? '21px' : '3px' }}; transition:left .2s; width:16px; height:16px; border-radius:50%; background:#fff; display:block; box-shadow:0 1px 3px rgba(0,0,0,.2);"></span>
                            </div>
                            <div>
                                <div class="toggle-label" id="toggle-label">
                                    {{ $active ? 'Active' : 'Inactive' }}
                                </div>
                                <div class="toggle-desc">
                                    Active services can be added to bookings during check-out
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:10px; align-items:center; margin-top:8px;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('extra-services.index') }}" class="btn-cancel">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- Danger zone --}}
    <div class="card mt-4" style="border-radius:12px; border:1px solid #fecaca;">
        <div class="card-header" style="background:transparent; border-bottom:1px solid #fecaca; padding:14px 20px;">
            <h6 style="margin:0; font-size:13px; font-weight:700; color:#dc2626; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-exclamation-triangle"></i> Danger Zone
            </h6>
        </div>
        <div class="card-body">
            <p style="font-size:13px; color:#6b7280; margin-bottom:12px;">
                Permanently delete this service. Services that have been used in bookings cannot be deleted.
            </p>
            <form action="{{ route('extra-services.destroy', $extraService) }}" method="POST"
                  onsubmit="return confirm('Delete \'{{ addslashes($extraService->name) }}\'? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        style="padding:7px 16px; border-radius:8px; border:1.5px solid #fca5a5; background:#fff; color:#dc2626; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    <i class="fas fa-trash-alt"></i> Delete Service
                </button>
            </form>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    function updateToggle(checkbox) {
        const sw   = document.getElementById('toggle-switch');
        const knob = sw.querySelector('span');
        const label = document.getElementById('toggle-label');
        if (checkbox.checked) {
            sw.style.background = '#1a56db';
            knob.style.left = '21px';
            label.textContent = 'Active';
        } else {
            sw.style.background = '#d1d5db';
            knob.style.left = '3px';
            label.textContent = 'Inactive';
        }
    }
</script>
@endpush