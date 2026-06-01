@extends('layouts.app')

@section('title', 'Edit ' . $user->name)
@section('page-title', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('header-actions')
    <a href="{{ route('users.show', $user) }}"
       style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@push('styles')
<style>
    .field-group { margin-bottom:20px; }
    .field-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; display:block; }
    .field-label .required { color:#ef4444; margin-left:2px; }
    .field-input, .field-select {
        width:100%; height:40px; padding:0 12px;
        border:1.5px solid #e5e7eb; border-radius:8px;
        font-family:inherit; font-size:13.5px; color:#111827;
        background:#fff; outline:none; transition:border-color .2s, box-shadow .2s;
    }
    .field-input:focus, .field-select:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .field-input.is-invalid, .field-select.is-invalid { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.08); }
    .field-error { font-size:12px; color:#ef4444; margin-top:5px; }
    .field-hint  { font-size:12px; color:#9ca3af; margin-top:4px; }

    .form-section-title {
        font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
        color:#9ca3af; margin-bottom:16px; padding-bottom:10px;
        border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px;
    }

    .role-cards { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .role-card {
        padding:14px 16px; border:1.5px solid #e5e7eb; border-radius:10px;
        cursor:pointer; transition:all .15s; display:flex; align-items:flex-start; gap:12px;
    }
    .role-card:hover { border-color:#93c5fd; background:#f8faff; }
    .role-card.selected-admin { border-color:#f59e0b; background:#fffbeb; }
    .role-card.selected-staff { border-color:#1a56db; background:#eff6ff; }
    .role-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
    .role-icon-admin { background:#fff7ed; color:#c2410c; }
    .role-icon-staff { background:#eff6ff; color:#1d4ed8; }
    .role-title { font-size:13.5px; font-weight:600; color:#111827; margin-bottom:2px; }
    .role-desc  { font-size:12px; color:#9ca3af; }

    .status-cards { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .status-card {
        padding:12px 16px; border:1.5px solid #e5e7eb; border-radius:10px;
        cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:10px;
    }
    .status-card:hover { border-color:#d1d5db; }
    .status-card.selected-active   { border-color:#22c55e; background:#f0fdf4; }
    .status-card.selected-inactive { border-color:#ef4444; background:#fef2f2; }
    .status-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
    .status-active   { background:#22c55e; }
    .status-inactive { background:#ef4444; }
    .status-title { font-size:13.5px; font-weight:600; color:#111827; }

    .btn-submit {
        padding:10px 24px; border-radius:9px; border:none; background:#1a56db; color:#fff;
        font-family:inherit; font-size:14px; font-weight:600; cursor:pointer;
        display:inline-flex; align-items:center; gap:8px; transition:background .15s;
    }
    .btn-submit:hover { background:#1447b5; }
    .btn-cancel {
        padding:10px 20px; border-radius:9px; border:1.5px solid #e5e7eb;
        background:#fff; color:#374151; font-family:inherit; font-size:14px; font-weight:600;
        text-decoration:none; display:inline-flex; align-items:center; gap:8px;
    }
    .btn-cancel:hover { border-color:#d1d5db; background:#f9fafb; color:#374151; }

    .self-note {
        display:flex; align-items:center; gap:8px; padding:10px 14px;
        border-radius:8px; background:#eff6ff; border:1px solid #bfdbfe;
        font-size:13px; color:#1d4ed8; margin-bottom:20px;
    }
</style>
@endpush

@section('content')

<div style="max-width:600px;">
    <div class="card" style="border-radius:12px;">
        <div class="card-body" style="padding:28px;">

            @if($user->id === auth()->id())
            <div class="self-note">
                <i class="fas fa-info-circle"></i>
                You are editing your own account.
            </div>
            @endif

            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf @method('PUT')

                {{-- Account info --}}
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Account Information
                </div>

                <div class="field-group">
                    <label class="field-label" for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name"
                           class="field-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name', $user->name) }}" autofocus>
                    @error('name') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field-group">
                    <label class="field-label" for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email', $user->email) }}">
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                {{-- Role --}}
                <div class="form-section-title" style="margin-top:8px;">
                    <i class="fas fa-shield-alt"></i> Role
                </div>

                @error('role') <div style="font-size:12px; color:#ef4444; margin-bottom:10px;">{{ $message }}</div> @enderror

                <div class="field-group">
                    @php $currentRole = old('role', $user->role); @endphp
                    <input type="hidden" name="role" id="role-input" value="{{ $currentRole }}">
                    <div class="role-cards">
                        <div class="role-card {{ $currentRole === 'staff' ? 'selected-staff' : '' }}"
                             id="card-staff" onclick="selectRole('staff')">
                            <div class="role-icon role-icon-staff"><i class="fas fa-user"></i></div>
                            <div>
                                <div class="role-title">Staff</div>
                                <div class="role-desc">Manage bookings, check-ins, and payments</div>
                            </div>
                        </div>
                        <div class="role-card {{ $currentRole === 'admin' ? 'selected-admin' : '' }}"
                             id="card-admin" onclick="selectRole('admin')">
                            <div class="role-icon role-icon-admin"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <div class="role-title">Admin</div>
                                <div class="role-desc">Full access including users, settings, and reports</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="form-section-title" style="margin-top:8px;">
                    <i class="fas fa-toggle-on"></i> Status
                </div>

                <div class="field-group">
                    @php $currentStatus = old('status', $user->status); @endphp
                    <input type="hidden" name="status" id="status-input" value="{{ $currentStatus }}">
                    <div class="status-cards">
                        <div class="status-card {{ $currentStatus === 'active' ? 'selected-active' : '' }}"
                             id="card-active" onclick="selectStatus('active')">
                            <span class="status-dot status-active"></span>
                            <span class="status-title">Active</span>
                        </div>
                        <div class="status-card {{ $currentStatus === 'inactive' ? 'selected-inactive' : '' }}"
                             id="card-inactive" onclick="selectStatus('inactive')">
                            <span class="status-dot status-inactive"></span>
                            <span class="status-title">Inactive</span>
                        </div>
                    </div>
                    @if($user->id === auth()->id())
                        <div class="field-hint" style="margin-top:8px;">
                            <i class="fas fa-info-circle"></i>
                            Deactivating your own account will log you out immediately.
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:10px; align-items:center; margin-top:8px;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('users.show', $user) }}" class="btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function selectRole(role) {
        document.getElementById('role-input').value = role;
        ['admin','staff'].forEach(r => {
            document.getElementById('card-' + r).className =
                'role-card' + (r === role ? ' selected-' + r : '');
        });
    }

    function selectStatus(status) {
        document.getElementById('status-input').value = status;
        ['active','inactive'].forEach(s => {
            document.getElementById('card-' + s).className =
                'status-card' + (s === status ? ' selected-' + s : '');
        });
    }
</script>
@endpush