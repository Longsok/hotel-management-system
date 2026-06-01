@extends('layouts.guest')

@section('title', 'Booking Confirmed')

@push('styles')
<style>
    .confirm-wrap { max-width: 640px; margin: 60px auto; padding: 0 24px 60px; text-align: center; }
    .confirm-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px; font-size: 32px; color: #fff;
        box-shadow: 0 8px 24px rgba(34,197,94,.3);
    }
    .confirm-title { font-size: 28px; font-weight: 800; color: #111827; margin-bottom: 8px; }
    .confirm-sub   { font-size: 15px; color: #6b7280; margin-bottom: 32px; }
    .confirm-card  {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
        padding: 24px; text-align: left; margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .cc-title { font-size: 12px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 14px; }
    .cc-row   { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
    .cc-row:last-child { border-bottom: none; }
    .cc-lbl { color: #6b7280; }
    .cc-val { font-weight: 600; color: #111827; }
    .bk-num { font-size: 22px; font-weight: 800; color: #1a56db; font-family: monospace; }
    .confirm-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 28px; border-radius: 10px; background: #1a56db; color: #fff;
        font-size: 14px; font-weight: 700; text-decoration: none;
        box-shadow: 0 3px 12px rgba(26,86,219,.28); transition: background .15s;
    }
    .confirm-btn:hover { background: #1447b5; }
</style>
@endpush

@section('content')
<div class="confirm-wrap">
    <div class="confirm-icon"><i class="fas fa-check"></i></div>
    <div class="confirm-title">Booking Confirmed!</div>
    <div class="confirm-sub">Your deposit has been received. We look forward to welcoming you.</div>

    <div class="confirm-card">
        <div class="cc-title">Booking Details</div>
        <div class="cc-row">
            <span class="cc-lbl">Booking Number</span>
            <span class="bk-num">{{ $booking->booking_number }}</span>
        </div>
        <div class="cc-row">
            <span class="cc-lbl">Guest</span>
            <span class="cc-val">{{ $booking->customer->name ?? ($pending['name'] ?? '—') }}</span>
        </div>
        <div class="cc-row">
            <span class="cc-lbl">Room</span>
            <span class="cc-val">Room {{ $booking->room->room_number }} · {{ $booking->room->roomType?->name }}</span>
        </div>
        <div class="cc-row">
            <span class="cc-lbl">Check-in</span>
            <span class="cc-val">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('D, d M Y') }}</span>
        </div>
        <div class="cc-row">
            <span class="cc-lbl">Check-out</span>
            <span class="cc-val">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('D, d M Y') }}</span>
        </div>
        <div class="cc-row">
            <span class="cc-lbl">Duration</span>
            <span class="cc-val">{{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }}</span>
        </div>
        <div class="cc-row">
            <span class="cc-lbl">Total</span>
            <span class="cc-val">\${{ number_format($booking->room_total, 2) }}</span>
        </div>
        <div class="cc-row">
            <span class="cc-lbl" style="color:#15803d;font-weight:600;">Deposit Paid</span>
            <span class="cc-val" style="color:#15803d;">\${{ number_format($pending['deposit'] ?? 0, 2) }}</span>
        </div>
        <div class="cc-row">
            <span class="cc-lbl">Balance at Check-in</span>
            <span class="cc-val">\${{ number_format($booking->room_total - ($pending['deposit'] ?? 0), 2) }}</span>
        </div>
    </div>

    @if($booking->special_requests)
    <div class="confirm-card">
        <div class="cc-title">Special Requests</div>
        <p style="font-size:14px;color:#374151;">{{ $booking->special_requests }}</p>
    </div>
    @endif

    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;font-size:13px;color:#92400e;margin-bottom:28px;text-align:left;display:flex;gap:10px;">
        <i class="fas fa-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
        <span>Please present your booking number <strong>{{ $booking->booking_number }}</strong> at reception. Remaining balance is due at check-in.</span>
    </div>

    <a href="{{ route('guest.booking') }}" class="confirm-btn">
        <i class="fas fa-home"></i> Back to Home
    </a>
</div>
@endsection
