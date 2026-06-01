@extends('layouts.guest')
@section('title', 'My Bookings')
@push('styles')
<style>
    .history-wrap { max-width:900px;margin:0 auto;padding:40px 24px 60px; }
    .history-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px; }
    .history-title { font-size:22px;font-weight:800;color:#111827; }
    .history-sub   { font-size:13.5px;color:#6b7280;margin-top:3px; }
    .back-btn { padding:8px 16px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px; }
    .bk-card { background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px 22px;margin-bottom:16px;transition:box-shadow .2s; }
    .bk-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
    .bk-top { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px; }
    .bk-num { font-size:14px;font-weight:700;color:#1a56db;font-family:monospace; }
    .bk-date { font-size:12px;color:#9ca3af;margin-top:2px; }
    .bk-pill { padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600; }
    .pill-pending    { background:#fffbeb;color:#d97706; }
    .pill-confirmed  { background:#eff6ff;color:#1d4ed8; }
    .pill-checked_in { background:#f0fdf4;color:#15803d; }
    .pill-checked_out{ background:#f1f5f9;color:#475569; }
    .pill-cancelled  { background:#fef2f2;color:#dc2626; }
    .bk-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;padding-top:14px;border-top:1px solid #f3f4f6; }
    .bk-field { font-size:12px;color:#9ca3af;margin-bottom:3px; }
    .bk-val   { font-size:13.5px;font-weight:600;color:#111827; }
    .bk-pay   { display:flex;align-items:center;gap:6px;margin-top:12px;padding-top:12px;border-top:1px solid #f3f4f6;font-size:13px; }
    .paid-badge   { padding:2px 9px;border-radius:20px;background:#f0fdf4;color:#15803d;font-size:11.5px;font-weight:700; }
    .unpaid-badge { padding:2px 9px;border-radius:20px;background:#fef2f2;color:#dc2626;font-size:11.5px;font-weight:700; }
    .empty-state { text-align:center;padding:60px 20px;color:#9ca3af; }
    .empty-state i { font-size:48px;margin-bottom:16px;display:block;color:#e5e7eb; }
    @media(max-width:600px){ .history-wrap{padding:24px 16px 40px;} }
</style>
@endpush
@section('content')
<div class="history-wrap">
    <div class="history-header">
        <div>
            <div class="history-title">My Bookings</div>
            <div class="history-sub">Welcome back, {{ $guest->name }}</div>
        </div>
        <a href="{{ route('guest.booking') }}" class="back-btn">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px 16px;font-size:13.5px;color:#15803d;display:flex;align-items:center;gap:8px;margin-bottom:20px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if($bookings->isEmpty())
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p style="font-size:15px;font-weight:600;color:#6b7280;">No bookings yet</p>
            <p style="font-size:13.5px;margin-top:6px;">Your booking history will appear here.</p>
            <a href="{{ route('guest.booking') }}"
               style="display:inline-flex;align-items:center;gap:7px;margin-top:20px;padding:11px 22px;border-radius:10px;background:#1a56db;color:#fff;font-size:14px;font-weight:700;text-decoration:none;">
                <i class="fas fa-search"></i> Browse Rooms
            </a>
        </div>
    @else
        @foreach($bookings as $bk)
        <div class="bk-card">
            <div class="bk-top">
                <div>
                    <div class="bk-num">{{ $bk->booking_number }}</div>
                    <div class="bk-date">Booked {{ $bk->created_at->format('d M Y') }}</div>
                </div>
                <span class="bk-pill pill-{{ $bk->status }}">{{ ucfirst(str_replace('_', ' ', $bk->status)) }}</span>
            </div>

            <div class="bk-grid">
                <div>
                    <div class="bk-field">Room</div>
                    <div class="bk-val">{{ $bk->room->room_number ?? '—' }} · {{ $bk->room->roomType->name ?? '' }}</div>
                </div>
                <div>
                    <div class="bk-field">Check-in</div>
                    <div class="bk-val">{{ \Carbon\Carbon::parse($bk->check_in_date)->format('d M Y') }}</div>
                </div>
                <div>
                    <div class="bk-field">Check-out</div>
                    <div class="bk-val">{{ \Carbon\Carbon::parse($bk->check_out_date)->format('d M Y') }}</div>
                </div>
                <div>
                    <div class="bk-field">Duration</div>
                    <div class="bk-val">{{ $bk->nights }} night{{ $bk->nights > 1 ? 's' : '' }}</div>
                </div>
                <div>
                    <div class="bk-field">Total</div>
                    <div class="bk-val">${{ number_format($bk->room_total, 2) }}</div>
                </div>
                <div>
                    <div class="bk-field">Source</div>
                    <div class="bk-val">{{ ucfirst(str_replace('_', ' ', $bk->booking_source)) }}</div>
                </div>
            </div>

            @php
                $depositPaid = $bk->payments->where('payment_type', 'deposit')->where('status', 'paid')->sum('amount');
                $allPaid     = $bk->payments->where('status', 'paid')->sum('amount');
                $grandTotal  = $bk->invoice ? $bk->invoice->grand_total : $bk->room_total;
                $balance     = max(0, $grandTotal - $allPaid);
            @endphp
            <div class="bk-pay">
                <span style="color:#6b7280;">Deposit:</span>
                <span style="font-weight:600;">${{ number_format($depositPaid, 2) }}</span>
                @if($depositPaid > 0)
                    <span class="paid-badge"><i class="fas fa-check"></i> Paid</span>
                @else
                    <span class="unpaid-badge">Pending</span>
                @endif
                @if($balance > 0)
                    <span style="margin-left:auto;color:#d97706;font-size:13px;font-weight:600;">
                        Balance due: ${{ number_format($balance, 2) }}
                    </span>
                @else
                    <span class="paid-badge" style="margin-left:auto;"><i class="fas fa-check-circle"></i> Fully Settled</span>
                @endif
            </div>
        </div>
        @endforeach

        <div style="margin-top:20px;">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
