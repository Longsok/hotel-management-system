@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('header-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New User
    </a>
@endsection

@push('styles')
<style>
    .filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .filter-input-wrap { position:relative; }
    .filter-input-icon { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; pointer-events:none; }
    .filter-input {
        height:36px; padding:0 12px 0 34px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:13px;
        color:#374151; background:#fff; outline:none; transition:border-color .2s; min-width:220px;
    }
    .filter-select {
        height:36px; padding:0 12px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:13px;
        color:#374151; background:#fff; outline:none; transition:border-color .2s; min-width:130px;
    }
    .filter-input:focus, .filter-select:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .btn-filter { height:36px; padding:0 16px; border-radius:8px; border:none; background:#1a56db; color:#fff; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; }
    .btn-filter:hover { background:#1447b5; }
    .btn-reset { height:36px; padding:0 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#6b7280; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; text-decoration:none; }
    .btn-reset:hover { border-color:#d1d5db; color:#374151; }

    .user-avatar {
        width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;
        font-size:13px; font-weight:700; color:#fff; flex-shrink:0;
    }
    .avatar-admin { background:linear-gradient(135deg,#f59e0b,#ef4444); }
    .avatar-staff { background:linear-gradient(135deg,#818cf8,#1a56db); }

    .role-badge {
        display:inline-flex; align-items:center; gap:5px;
        padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;
    }
    .role-admin  { background:#fff7ed; color:#c2410c; }
    .role-staff  { background:#eff6ff; color:#1d4ed8; }

    .status-badge {
        display:inline-flex; align-items:center; gap:5px;
        padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;
    }
    .status-badge .dot { width:6px; height:6px; border-radius:50%; }
    .badge-active   { background:#f0fdf4; color:#15803d; } .badge-active .dot { background:#22c55e; }
    .badge-inactive { background:#fef2f2; color:#dc2626; } .badge-inactive .dot { background:#ef4444; }

    .you-tag {
        display:inline-flex; align-items:center; padding:2px 7px;
        border-radius:6px; background:#eff6ff; color:#1d4ed8;
        font-size:11px; font-weight:700; margin-left:6px;
    }

    .action-btn {
        width:29px; height:29px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center;
        border:1px solid #e5e7eb; color:#6b7280; background:#fff; font-size:12px; text-decoration:none; transition:all .15s;
    }
    .action-btn:hover { background:#eff6ff; border-color:#93c5fd; color:#1d4ed8; }
    .action-btn.red:hover { background:#fef2f2; border-color:#fca5a5; color:#dc2626; }

    .pagination { gap:4px; }
    .page-link { border-radius:7px !important; font-size:13px !important; font-weight:600; border:1.5px solid #e5e7eb !important; color:#374151 !important; padding:5px 11px !important; }
    .page-item.active .page-link { background:#1a56db !important; border-color:#1a56db !important; color:#fff !important; }
    .page-link:hover { background:#eff6ff !important; border-color:#93c5fd !important; color:#1d4ed8 !important; }

    .empty-state { text-align:center; padding:48px 20px; color:#9ca3af; }
    .empty-state i { font-size:40px; margin-bottom:12px; display:block; color:#d1d5db; }
    .empty-state p { font-size:14px; margin:0; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter --}}
<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="filter-bar">
                <div class="filter-input-wrap">
                    <i class="fas fa-search filter-input-icon"></i>
                    <input type="text" name="search" class="filter-input"
                           placeholder="Search name or email…" value="{{ request('search') }}">
                </div>
                <select name="role" class="filter-select">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['search','role','status']))
                    <a href="{{ route('users.index') }}" class="btn-reset">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card" style="border-radius:12px;">
    <div class="card-body p-0">

        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
            <span style="font-size:13px; color:#6b7280;">
                Showing <strong style="color:#111827;">{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</strong>
                of <strong style="color:#111827;">{{ $users->total() }}</strong> users
            </span>
        </div>

        @if($users->isEmpty())
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No users found. <a href="{{ route('users.create') }}" style="color:#1a56db;">Add the first one.</a></p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:13.5px;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="padding:11px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">User</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Role</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Status</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Joined</th>
                        <th style="padding:11px 16px; border-bottom:1px solid #f3f4f6;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td style="padding:12px 20px; vertical-align:middle;">
                            <div style="display:flex; align-items:center; gap:11px;">
                                <div class="user-avatar avatar-{{ $user->role }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600; color:#111827; font-size:13.5px;">
                                        <a href="{{ route('users.show', $user) }}" style="color:inherit; text-decoration:none;">
                                            {{ $user->name }}
                                        </a>
                                        @if($user->id === auth()->id())
                                            <span class="you-tag">You</span>
                                        @endif
                                    </div>
                                    <div style="font-size:12px; color:#9ca3af;">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <span class="role-badge role-{{ $user->role }}">
                                <i class="fas fa-{{ $user->role === 'admin' ? 'shield-alt' : 'user' }}" style="font-size:10px;"></i>
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <span class="status-badge badge-{{ $user->status }}">
                                <span class="dot"></span>
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; color:#6b7280; font-size:12.5px;">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <div style="display:flex; gap:5px;">
                                <a href="{{ route('users.show', $user) }}" class="action-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="action-btn" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Remove {{ addslashes($user->name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn red" title="Delete"
                                            style="border:1px solid #e5e7eb; cursor:pointer;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding:14px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end;">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
        @endif

    </div>
</div>

@endsection