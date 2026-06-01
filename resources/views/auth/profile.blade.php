@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('breadcrumb')
    <li>My Profile</li>
@endsection

@push('styles')
<style>
    .profile-layout {
        display: grid;
        grid-template-columns: 290px 1fr;
        gap: 22px;
        align-items: start;
    }

    /* ── Profile card (left) ── */
    .profile-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
    }
    .profile-card-hero {
        background: linear-gradient(135deg, #1a56db 0%, #3b82f6 60%, #818cf8 100%);
        border-radius: 14px 14px 0 0;
        padding: 24px 20px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    .hero-avatar {
        width: 68px; height: 68px;
        border-radius: 50%;
        background: rgba(255,255,255,.22);
        border: 3px solid rgba(255,255,255,.55);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 26px; font-weight: 700;
        box-shadow: 0 4px 14px rgba(0,0,0,.18);
    }
    .hero-name  { font-size: 15px; font-weight: 700; color: #fff; }
    .hero-email { font-size: 12px; color: rgba(255,255,255,.72); }

    .profile-card-body { padding: 18px 20px 20px; }

    .profile-badges { display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
    .badge-role   { padding: 3px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600; background: #eff6ff; color: #1d4ed8; }
    .badge-active { padding: 3px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600; background: #f0fdf4; color: #15803d; }
    .badge-inactive { padding: 3px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600; background: #fef2f2; color: #dc2626; }

    .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
    .stat-tile { background: #f9fafb; border-radius: 10px; padding: 12px 8px; text-align: center; }
    .stat-tile-val { font-size: 20px; font-weight: 700; color: #111827; }
    .stat-tile-lbl { font-size: 11px; color: #9ca3af; margin-top: 2px; }

    .meta-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-top: 1px solid #f3f4f6; font-size: 13px; }
    .meta-row i { width: 15px; color: #9ca3af; font-size: 12px; flex-shrink: 0; }
    .meta-lbl { font-size: 10.5px; color: #9ca3af; }
    .meta-val { font-weight: 600; color: #374151; }

    /* ── Form sections (right) ── */
    .form-section { background: #fff; border: 1px solid var(--border); border-radius: 14px; margin-bottom: 20px; }
    .fs-header {
        padding: 16px 22px; border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 12px;
    }
    .fs-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .fs-title { font-size: 14px; font-weight: 700; color: #111827; }
    .fs-sub   { font-size: 12px; color: #9ca3af; margin-top: 1px; }
    .fs-body  { padding: 22px; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-full { grid-column: span 2; }

    .f-label { display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .f-label .req { color: #ef4444; margin-left: 2px; }
    .f-hint  { font-size: 11.5px; color: #9ca3af; margin-top: 4px; }

    .f-wrap { position: relative; }
    .f-wrap .f-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px; pointer-events: none; }
    .f-input {
        width: 100%; padding: 10px 13px 10px 38px;
        border: 1.5px solid #e5e7eb; border-radius: 9px;
        font-family: inherit; font-size: 13.5px; color: #111827;
        background: #f9fafb; outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .f-input.no-icon { padding-left: 13px; }
    .f-input:focus   { border-color: #1a56db; background: #fff; box-shadow: 0 0 0 3px rgba(26,86,219,.08); }
    .f-input.err     { border-color: #ef4444; background: #fff5f5; }
    .err-msg         { font-size: 11.5px; color: #ef4444; margin-top: 4px; display: block; }
    .f-input.readonly { background: #f3f4f6; color: #6b7280; cursor: default; }

    .btn-submit {
        padding: 10px 24px; border-radius: 9px; border: none;
        color: #fff; font-family: inherit; font-size: 13.5px; font-weight: 700;
        cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
        transition: opacity .18s, transform .15s;
    }
    .btn-submit:hover { opacity: .88; transform: translateY(-1px); }
    .btn-blue { background: #1a56db; box-shadow: 0 2px 10px rgba(26,86,219,.25); }
    .btn-red  { background: #dc2626; box-shadow: 0 2px 10px rgba(220,38,38,.22); }

    /* password strength */
    .strength-wrap { margin-top: 6px; }
    .strength-bar  { height: 4px; border-radius: 2px; background: #f3f4f6; overflow: hidden; margin-bottom: 3px; }
    .strength-fill { height: 100%; border-radius: 2px; width: 0; transition: width .3s, background .3s; }
    .strength-lbl  { font-size: 11.5px; font-weight: 600; }

    @media (max-width: 860px) {
        .profile-layout { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
        .form-full { grid-column: span 1; }
    }
</style>
@endpush

@section('content')

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;color:#15803d;border-radius:10px;padding:12px 16px;font-size:13.5px;font-weight:500;display:flex;align-items:center;gap:9px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="profile-layout">

    {{-- ── LEFT: profile card ── --}}
    <div class="profile-card">

        {{-- Hero section — avatar inside gradient, no absolute positioning --}}
        <div class="profile-card-hero">
            <div class="hero-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="hero-name">{{ $user->name }}</div>
            <div class="hero-email">{{ $user->email }}</div>
        </div>

        <div class="profile-card-body">

            {{-- Badges --}}
            <div class="profile-badges">
                <span class="badge-role">
                    <i class="fas fa-{{ $user->isAdmin() ? 'shield-alt' : 'user' }}" style="margin-right:4px;"></i>
                    {{ ucfirst($user->role) }}
                </span>
                <span class="{{ $user->isActive() ? 'badge-active' : 'badge-inactive' }}">
                    {{ ucfirst($user->status) }}
                </span>
            </div>

            {{-- Activity stats --}}
            <div class="stat-grid">
                <div class="stat-tile">
                    <div class="stat-tile-val">{{ $stats['bookings'] }}</div>
                    <div class="stat-tile-lbl">Bookings</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-tile-val">{{ $stats['check_ins'] }}</div>
                    <div class="stat-tile-lbl">Check-ins</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-tile-val">{{ $stats['check_outs'] }}</div>
                    <div class="stat-tile-lbl">Check-outs</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-tile-val">{{ $stats['payments'] }}</div>
                    <div class="stat-tile-lbl">Payments</div>
                </div>
            </div>

            {{-- Meta --}}
            <div class="meta-row">
                <i class="fas fa-calendar-plus"></i>
                <div>
                    <div class="meta-lbl">Member since</div>
                    <div class="meta-val">{{ $user->created_at->format('M d, Y') }}</div>
                </div>
            </div>
            <div class="meta-row">
                <i class="fas fa-clock"></i>
                <div>
                    <div class="meta-lbl">Last updated</div>
                    <div class="meta-val">{{ $user->updated_at->diffForHumans() }}</div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── RIGHT: forms ── --}}
    <div>

        {{-- Account info --}}
        <div class="form-section">
            <div class="fs-header">
                <div class="fs-icon" style="background:#eff6ff;">
                    <i class="fas fa-user" style="color:#1a56db;"></i>
                </div>
                <div>
                    <div class="fs-title">Account Information</div>
                    <div class="fs-sub">Update your name and email address</div>
                </div>
            </div>
            <div class="fs-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-grid">

                        <div class="form-full">
                            <label class="f-label">Full Name <span class="req">*</span></label>
                            <div class="f-wrap">
                                <i class="fas fa-user f-icon"></i>
                                <input type="text" name="name"
                                       class="f-input {{ $errors->has('name') ? 'err' : '' }}"
                                       value="{{ old('name', $user->name) }}">
                            </div>
                            @error('name')<span class="err-msg">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-full">
                            <label class="f-label">Email Address <span class="req">*</span></label>
                            <div class="f-wrap">
                                <i class="fas fa-envelope f-icon"></i>
                                <input type="email" name="email"
                                       class="f-input {{ $errors->has('email') ? 'err' : '' }}"
                                       value="{{ old('email', $user->email) }}">
                            </div>
                            @error('email')<span class="err-msg">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-full" style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="btn-submit btn-blue">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- Change password --}}
        <div class="form-section">
            <div class="fs-header">
                <div class="fs-icon" style="background:#fef2f2;">
                    <i class="fas fa-lock" style="color:#dc2626;"></i>
                </div>
                <div>
                    <div class="fs-title">Change Password</div>
                    <div class="fs-sub">Use a strong password of at least 8 characters</div>
                </div>
            </div>
            <div class="fs-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-grid">

                        <div class="form-full">
                            <label class="f-label">Current Password <span class="req">*</span></label>
                            <div class="f-wrap">
                                <i class="fas fa-lock f-icon"></i>
                                <input type="password" name="current_password"
                                       class="f-input {{ $errors->has('current_password') ? 'err' : '' }}"
                                       placeholder="Enter current password">
                            </div>
                            @error('current_password')<span class="err-msg">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="f-label">New Password <span class="req">*</span></label>
                            <div class="f-wrap">
                                <i class="fas fa-key f-icon"></i>
                                <input type="password" name="new_password" id="new_pw"
                                       class="f-input {{ $errors->has('new_password') ? 'err' : '' }}"
                                       placeholder="Min. 8 characters"
                                       oninput="pwStrength(this.value)">
                            </div>
                            @error('new_password')<span class="err-msg">{{ $message }}</span>@enderror
                            <div class="strength-wrap" id="sw" style="display:none;">
                                <div class="strength-bar"><div class="strength-fill" id="sf"></div></div>
                                <span class="strength-lbl" id="sl"></span>
                            </div>
                        </div>

                        <div>
                            <label class="f-label">Confirm New Password <span class="req">*</span></label>
                            <div class="f-wrap">
                                <i class="fas fa-key f-icon"></i>
                                <input type="password" name="new_password_confirmation"
                                       class="f-input" placeholder="Repeat new password">
                            </div>
                        </div>

                        <div class="form-full" style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="btn-submit btn-red">
                                <i class="fas fa-lock"></i> Update Password
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- Read-only details --}}
        <div class="form-section">
            <div class="fs-header">
                <div class="fs-icon" style="background:#f0fdf4;">
                    <i class="fas fa-info-circle" style="color:#15803d;"></i>
                </div>
                <div>
                    <div class="fs-title">Account Details</div>
                    <div class="fs-sub">Managed by administrators</div>
                </div>
            </div>
            <div class="fs-body">
                <div class="form-grid">
                    <div>
                        <label class="f-label">Role</label>
                        <input class="f-input no-icon readonly" readonly
                               value="{{ ucfirst($user->role) }}">
                        <p class="f-hint">Contact an admin to change your role</p>
                    </div>
                    <div>
                        <label class="f-label">Status</label>
                        <input class="f-input no-icon readonly" readonly
                               value="{{ ucfirst($user->status) }}">
                        <p class="f-hint">Contact an admin to change your status</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function pwStrength(v) {
    var sw = document.getElementById('sw'),
        sf = document.getElementById('sf'),
        sl = document.getElementById('sl');
    if (!v) { sw.style.display = 'none'; return; }
    sw.style.display = 'block';
    var s = 0;
    if (v.length >= 8)  s++;
    if (v.length >= 12) s++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    var lvl = [
        {w:'20%', bg:'#ef4444', t:'Very weak',   c:'#ef4444'},
        {w:'40%', bg:'#f97316', t:'Weak',         c:'#ea580c'},
        {w:'60%', bg:'#eab308', t:'Fair',         c:'#a16207'},
        {w:'80%', bg:'#3b82f6', t:'Strong',       c:'#1d4ed8'},
        {w:'100%',bg:'#22c55e', t:'Very strong',  c:'#15803d'},
    ][Math.min(s, 4)];
    sf.style.width = lvl.w; sf.style.background = lvl.bg;
    sl.textContent = lvl.t; sl.style.color = lvl.c;
}
</script>
@endpush