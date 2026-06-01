@extends('layouts.app')

@section('title', 'Extra Services')
@section('page-title', 'Extra Services')

@section('breadcrumb')
    <li class="breadcrumb-item active">Extra Services</li>
@endsection

@section('header-actions')
    @if(auth()->user()->isAdmin())
        <a href="{{ route('extra-services.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> New Service
        </a>
    @endif
@endsection

@push('styles')
<style>
    .filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .filter-input-wrap { position:relative; }
    .filter-input-icon { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; pointer-events:none; }
    .filter-input {
        height:36px; padding:0 12px 0 34px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:13px;
        color:#374151; background:#fff; outline:none; transition:border-color .2s;
        min-width:240px;
    }
    .filter-input:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .btn-filter { height:36px; padding:0 16px; border-radius:8px; border:none; background:#1a56db; color:#fff; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; }
    .btn-filter:hover { background:#1447b5; }
    .btn-reset { height:36px; padding:0 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#6b7280; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; text-decoration:none; }
    .btn-reset:hover { border-color:#d1d5db; color:#374151; }

    .status-badge {
        display:inline-flex; align-items:center; gap:5px;
        padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;
    }
    .status-badge .dot { width:6px; height:6px; border-radius:50%; }
    .badge-active   { background:#f0fdf4; color:#15803d; }
    .badge-active .dot { background:#22c55e; }
    .badge-inactive { background:#fef2f2; color:#dc2626; }
    .badge-inactive .dot { background:#ef4444; }

    .action-btn {
        width:29px; height:29px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center;
        border:1px solid #e5e7eb; color:#6b7280; background:#fff; font-size:12px; text-decoration:none; transition:all .15s;
    }
    .action-btn:hover { background:#eff6ff; border-color:#93c5fd; color:#1d4ed8; }
    .action-btn.red:hover { background:#fef2f2; border-color:#fca5a5; color:#dc2626; }

    .service-icon {
        width:38px; height:38px; border-radius:10px; background:#eff6ff;
        display:flex; align-items:center; justify-content:center;
        color:#1a56db; font-size:16px; flex-shrink:0;
    }

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

{{-- Flash messages --}}
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
        <form method="GET" action="{{ route('extra-services.index') }}">
            <div class="filter-bar">
                <div class="filter-input-wrap">
                    <i class="fas fa-search filter-input-icon"></i>
                    <input type="text" name="search" class="filter-input"
                           placeholder="Search service name…" value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request('search'))
                    <a href="{{ route('extra-services.index') }}" class="btn-reset">
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

        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:13px; color:#6b7280;">
                Showing <strong style="color:#111827;">{{ $services->firstItem() ?? 0 }}–{{ $services->lastItem() ?? 0 }}</strong>
                of <strong style="color:#111827;">{{ $services->total() }}</strong> services
            </span>
        </div>

        @if($services->isEmpty())
            <div class="empty-state">
                <i class="fas fa-concierge-bell"></i>
                <p>No extra services found.
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('extra-services.create') }}" style="color:#1a56db;">Add the first one.</a>
                    @endif
                </p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:13.5px;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="padding:11px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Service</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Description</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Price</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Status</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Created</th>
                        @if(auth()->user()->isAdmin())
                            <th style="padding:11px 16px; border-bottom:1px solid #f3f4f6;"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                    <tr>
                        <td style="padding:12px 20px; vertical-align:middle;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="service-icon">
                                    <i class="fas fa-concierge-bell"></i>
                                </div>
                                <span style="font-size:14px; font-weight:600; color:#111827;">{{ $service->name }}</span>
                            </div>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; color:#6b7280; font-size:13px; max-width:300px;">
                            {{ $service->description ? Str::limit($service->description, 60) : '—' }}
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <span style="font-size:14px; font-weight:700; color:#111827;">
                                ${{ number_format($service->price, 2) }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <span class="status-badge {{ $service->is_active ? 'badge-active' : 'badge-inactive' }}">
                                <span class="dot"></span>
                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; color:#6b7280; font-size:12.5px;">
                            {{ $service->created_at->format('d M Y') }}
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <div style="display:flex; gap:5px;">
                                <a href="{{ route('extra-services.edit', $service) }}" class="action-btn" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('extra-services.destroy', $service) }}" method="POST"
                                      onsubmit="return confirm('Delete \'{{ addslashes($service->name) }}\'? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn red" title="Delete"
                                            style="border:1px solid #e5e7eb; cursor:pointer;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div style="padding:14px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end;">
                {{ $services->withQueryString()->links() }}
            </div>
        @endif
        @endif

    </div>
</div>

@endsection