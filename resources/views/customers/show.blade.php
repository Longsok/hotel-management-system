@extends('layouts.app')

@section('title', $customer->name)
@section('page-title', $customer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@section('header-actions')
    <div style="display:flex; gap:8px; align-items:center;">
        <a href="{{ route('bookings.create', ['customer_id' => $customer->id]) }}"
           style="padding:7px 16px; border-radius:8px; border:none; background:#1a56db; color:#fff; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; text-decoration:none;">
            <i class="fas fa-plus"></i> New Booking
        </a>
        <a href="{{ route('customers.edit', $customer) }}"
           style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
            <i class="fas fa-pencil-alt"></i> Edit
        </a>
        <a href="{{ route('customers.index') }}"
           style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@endsection

@push('styles')
<style>
    .show-grid { display:grid; grid-template-columns:340px 1fr; gap:20px; align-items:start; }
    @media (max-width:900px) { .show-grid { grid-template-columns:1fr; } }

    .info-label { font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:#9ca3af; margin-bottom:3px; }
    .info-val   { font-size:14px; font-weight:600; color:#111827; }

    .profile-avatar {
        width:72px; height:72px; border-radius:50%;
        background:linear-gradient(135deg,#818cf8,#1a56db);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-size:26px; font-weight:700; flex-shrink:0;
    }

    .status-badge {
        display:inline-flex; align-items:center; gap:5px;
        padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;
    }
    .status-badge .dot { width:7px; height:7px; border-radius:50%; }
    .badge-active   { background:#f0fdf4; color:#15803d; }
    .badge-active .dot { background:#22c55e; }
    .badge-inactive { background:#fef2f2; color:#dc2626; }
    .badge-inactive .dot { background:#ef4444; }

    .detail-row {
        display:flex; justify-content:space-between; align-items:baseline;
        padding:10px 0; border-bottom:1px solid #f3f4f6; gap:8px;
        font-size:13.5px;
    }
    .detail-row:last-child { border-bottom:none; }
    .detail-key { color:#6b7280; }
    .detail-val { font-weight:600; color:#111827; text-align:right; word-break:break-word; }

    .stat-mini {
        background:#f9fafb; border-radius:10px; padding:14px 16px;
        text-align:center; border:1px solid #f3f4f6;
    }
    .stat-mini-num  { font-size:24px; font-weight:700; color:#111827; line-height:1; }
    .stat-mini-label{ font-size:11px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }

    /* Booking status chips */
    .bk-status {
        display:inline-flex; align-items:center; gap:5px;
        padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:600;
    }
    .bk-status .dot { width:6px; height:6px; border-radius:50%; }
    .bk-pending    { background:#fffbeb; color:#d97706; } .bk-pending .dot    { background:#f59e0b; }
    .bk-confirmed  { background:#eff6ff; color:#1d4ed8; } .bk-confirmed .dot  { background:#3b82f6; }
    .bk-checked_in { background:#f0fdf4; color:#15803d; } .bk-checked_in .dot { background:#22c55e; }
    .bk-checked_out{ background:#f1f5f9; color:#475569; } .bk-checked_out .dot{ background:#64748b; }
    .bk-cancelled  { background:#fef2f2; color:#dc2626; } .bk-cancelled .dot  { background:#ef4444; }
    .bk-no_show    { background:#fdf4ff; color:#7e22ce; } .bk-no_show .dot    { background:#a855f7; }

    .action-btn {
        width:28px; height:28px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center;
        border:1px solid #e5e7eb; color:#6b7280; background:#fff; font-size:11px; text-decoration:none; transition:all .15s;
    }
    .action-btn:hover { background:#eff6ff; border-color:#93c5fd; color:#1d4ed8; }

    .empty-state { text-align:center; padding:36px 20px; color:#9ca3af; }
    .empty-state i { font-size:32px; margin-bottom:10px; display:block; color:#d1d5db; }
    .empty-state p { font-size:13px; margin:0; }
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

<div class="show-grid">

    {{-- ── Left column: profile + identity ── --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Profile card --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body">

                {{-- Avatar + name --}}
                <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}{{ strtoupper(substr(strrchr($customer->name, ' ') ?: ' ', 1, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:18px; font-weight:700; color:#111827; margin-bottom:6px;">
                            {{ $customer->name }}
                        </div>
                        <span class="status-badge badge-{{ $customer->status }}">
                            <span class="dot"></span>
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                </div>

                {{-- Stats --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px;">
                    <div class="stat-mini">
                        <div class="stat-mini-num">{{ $customer->bookings->count() }}</div>
                        <div class="stat-mini-label">Total Bookings</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-num">
                            {{ $customer->bookings->whereIn('status', ['pending','confirmed','checked_in'])->count() }}
                        </div>
                        <div class="stat-mini-label">Active</div>
                    </div>
                </div>

                {{-- Contact details --}}
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-envelope" style="width:14px; margin-right:4px; color:#9ca3af;"></i>Email</span>
                    <span class="detail-val" style="color:#1a56db;">{{ $customer->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-phone" style="width:14px; margin-right:4px; color:#9ca3af;"></i>Phone</span>
                    <span class="detail-val">{{ $customer->phone ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-globe" style="width:14px; margin-right:4px; color:#9ca3af;"></i>Nationality</span>
                    <span class="detail-val">{{ $customer->nationality }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-map-marker-alt" style="width:14px; margin-right:4px; color:#9ca3af;"></i>Address</span>
                    <span class="detail-val">{{ $customer->address ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-calendar-alt" style="width:14px; margin-right:4px; color:#9ca3af;"></i>Member since</span>
                    <span class="detail-val">{{ $customer->created_at->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-clock" style="width:14px; margin-right:4px; color:#9ca3af;"></i>Last updated</span>
                    <span class="detail-val">{{ $customer->updated_at->format('d M Y') }}</span>
                </div>

            </div>
        </div>

        {{-- Identity documents card --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-header" style="background:transparent; border-bottom:1px solid #f3f4f6; padding:14px 20px;">
                <h6 style="margin:0; font-size:13px; font-weight:700; color:#374151; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-id-card" style="color:#9ca3af;"></i> Identity Documents
                </h6>
            </div>
            <div class="card-body">
                <div class="detail-row">
                    <span class="detail-key">ID Card</span>
                    <span class="detail-val">{{ $customer->id_card ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Passport</span>
                    <span class="detail-val">{{ $customer->passport ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Danger zone (admin only) --}}
        @can('admin')
        <div class="card" style="border-radius:12px; border:1px solid #fecaca;">
            <div class="card-header" style="background:transparent; border-bottom:1px solid #fecaca; padding:14px 20px;">
                <h6 style="margin:0; font-size:13px; font-weight:700; color:#dc2626; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                </h6>
            </div>
            <div class="card-body">
                <p style="font-size:13px; color:#6b7280; margin-bottom:12px;">
                    Permanently delete this customer. Customers with active bookings cannot be deleted.
                </p>
                <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                      onsubmit="return confirm('Delete {{ addslashes($customer->name) }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="padding:7px 16px; border-radius:8px; border:1.5px solid #fca5a5; background:#fff; color:#dc2626; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <i class="fas fa-trash-alt"></i> Delete Customer
                    </button>
                </form>
            </div>
        </div>
        @endcan

    </div>

    {{-- ── Right column: booking history ── --}}
    <div>
        <div class="card" style="border-radius:12px;">
            <div class="card-header" style="background:transparent; border-bottom:1px solid #f3f4f6; padding:14px 20px; display:flex; align-items:center; justify-content:space-between;">
                <h6 style="margin:0; font-size:13px; font-weight:700; color:#374151; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-calendar-check" style="color:#9ca3af;"></i> Booking History
                </h6>
                <a href="{{ route('bookings.index', ['customer_id' => $customer->id]) }}"
                   style="font-size:12px; color:#1a56db; text-decoration:none; font-weight:600;">
                    View all <i class="fas fa-arrow-right" style="font-size:11px;"></i>
                </a>
            </div>

            @if($customer->bookings->isEmpty())
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No bookings yet for this customer.</p>
                        <a href="{{ route('bookings.create', ['customer_id' => $customer->id]) }}"
                           style="display:inline-block; margin-top:10px; padding:7px 14px; border-radius:8px; background:#1a56db; color:#fff; font-size:13px; font-weight:600; text-decoration:none;">
                            <i class="fas fa-plus me-1"></i> Create Booking
                        </a>
                    </div>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:13px;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th style="padding:10px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Booking #</th>
                            <th style="padding:10px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Room</th>
                            <th style="padding:10px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Check-in</th>
                            <th style="padding:10px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Check-out</th>
                            <th style="padding:10px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Total</th>
                            <th style="padding:10px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Status</th>
                            <th style="padding:10px 16px; border-bottom:1px solid #f3f4f6;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->bookings as $booking)
                        <tr>
                            <td style="padding:11px 16px; vertical-align:middle; font-family:monospace; font-size:12px; font-weight:700; color:#1a56db;">
                                <a href="{{ route('bookings.show', $booking) }}" style="color:inherit; text-decoration:none;">
                                    {{ $booking->booking_number }}
                                </a>
                            </td>
                            <td style="padding:11px 16px; vertical-align:middle; color:#374151;">
                                @if($booking->room)
                                    Room {{ $booking->room->room_number }}
                                    <span style="font-size:11px; color:#9ca3af; display:block;">
                                        {{ $booking->room->roomType->name ?? '' }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding:11px 16px; vertical-align:middle; color:#374151;">
                                {{ $booking->check_in_date->format('d M Y') }}
                            </td>
                            <td style="padding:11px 16px; vertical-align:middle; color:#374151;">
                                {{ $booking->check_out_date->format('d M Y') }}
                            </td>
                            <td style="padding:11px 16px; vertical-align:middle; font-weight:600; color:#111827;">
                                ${{ number_format($booking->room_total, 2) }}
                            </td>
                            <td style="padding:11px 16px; vertical-align:middle;">
                                <span class="bk-status bk-{{ $booking->status }}">
                                    <span class="dot"></span>
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td style="padding:11px 16px; vertical-align:middle;">
                                <a href="{{ route('bookings.show', $booking) }}" class="action-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    </div>

</div>

@endsection