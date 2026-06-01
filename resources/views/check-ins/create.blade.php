@extends('layouts.app')

@section('title', 'New Check-In')
@section('page-title', 'New Check-In')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('check-ins.index') }}">Check-Ins</a></li>
    <li class="breadcrumb-item active">New Check-In</li>
@endsection

@section('header-actions')
    <a href="{{ route('check-ins.index') }}"
       style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@push('styles')
<style>
    .layout { display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start; }
    @media (max-width:900px) { .layout { grid-template-columns:1fr; } }

    .form-section-title {
        font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
        color:#9ca3af; margin-bottom:16px; padding-bottom:10px;
        border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px;
    }
    .field-group { margin-bottom:18px; }
    .field-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; display:block; }
    .field-label .required { color:#ef4444; margin-left:2px; }
    .field-select, .field-textarea, .field-input {
        width:100%; height:40px; padding:0 12px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:13.5px; color:#111827;
        background:#fff; outline:none; transition:border-color .2s, box-shadow .2s;
    }
    .field-textarea { height:auto; padding:10px 12px; resize:vertical; }
    .field-select:focus, .field-textarea:focus, .field-input:focus {
        border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08);
    }
    .field-select.is-invalid { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.08); }
    .field-error { font-size:12px; color:#ef4444; margin-top:5px; }

    /* Booking card preview */
    .booking-preview {
        border:1.5px solid #e5e7eb; border-radius:10px; overflow:hidden;
        transition:border-color .2s; display:none;
    }
    .booking-preview.show { display:block; }
    .booking-preview-header {
        background:#f9fafb; padding:12px 16px;
        font-size:11px; font-weight:700; text-transform:uppercase;
        letter-spacing:.5px; color:#9ca3af;
        border-bottom:1px solid #f3f4f6;
    }
    .booking-preview-body { padding:14px 16px; }
    .preview-row {
        display:flex; justify-content:space-between; align-items:baseline;
        padding:7px 0; border-bottom:1px solid #f3f4f6; font-size:13px; gap:8px;
    }
    .preview-row:last-child { border-bottom:none; }
    .preview-key { color:#6b7280; }
    .preview-val { font-weight:600; color:#111827; text-align:right; }

    /* Today's bookings list */
    .booking-list { display:flex; flex-direction:column; gap:8px; }
    .booking-item {
        padding:12px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
        cursor:pointer; transition:all .15s; display:flex; align-items:center; justify-content:space-between;
    }
    .booking-item:hover { border-color:#93c5fd; background:#f8faff; }
    .booking-item.selected { border-color:#1a56db; background:#eff6ff; }
    .booking-name  { font-size:13.5px; font-weight:600; color:#111827; }
    .booking-sub   { font-size:12px; color:#9ca3af; margin-top:2px; }
    .booking-badge {
        padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600;
        background:#eff6ff; color:#1d4ed8; flex-shrink:0;
    }

    /* Deposit info box */
    .deposit-box {
        background:#fffbeb; border:1px solid #fde68a; border-radius:10px;
        padding:14px 16px; display:flex; align-items:flex-start; gap:10px;
    }
    .deposit-box i { color:#f59e0b; margin-top:2px; flex-shrink:0; }
    .deposit-title { font-size:13px; font-weight:700; color:#92400e; margin-bottom:2px; }
    .deposit-desc  { font-size:12px; color:#78350f; }

    .btn-submit {
        padding:11px 28px; border-radius:9px; border:none; background:#1a56db; color:#fff;
        font-family:inherit; font-size:14px; font-weight:600; cursor:pointer;
        display:inline-flex; align-items:center; gap:8px; transition:background .15s; width:100%;
        justify-content:center;
    }
    .btn-submit:hover { background:#1447b5; }
    .btn-submit:disabled { background:#93c5fd; cursor:not-allowed; }
</style>
@endpush

@section('content')

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('check-ins.store') }}" method="POST" id="checkin-form">
@csrf

<div class="layout">

    {{-- ── Left: Form ── --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Select booking --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:24px;">

                <div class="form-section-title">
                    <i class="fas fa-calendar-check"></i> Select Booking
                </div>

                <input type="hidden" name="booking_id" id="booking_id"
                       value="{{ old('booking_id', $booking?->id) }}">

                @error('booking_id')
                    <div style="font-size:12px; color:#ef4444; margin-bottom:12px;">{{ $message }}</div>
                @enderror

                @if($confirmedToday->isEmpty() && !$booking)
                    <div style="text-align:center; padding:24px; color:#9ca3af; font-size:13px;">
                        <i class="fas fa-calendar-times" style="font-size:28px; display:block; margin-bottom:10px; color:#d1d5db;"></i>
                        No confirmed bookings for today.
                        <a href="{{ route('bookings.index') }}" style="color:#1a56db; display:block; margin-top:6px;">View all bookings →</a>
                    </div>
                @else
                    <div class="booking-list">
                        @foreach($confirmedToday as $b)
                        <div class="booking-item {{ (old('booking_id', $booking?->id) == $b->id) ? 'selected' : '' }}"
                             id="bitem-{{ $b->id }}"
                             onclick="selectBooking({{ $b->id }}, '{{ addslashes($b->customer->name) }}', '{{ $b->room->room_number }}', '{{ $b->check_in_date->format('d M Y') }}', '{{ $b->check_out_date->format('d M Y') }}', '{{ number_format($b->room_total, 2) }}', '{{ $b->booking_number }}')">
                            <div>
                                <div class="booking-name">{{ $b->customer->name }}</div>
                                <div class="booking-sub">Room {{ $b->room->room_number }} · {{ $b->check_in_date->format('d M Y') }} → {{ $b->check_out_date->format('d M Y') }}</div>
                            </div>
                            <span class="booking-badge">{{ $b->booking_number }}</span>
                        </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

        {{-- Notes --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:24px;">
                <div class="form-section-title">
                    <i class="fas fa-sticky-note"></i> Notes
                </div>
                <div class="field-group" style="margin-bottom:0;">
                    <textarea name="notes" rows="3" class="field-textarea"
                              placeholder="Special requests, room condition notes, key handover…">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Right: Summary ── --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Booking preview --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:20px;">

                <div class="form-section-title" style="margin-bottom:14px;">
                    <i class="fas fa-receipt"></i> Booking Summary
                </div>

                <div class="booking-preview {{ $booking ? 'show' : '' }}" id="booking-preview">
                    <div class="booking-preview-header">Selected Booking</div>
                    <div class="booking-preview-body">
                        <div class="preview-row">
                            <span class="preview-key">Booking #</span>
                            <span class="preview-val" id="prev-bnum">{{ $booking?->booking_number }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Customer</span>
                            <span class="preview-val" id="prev-name">{{ $booking?->customer->name }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Room</span>
                            <span class="preview-val" id="prev-room">{{ $booking ? 'Room ' . $booking->room->room_number : '' }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Check-in</span>
                            <span class="preview-val" id="prev-cin">{{ $booking?->check_in_date->format('d M Y') }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Check-out</span>
                            <span class="preview-val" id="prev-cout">{{ $booking?->check_out_date->format('d M Y') }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Room Total</span>
                            <span class="preview-val" id="prev-total" style="color:#1a56db;">$0.00</span>
                        </div>
                    </div>
                </div>

                @if(!$booking)
                <div id="no-selection" style="text-align:center; padding:20px 0; color:#d1d5db; font-size:13px;">
                    <i class="fas fa-hand-pointer" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                    Select a booking from the left
                </div>
                @endif

            </div>
        </div>

        {{-- BUG FIX: Deposit info box now explains that deposit will be collected
             on the NEXT screen (payment form), allowing choice of cash or Stripe.
             The old code auto-created a cash payment here, which (a) forced cash
             and (b) caused duplicate deposits if online payment was already made. --}}
        <div class="deposit-box" id="deposit-box" style="{{ $booking ? '' : 'display:none;' }}">
            <i class="fas fa-credit-card"></i>
            <div>
                <div class="deposit-title">Deposit Collection — Next Step</div>
                <div class="deposit-desc">
                    After confirming check-in, you'll be taken to the payment screen to collect:
                    <strong id="deposit-amount" style="font-size:15px; display:block; margin-top:4px; color:#92400e;">
                        ${{ $booking ? number_format($booking->room_total * 0.5, 2) : '0.00' }}
                    </strong>
                    (50% of room total · tax applied at checkout)
                </div>
                <div class="deposit-desc" style="margin-top:6px; color:#78350f;">
                    <i class="fas fa-info-circle me-1"></i>
                    You can collect via <strong>Cash</strong> or <strong>Stripe</strong>.
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-submit" id="submit-btn"
                {{ !$booking && $confirmedToday->isEmpty() ? 'disabled' : '' }}>
            <i class="fas fa-sign-in-alt"></i> Confirm Check-In &amp; Collect Deposit
        </button>

    </div>

</div>

</form>

@endsection

@push('scripts')
<script>
    let selectedId = {{ $booking ? $booking->id : 'null' }};

    function selectBooking(id, name, room, cin, cout, total, bnum) {
        if (selectedId) {
            const prev = document.getElementById('bitem-' + selectedId);
            if (prev) prev.classList.remove('selected');
        }

        selectedId = id;
        document.getElementById('booking_id').value = id;
        document.getElementById('bitem-' + id).classList.add('selected');

        document.getElementById('prev-bnum').textContent  = bnum;
        document.getElementById('prev-name').textContent  = name;
        document.getElementById('prev-room').textContent  = 'Room ' + room;
        document.getElementById('prev-cin').textContent   = cin;
        document.getElementById('prev-cout').textContent  = cout;
        document.getElementById('prev-total').textContent = '$' + total;

        const numTotal = parseFloat(total.replace(/,/g, ''));
        const deposit  = (numTotal * 0.5).toFixed(2);
        document.getElementById('deposit-amount').textContent =
            '$' + parseFloat(deposit).toLocaleString('en-US', {minimumFractionDigits:2});

        document.getElementById('booking-preview').classList.add('show');
        document.getElementById('deposit-box').style.display = 'flex';
        document.getElementById('submit-btn').disabled = false;
        const noSel = document.getElementById('no-selection');
        if (noSel) noSel.style.display = 'none';
    }

    @if($booking)
        selectBooking(
            {{ $booking->id }},
            '{{ addslashes($booking->customer->name) }}',
            '{{ $booking->room->room_number }}',
            '{{ $booking->check_in_date->format('d M Y') }}',
            '{{ $booking->check_out_date->format('d M Y') }}',
            '{{ number_format($booking->room_total, 2) }}',
            '{{ $booking->booking_number }}'
        );
    @endif
</script>
@endpush
