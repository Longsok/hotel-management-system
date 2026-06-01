@extends('layouts.app')

@section('title', 'Edit ' . $booking->booking_number)
@section('page-title', 'Edit Booking')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bookings.show', $booking) }}">{{ $booking->booking_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<style>
    .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
    .fcontrol {
        width:100%; padding:10px 13px; border:1.5px solid #e5e7eb; border-radius:9px;
        font-family:inherit; font-size:13.5px; color:#111827; background:#f9fafb; outline:none;
        transition:border-color .2s, box-shadow .2s;
    }
    .fcontrol:focus { border-color:#1a56db; background:#fff; box-shadow:0 0 0 3px rgba(26,86,219,.09); }
    .fcontrol.is-invalid { border-color:#ef4444; background:#fff5f5; }
    .err-msg { font-size:12px; color:#ef4444; margin-top:4px; display:block; }
    textarea.fcontrol { resize:vertical; min-height:90px; }
    .section-title { font-size:13px; font-weight:700; color:#374151; padding-bottom:10px; margin-bottom:16px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:7px; }
    .info-box { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:14px 16px; }
    .info-box-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; margin-bottom:3px; }
    .info-box-val   { font-size:14px; font-weight:700; color:#111827; }
</style>
@endpush

@section('content')

<div style="max-width:720px;">
    <form action="{{ route('bookings.update', $booking) }}" method="POST">
        @csrf @method('PUT')

        {{-- Read-only booking info --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="section-title">
                    <i class="fas fa-info-circle" style="color:#1a56db;"></i> Booking Information
                    <span style="margin-left:auto;font-size:12px;font-weight:500;color:#9ca3af;">Read-only</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                    <div class="info-box">
                        <div class="info-box-label">Booking ID</div>
                        <div class="info-box-val">{{ $booking->booking_number }}</div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Guest</div>
                        <div class="info-box-val">{{ $booking->customer->name }}</div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Room</div>
                        <div class="info-box-val">{{ $booking->room->room_number }} ({{ $booking->room->roomType->name ?? '' }})</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Editable fields --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="section-title">
                    <i class="fas fa-edit" style="color:#1a56db;"></i> Edit Details
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label class="form-label">Check-In Date</label>
                        <input type="date" name="check_in_date"
                               class="fcontrol {{ $errors->has('check_in_date') ? 'is-invalid':'' }}"
                               value="{{ old('check_in_date', $booking->check_in_date->format('Y-m-d')) }}"
                               onchange="calcNights()">
                        @error('check_in_date') <span class="err-msg">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="form-label">Check-Out Date</label>
                        <input type="date" name="check_out_date"
                               class="fcontrol {{ $errors->has('check_out_date') ? 'is-invalid':'' }}"
                               value="{{ old('check_out_date', $booking->check_out_date->format('Y-m-d')) }}"
                               onchange="calcNights()">
                        @error('check_out_date') <span class="err-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div id="nightsDisplay" style="display:none;padding:9px 13px;background:#eff6ff;border-radius:8px;font-size:13px;font-weight:600;color:#1d4ed8;margin-bottom:16px;">
                    <i class="fas fa-moon me-1"></i> <span id="nightsNum"></span> night(s) — Est. total: $<span id="estTotal"></span>
                </div>

                <div>
                    <label class="form-label">Special Requests</label>
                    <textarea name="special_requests" class="fcontrol"
                              placeholder="Any special requests from the guest…">{{ old('special_requests', $booking->special_requests) }}</textarea>
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;">
            <a href="{{ route('bookings.show', $booking) }}"
               style="padding:10px 20px;border-radius:9px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-family:inherit;font-size:14px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:7px;">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit"
                style="padding:10px 24px;border-radius:9px;border:none;background:#1a56db;color:#fff;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:7px;box-shadow:0 2px 10px rgba(26,86,219,.25);transition:background .2s;"
                onmouseover="this.style.background='#1447b5'" onmouseout="this.style.background='#1a56db'">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    var pricePerNight = {{ $booking->room_price }};

    function calcNights() {
        var ci = document.querySelector('[name=check_in_date]').value;
        var co = document.querySelector('[name=check_out_date]').value;
        if (!ci || !co) return;
        var nights = Math.round((new Date(co) - new Date(ci)) / 86400000);
        if (nights > 0) {
            document.getElementById('nightsDisplay').style.display = 'block';
            document.getElementById('nightsNum').textContent = nights;
            document.getElementById('estTotal').textContent  = (nights * pricePerNight).toFixed(2);
        } else {
            document.getElementById('nightsDisplay').style.display = 'none';
        }
    }
    calcNights();
</script>
@endpush
