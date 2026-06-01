@extends('layouts.app')

@section('title', 'Check-In Detail')
@section('page-title', 'Check-In Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('check-ins.index') }}">Check-Ins</a></li>
    <li class="breadcrumb-item active">{{ $checkIn->booking->booking_number }}</li>
@endsection

@section('header-actions')
    <div style="display:flex; gap:8px;">
        @if($checkIn->booking->status === 'checked_in')
        <a href="{{ route('check-outs.create', ['booking_id' => $checkIn->booking_id]) }}"
           style="padding:7px 16px; border-radius:8px; border:none; background:#1a56db; color:#fff; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
            <i class="fas fa-sign-out-alt"></i> Process Check-Out
        </a>
        @endif
        <a href="{{ route('check-ins.index') }}"
           style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@endsection

@push('styles')
<style>
    .show-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }
    @media (max-width:900px) { .show-grid { grid-template-columns:1fr; } }

    .detail-row {
        display:flex; justify-content:space-between; align-items:baseline;
        padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:13.5px; gap:8px;
    }
    .detail-row:last-child { border-bottom:none; }
    .detail-key { color:#6b7280; }
    .detail-val { font-weight:600; color:#111827; text-align:right; }

    .section-title {
        font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
        color:#9ca3af; margin-bottom:16px; padding-bottom:10px;
        border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px;
    }

    .status-badge {
        display:inline-flex; align-items:center; gap:5px;
        padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;
    }
    .status-badge .dot { width:7px; height:7px; border-radius:50%; }
    .badge-checked_in  { background:#f0fdf4; color:#15803d; } .badge-checked_in .dot  { background:#22c55e; }
    .badge-checked_out { background:#f1f5f9; color:#475569; } .badge-checked_out .dot { background:#64748b; }

    .deposit-card {
        background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:16px;
        text-align:center;
    }
    .deposit-amount { font-size:28px; font-weight:700; color:#15803d; margin-bottom:4px; }
    .deposit-label  { font-size:12px; color:#166534; font-weight:600; }

    .timeline-item { display:flex; gap:12px; padding-bottom:16px; position:relative; }
    .timeline-item:not(:last-child)::before {
        content:''; position:absolute; left:14px; top:28px;
        width:2px; bottom:0; background:#f3f4f6;
    }
    .timeline-dot {
        width:28px; height:28px; border-radius:50%; background:#eff6ff;
        display:flex; align-items:center; justify-content:center;
        color:#1a56db; font-size:12px; flex-shrink:0; border:2px solid #bfdbfe;
    }
    .timeline-dot.green { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
    .timeline-content { font-size:13px; padding-top:4px; }
    .timeline-title { font-weight:600; color:#111827; margin-bottom:2px; }
    .timeline-sub   { color:#9ca3af; font-size:12px; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="show-grid">

    {{-- ── Left ── --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Customer & room info --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:24px;">

                <div class="section-title"><i class="fas fa-user"></i> Customer</div>
                <div class="detail-row">
                    <span class="detail-key">Name</span>
                    <span class="detail-val">
                        <a href="{{ route('customers.show', $checkIn->booking->customer) }}" style="color:#1a56db; text-decoration:none;">
                            {{ $checkIn->booking->customer->name }}
                        </a>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Phone</span>
                    <span class="detail-val">{{ $checkIn->booking->customer->phone ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Nationality</span>
                    <span class="detail-val">{{ $checkIn->booking->customer->nationality }}</span>
                </div>

                <div class="section-title" style="margin-top:20px;"><i class="fas fa-door-open"></i> Room</div>
                <div class="detail-row">
                    <span class="detail-key">Room Number</span>
                    <span class="detail-val">Room {{ $checkIn->booking->room->room_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Type</span>
                    <span class="detail-val">{{ $checkIn->booking->room->roomType->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Floor</span>
                    <span class="detail-val">{{ $checkIn->booking->room->floor }}</span>
                </div>

                <div class="section-title" style="margin-top:20px;"><i class="fas fa-calendar-alt"></i> Booking</div>
                <div class="detail-row">
                    <span class="detail-key">Booking #</span>
                    <span class="detail-val">
                        <a href="{{ route('bookings.show', $checkIn->booking) }}" style="color:#1a56db; text-decoration:none; font-family:monospace;">
                            {{ $checkIn->booking->booking_number }}
                        </a>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Check-in Date</span>
                    <span class="detail-val">{{ $checkIn->booking->check_in_date->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Check-out Date</span>
                    <span class="detail-val">{{ $checkIn->booking->check_out_date->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Nights</span>
                    <span class="detail-val">{{ $checkIn->booking->check_in_date->diffInDays($checkIn->booking->check_out_date) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Room Total</span>
                    <span class="detail-val">${{ number_format($checkIn->booking->room_total, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Status</span>
                    <span class="detail-val">
                        <span class="status-badge badge-{{ $checkIn->booking->status }}">
                            <span class="dot"></span>
                            {{ ucfirst(str_replace('_', ' ', $checkIn->booking->status)) }}
                        </span>
                    </span>
                </div>

                @if($checkIn->notes)
                <div class="section-title" style="margin-top:20px;"><i class="fas fa-sticky-note"></i> Notes</div>
                <p style="font-size:13.5px; color:#374151; margin:0;">{{ $checkIn->notes }}</p>
                @endif

            </div>
        </div>

    </div>

    {{-- ── Right ── --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Deposit card --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:20px;">
                <div class="section-title"><i class="fas fa-piggy-bank"></i> Deposit Collected</div>
                <div class="deposit-card">
                    <div class="deposit-amount">${{ number_format($checkIn->deposit_amount, 2) }}</div>
                    <div class="deposit-label">50% of room total · Cash</div>
                    <div style="font-size:11px; color:#9ca3af; margin-top:4px;">Tax applied at checkout</div>
                </div>
            </div>
        </div>

        {{-- Check-in info --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:20px;">
                <div class="section-title"><i class="fas fa-info-circle"></i> Check-In Info</div>
                <div class="detail-row">
                    <span class="detail-key">Check-in Time</span>
                    <span class="detail-val">{{ $checkIn->check_in_time->format('d M Y, h:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Processed By</span>
                    <span class="detail-val">{{ $checkIn->checkedInBy->name ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Recorded At</span>
                    <span class="detail-val">{{ $checkIn->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:20px;">
                <div class="section-title"><i class="fas fa-history"></i> Timeline</div>
                <div>
                    <div class="timeline-item">
                        <div class="timeline-dot"><i class="fas fa-calendar-plus"></i></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Booking Created</div>
                            <div class="timeline-sub">{{ $checkIn->booking->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"><i class="fas fa-check"></i></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Booking Confirmed</div>
                            <div class="timeline-sub">{{ $checkIn->booking->updated_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot green"><i class="fas fa-sign-in-alt"></i></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Checked In</div>
                            <div class="timeline-sub">{{ $checkIn->check_in_time->format('d M Y, h:i A') }} · by {{ $checkIn->checkedInBy->name ?? '—' }}</div>
                        </div>
                    </div>
                    @if($checkIn->booking->checkOut)
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Checked Out</div>
                            <div class="timeline-sub">{{ $checkIn->booking->checkOut->check_out_time->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    @else
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background:#f9fafb; color:#d1d5db; border-color:#e5e7eb; border-style:dashed;">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title" style="color:#9ca3af;">Checkout Pending</div>
                            <div class="timeline-sub">Due {{ $checkIn->booking->check_out_date->format('d M Y') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Process checkout CTA --}}
        @if($checkIn->booking->status === 'checked_in')
        <a href="{{ route('check-outs.create', ['booking_id' => $checkIn->booking_id]) }}"
           style="padding:11px 20px; border-radius:9px; border:none; background:#1a56db; color:#fff; font-size:14px; font-weight:600; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px;">
            <i class="fas fa-sign-out-alt"></i> Process Check-Out
        </a>
        @endif

    </div>

</div>

@endsection