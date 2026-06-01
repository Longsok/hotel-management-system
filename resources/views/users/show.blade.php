@extends('layouts.app')

@section('title', $user->name)
@section('page-title', $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('header-actions')
    <div style="display:flex; gap:8px;">
        <a href="{{ route('users.edit', $user) }}"
           style="padding:7px 16px; border-radius:8px; border:none; background:#1a56db; color:#fff; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
            <i class="fas fa-pencil-alt"></i> Edit
        </a>
        <a href="{{ route('users.index') }}"
           style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@endsection

@push('styles')
<style>
    .user-avatar-lg {
        width:68px; height:68px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        font-size:26px; font-weight:700; color:#fff; flex-shrink:0;
    }
    .avatar-admin { background:linear-gradient(135deg,#f59e0b,#ef4444); }
    .avatar-staff { background:linear-gradient(135deg,#818cf8,#1a56db); }

    .role-badge {
        display:inline-flex; align-items:center; gap:5px;
        padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;
    }
    .role-admin { background:#fff7ed; color:#c2410c; }
    .role-staff { background:#eff6ff; color:#1d4ed8; }

    .status-badge {
        display:inline-flex; align-items:center; gap:5px;
        padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;
    }
    .status-badge .dot { width:7px; height:7px; border-radius:50%; }
    .badge-active   { background:#f0fdf4; color:#15803d; } .badge-active .dot { background:#22c55e; }
    .badge-inactive { background:#fef2f2; color:#dc2626; } .badge-inactive .dot { background:#ef4444; }

    .detail-row {
        display:flex; justify-content:space-between; align-items:baseline;
        padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:13.5px; gap:8px;
    }
    .detail-row:last-child { border-bottom:none; }
    .detail-key { color:#6b7280; }
    .detail-val { font-weight:600; color:#111827; text-align:right; }

    .stat-mini { background:#f9fafb; border-radius:10px; padding:14px 16px; text-align:center; border:1px solid #f3f4f6; }
    .stat-mini-num   { font-size:24px; font-weight:700; color:#111827; line-height:1; }
    .stat-mini-label { font-size:11px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }

    .you-tag { display:inline-flex; align-items:center; padding:2px 7px; border-radius:6px; background:#eff6ff; color:#1d4ed8; font-size:11px; font-weight:700; margin-left:6px; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div style="max-width:560px; display:flex; flex-direction:column; gap:20px;">

    {{-- Profile card --}}
    <div class="card" style="border-radius:12px;">
        <div class="card-body" style="padding:24px;">

            {{-- Avatar + name --}}
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:22px;">
                <div class="user-avatar-lg avatar-{{ $user->role }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:19px; font-weight:700; color:#111827; margin-bottom:8px;">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                            <span class="you-tag">You</span>
                        @endif
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <span class="role-badge role-{{ $user->role }}">
                            <i class="fas fa-{{ $user->role === 'admin' ? 'shield-alt' : 'user' }}" style="font-size:10px;"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                        <span class="status-badge badge-{{ $user->status }}">
                            <span class="dot"></span>
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:22px;">
                <div class="stat-mini">
                    <div class="stat-mini-num">{{ $user->bookingsCreated->count() }}</div>
                    <div class="stat-mini-label">Bookings</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-num">{{ $user->payments->count() }}</div>
                    <div class="stat-mini-label">Payments</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-num">{{ $user->invoices->count() }}</div>
                    <div class="stat-mini-label">Invoices</div>
                </div>
            </div>

            {{-- Details --}}
            <div class="detail-row">
                <span class="detail-key"><i class="fas fa-envelope" style="width:14px; margin-right:4px; color:#9ca3af;"></i> Email</span>
                <span class="detail-val" style="color:#1a56db;">{{ $user->email }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key"><i class="fas fa-calendar-alt" style="width:14px; margin-right:4px; color:#9ca3af;"></i> Joined</span>
                <span class="detail-val">{{ $user->created_at->format('d M Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key"><i class="fas fa-clock" style="width:14px; margin-right:4px; color:#9ca3af;"></i> Last updated</span>
                <span class="detail-val">{{ $user->updated_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key"><i class="fas fa-check-circle" style="width:14px; margin-right:4px; color:#9ca3af;"></i> Email verified</span>
                <span class="detail-val">
                    @if($user->email_verified_at)
                        <span style="color:#15803d;">{{ $user->email_verified_at->format('d M Y') }}</span>
                    @else
                        <span style="color:#9ca3af;">Not verified</span>
                    @endif
                </span>
            </div>

        </div>
    </div>

    {{-- Danger zone — cannot delete yourself --}}
    @if($user->id !== auth()->id())
    <div class="card" style="border-radius:12px; border:1px solid #fecaca;">
        <div class="card-header" style="background:transparent; border-bottom:1px solid #fecaca; padding:14px 20px;">
            <h6 style="margin:0; font-size:13px; font-weight:700; color:#dc2626; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-exclamation-triangle"></i> Danger Zone
            </h6>
        </div>
        <div class="card-body">
            <p style="font-size:13px; color:#6b7280; margin-bottom:12px;">
                Permanently remove this staff member from the system.
            </p>
            <form action="{{ route('users.destroy', $user) }}" method="POST"
                  onsubmit="return confirm('Remove {{ addslashes($user->name) }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        style="padding:7px 16px; border-radius:8px; border:1.5px solid #fca5a5; background:#fff; color:#dc2626; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    <i class="fas fa-trash-alt"></i> Remove User
                </button>
            </form>
        </div>
    </div>
    @endif

</div>

@endsection