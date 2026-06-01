@extends('layouts.guest')
@section('title', 'Create Account')
@push('styles')
<style>
    .auth-wrap { max-width:480px;margin:50px auto;padding:0 20px 60px; }
    .auth-card { background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,.07); }
    .auth-logo { text-align:center;margin-bottom:24px; }
    .auth-logo-icon { width:52px;height:52px;background:#1a56db;border-radius:13px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:22px;box-shadow:0 4px 14px rgba(26,86,219,.35); }
    .auth-title { font-size:22px;font-weight:800;color:#111827;text-align:center;margin-bottom:4px; }
    .auth-sub   { font-size:13.5px;color:#6b7280;text-align:center;margin-bottom:28px; }
    .f-label { display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px; }
    .f-label .req { color:#ef4444;margin-left:2px; }
    .f-input { width:100%;padding:11px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-family:inherit;font-size:14px;color:#111827;background:#f9fafb;outline:none;transition:border-color .2s,box-shadow .2s; }
    .f-input:focus { border-color:#1a56db;background:#fff;box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .f-input.err { border-color:#ef4444;background:#fff5f5; }
    .f-group { margin-bottom:16px; }
    .f-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
    .err-msg { font-size:12px;color:#ef4444;margin-top:4px;display:block; }
    .btn-auth { width:100%;padding:13px;border-radius:10px;border:none;background:#1a56db;color:#fff;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 3px 12px rgba(26,86,219,.28);transition:background .15s;margin-top:8px; }
    .btn-auth:hover { background:#1447b5; }
    .auth-footer { text-align:center;font-size:13.5px;color:#6b7280;margin-top:20px; }
    .auth-footer a { color:#1a56db;font-weight:600; }
    @media(max-width:480px){ .f-grid{grid-template-columns:1fr;} }
</style>
@endpush
@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo"><div class="auth-logo-icon"><i class="fas fa-hotel"></i></div></div>
        <div class="auth-title">Create your account</div>
        <div class="auth-sub">Book rooms and track your stay history</div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;font-size:13px;color:#dc2626;margin-bottom:16px;">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('guest.register.post') }}" method="POST">
            @csrf
            <div class="f-grid">
                <div class="f-group">
                    <label class="f-label">Full Name <span class="req">*</span></label>
                    <input type="text" name="name" class="f-input {{ $errors->has('name') ? 'err' : '' }}"
                           value="{{ old('name') }}" placeholder="Your full name">
                    @error('name')<span class="err-msg">{{ $message }}</span>@enderror
                </div>
                <div class="f-group">
                    <label class="f-label">Phone <span class="req">*</span></label>
                    <input type="tel" name="phone" class="f-input {{ $errors->has('phone') ? 'err' : '' }}"
                           value="{{ old('phone') }}" placeholder="+855 12 000 000">
                    @error('phone')<span class="err-msg">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="f-group">
                <label class="f-label">Email Address <span class="req">*</span></label>
                <input type="email" name="email" class="f-input {{ $errors->has('email') ? 'err' : '' }}"
                       value="{{ old('email') }}" placeholder="your@email.com">
                @error('email')<span class="err-msg">{{ $message }}</span>@enderror
            </div>
            <div class="f-grid">
                <div class="f-group">
                    <label class="f-label">Password <span class="req">*</span></label>
                    <input type="password" name="password" class="f-input" placeholder="Min. 8 characters">
                    @error('password')<span class="err-msg">{{ $message }}</span>@enderror
                </div>
                <div class="f-group">
                    <label class="f-label">Confirm Password <span class="req">*</span></label>
                    <input type="password" name="password_confirmation" class="f-input" placeholder="Repeat password">
                </div>
            </div>
            <button type="submit" class="btn-auth">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="{{ route('guest.login') }}">Sign in</a>
        </div>
    </div>
</div>
@endsection
