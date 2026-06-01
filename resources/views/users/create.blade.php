@extends('layouts.app')

@section('title', 'New User')
@section('page-title', 'New User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">New User</li>
@endsection

@section('header-actions')
    <a href="{{ route('users.index') }}"
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

    .password-wrap { position:relative; }
    .password-wrap .field-input { padding-right:42px; }
    .password-toggle {
        position:absolute; right:12px; top:50%; transform:translateY(-50%);
        background:none; border:none; color:#9ca3af; cursor:pointer; font-size:14px; padding:0;
    }
    .password-toggle:hover { color:#374151; }

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
</style>
@endpush

@section('content')

<div style="max-width:600px;">
    <div class="card" style="border-radius:12px;">
        <div class="card-body" style="padding:28px;">

            <form action="{{ route('users.store') }}" method="POST" id="user-form">
                @csrf

                {{-- Account info --}}
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Account Information
                </div>

                <div class="field-group">
                    <label class="field-label" for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name"
                           class="field-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name') }}" placeholder="e.g. Sok Long" autofocus>
                    @error('name') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field-group">
                    <label class="field-label" for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email') }}" placeholder="e.g. soklong@hotel.com">
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                {{-- Password --}}
                <div class="form-section-title" style="margin-top:8px;">
                    <i class="fas fa-lock"></i> Password
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Password <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="password"
                               class="field-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 characters">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field-group">
                    <label class="field-label" for="password_confirmation">Confirm Password <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="field-input" placeholder="Re-enter password">
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Role --}}
                <div class="form-section-title" style="margin-top:8px;">
                    <i class="fas fa-shield-alt"></i> Role
                </div>

                @error('role') <div style="font-size:12px; color:#ef4444; margin-bottom:10px;">{{ $message }}</div> @enderror

                <div class="field-group">
                    <input type="hidden" name="role" id="role-input" value="{{ old('role', 'staff') }}">
                    <div class="role-cards">
                        <div class="role-card {{ old('role', 'staff') === 'staff' ? 'selected-staff' : '' }}"
                             id="card-staff" onclick="selectRole('staff')">
                            <div class="role-icon role-icon-staff"><i class="fas fa-user"></i></div>
                            <div>
                                <div class="role-title">Staff</div>
                                <div class="role-desc">Manage bookings, check-ins, and payments</div>
                            </div>
                        </div>
                        <div class="role-card {{ old('role') === 'admin' ? 'selected-admin' : '' }}"
                             id="card-admin" onclick="selectRole('admin')">
                            <div class="role-icon role-icon-admin"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <div class="role-title">Admin</div>
                                <div class="role-desc">Full access including users, settings, and reports</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:10px; align-items:center; margin-top:8px;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Create User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function selectRole(role) {
        document.getElementById('role-input').value = role;
        ['admin','staff'].forEach(r => {
            const card = document.getElementById('card-' + r);
            card.className = 'role-card' + (r === role ? ' selected-' + r : '');
        });
    }
</script>
@endpush