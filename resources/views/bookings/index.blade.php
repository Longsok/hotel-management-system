@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Bookings</li>
@endsection

@section('header-actions')
    <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Booking
    </a>
@endsection

@push('styles')
<style>
    .filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .filter-input-wrap { position:relative; }
    .filter-input-icon { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; pointer-events:none; }
    .filter-input, .filter-select, .filter-date {
        height:36px; padding:0 12px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:13px;
        color:#374151; background:#fff; outline:none; transition:border-color .2s;
    }
    .filter-input { padding-left:34px; min-width:220px; }
    .filter-select { min-width:130px; }
    .filter-date   { min-width:140px; }
    .filter-input:focus,.filter-select:focus,.filter-date:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .btn-filter { height:36px; padding:0 16px; border-radius:8px; border:none; background:#1a56db; color:#fff; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; }
    .btn-filter:hover { background:#1447b5; }
    .btn-reset  { height:36px; padding:0 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#6b7280; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; text-decoration:none; }
    .btn-reset:hover { border-color:#d1d5db; color:#374151; }

    /* status chips */
    .status-strip { display:flex; gap:7px; flex-wrap:wrap; }
    .schip {
        padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;
        border:1.5px solid transparent; text-decoration:none;
        display:inline-flex; align-items:center; gap:5px; transition:all .15s;
    }
    .schip .dot { width:7px; height:7px; border-radius:50%; }
    .schip-all        { background:#f3f4f6; color:#374151; border-color:#d1d5db; }
    .schip-pending    { background:#fffbeb; color:#d97706; border-color:#fde68a; }
    .schip-confirmed  { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
    .schip-checked_in { background:#f0fdf4; color:#15803d; border-color:#86efac; }
    .schip-checked_out{ background:#f1f5f9; color:#475569; border-color:#cbd5e1; }
    .schip-cancelled  { background:#fef2f2; color:#dc2626; border-color:#fca5a5; }
    .schip-no_show    { background:#fdf4ff; color:#7e22ce; border-color:#e9d5ff; }
    .schip:hover, .schip.active { transform:translateY(-1px); box-shadow:0 3px 10px rgba(0,0,0,.09); }

    /* table */
    .action-btn {
        width:29px; height:29px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center;
        border:1px solid #e5e7eb; color:#6b7280; background:#fff; font-size:12px; text-decoration:none; transition:all .15s;
    }
    .action-btn:hover { background:#eff6ff; border-color:#93c5fd; color:#1d4ed8; }
    .action-btn.red:hover { background:#fef2f2; border-color:#fca5a5; color:#dc2626; }
    .action-btn.green:hover { background:#f0fdf4; border-color:#86efac; color:#15803d; }

    .guest-cell { display:flex; align-items:center; gap:9px; }
    .guest-avatar {
        width:32px; height:32px; border-radius:50%;
        background:linear-gradient(135deg,#818cf8,#1a56db);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-size:12px; font-weight:700; flex-shrink:0;
    }
    .guest-name  { font-size:13.5px; font-weight:600; color:#111827; }
    .guest-phone { font-size:11.5px; color:#9ca3af; }

    /* pagination override */
    .pagination { gap:4px; }
    .page-link { border-radius:7px !important; font-size:13px !important; font-weight:600; border:1.5px solid #e5e7eb !important; color:#374151 !important; padding:5px 11px !important; }
    .page-item.active .page-link { background:#1a56db !important; border-color:#1a56db !important; color:#fff !important; }
    .page-link:hover { background:#eff6ff !important; border-color:#93c5fd !important; color:#1d4ed8 !important; }
</style>
@endpush

@section('content')

{{-- ── Filter card ── --}}
<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body p-3">

        {{-- Status chips row --}}
        @php
            $chipDefs = [
                ''           => ['all',       '#9ca3af', 'All',        $bookings->total()],
                'pending'    => ['pending',    '#fbbf24', 'Pending',    null],
                'confirmed'  => ['confirmed',  '#3b82f6', 'Confirmed',  null],
                'checked_in' => ['checked_in', '#22c55e', 'Checked In', null],
                'checked_out'=> ['checked_out','#64748b', 'Checked Out',null],
                'cancelled'  => ['cancelled',  '#ef4444', 'Cancelled',  null],
            ];
        @endphp
        <div class="status-strip mb-3">
            @foreach($chipDefs as $val => [$cls, $color, $label, $count])
                <a href="{{ route('bookings.index', array_merge(request()->except('status','page'), $val ? ['status'=>$val] : [])) }}"
                   class="schip schip-{{ $cls }} {{ request('status', '') === $val ? 'active' : '' }}">
                    <span class="dot" style="background:{{ $color }};"></span>
                    {{ $label }}
                    @if($count !== null) <strong>({{ $count }})</strong> @endif
                </a>
            @endforeach
        </div>

        {{-- Filter form --}}
        <form method="GET" action="{{ route('bookings.index') }}">
            <div class="filter-bar">
                <div class="filter-input-wrap">
                    <i class="fas fa-search filter-input-icon"></i>
                    <input type="text" name="search" class="filter-input"
                           placeholder="Search booking ID or guest…" value="{{ request('search') }}">
                </div>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <select name="source" class="filter-select">
                    <option value="">All Sources</option>
                    <option value="walk_in"  {{ request('source')==='walk_in'  ? 'selected':'' }}>Walk-In</option>
                    <option value="online"   {{ request('source')==='online'   ? 'selected':'' }}>Online</option>
                </select>
                <input type="date" name="check_in"  class="filter-date" value="{{ request('check_in') }}"  title="Check-in from">
                <input type="date" name="check_out" class="filter-date" value="{{ request('check_out') }}" title="Check-out to">
                <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('bookings.index') }}" class="btn-reset"><i class="fas fa-times"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- ── Bookings table ── --}}
<div class="card" style="border-radius:12px;">
    <div style="padding:14px 20px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:14px;font-weight:700;color:#111827;">
            <i class="fas fa-list-alt me-2" style="color:#1a56db;"></i>
            All Bookings
        </span>
        <span style="font-size:12.5px;color:#9ca3af;">
            Showing {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }} of {{ $bookings->total() }} records
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th style="text-align:center;">Nights</th>
                        <th>Amount</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr>
                            <td>
                                <a href="{{ route('bookings.show', $b) }}"
                                   style="font-weight:700;font-size:13px;color:#1a56db;text-decoration:none;">
                                    {{ $b->booking_number }}
                                </a>
                            </td>
                            <td>
                                <div class="guest-cell">
                                    <div class="guest-avatar">{{ strtoupper(substr($b->customer->name??'G',0,1)) }}</div>
                                    <div>
                                        <div class="guest-name">{{ $b->customer->name ?? '–' }}</div>
                                        <div class="guest-phone">{{ $b->customer->phone ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:700;font-size:13.5px;color:#111827;">{{ $b->room->room_number ?? '–' }}</div>
                                <div style="font-size:11.5px;color:#9ca3af;">{{ $b->room->roomType->name ?? '' }}</div>
                            </td>
                            <td style="font-size:13px;color:#374151;">{{ $b->check_in_date?->format('M d, Y') ?? '–' }}</td>
                            <td style="font-size:13px;color:#374151;">{{ $b->check_out_date?->format('M d, Y') ?? '–' }}</td>
                            <td style="font-weight:700;text-align:center;">{{ $b->nights }}</td>
                            <td style="font-weight:700;color:#111827;">${{ number_format($b->room_total,0) }}</td>
                            <td>
                                <span style="font-size:12px;font-weight:600;padding:3px 9px;border-radius:6px;
                                    {{ $b->booking_source==='online' ? 'background:#eff6ff;color:#1d4ed8;' : 'background:#f3f4f6;color:#374151;' }}">
                                    {{ $b->booking_source === 'online' ? '🌐 Online' : '🚶 Walk-In' }}
                                </span>
                            </td>
                            <td>
                                <span class="status-pill status-{{ $b->status }}" style="font-size:11.5px;">
                                    {{ ucfirst(str_replace('_',' ',$b->status)) }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:5px;">
                                    <a href="{{ route('bookings.show', $b) }}" class="action-btn" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($b->status,['pending','confirmed']))
                                        <a href="{{ route('bookings.edit', $b) }}" class="action-btn" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    @endif
                                    @if($b->status === 'confirmed')
                                        <a href="{{ route('check-ins.create', ['booking_id'=>$b->id]) }}" class="action-btn green" title="Check In">
                                            <i class="fas fa-sign-in-alt"></i>
                                        </a>
                                    @endif
                                    @if($b->canCancel())
                                        <form action="{{ route('bookings.cancel', $b) }}" method="POST"
                                              onsubmit="return confirm('Cancel booking {{ $b->booking_number }}?')">
                                            @csrf
                                            <button type="submit" class="action-btn red" title="Cancel">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5" style="color:#9ca3af;">
                                <i class="fas fa-calendar-times d-block mb-3" style="font-size:36px;opacity:.2;"></i>
                                <div style="font-size:15px;font-weight:600;color:#374151;">No bookings found</div>
                                <div style="font-size:13px;margin-top:4px;">Try adjusting your filters or create a new booking.</div>
                                <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="fas fa-plus me-1"></i> New Booking
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($bookings->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:12.5px;color:#9ca3af;">
                Page {{ $bookings->currentPage() }} of {{ $bookings->lastPage() }}
            </span>
            {{ $bookings->links() }}
        </div>
    @endif
</div>

@endsection
