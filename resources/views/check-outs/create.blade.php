@extends('layouts.app')

@section('title', 'New Check-Out')
@section('page-title', 'New Check-Out')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('check-outs.index') }}">Check-Outs</a></li>
    <li class="breadcrumb-item active">New Check-Out</li>
@endsection

@section('header-actions')
    <a href="{{ route('check-outs.index') }}"
       style="padding:7px 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@push('styles')
<style>
    .layout { display:grid; grid-template-columns:1fr 380px; gap:20px; align-items:start; }
    @media (max-width:960px) { .layout { grid-template-columns:1fr; } }

    .section-title {
        font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
        color:#9ca3af; margin-bottom:16px; padding-bottom:10px;
        border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px;
    }

    .field-group { margin-bottom:16px; }
    .field-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; display:block; }
    .field-select, .field-input, .field-textarea {
        width:100%; height:40px; padding:0 12px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:13.5px; color:#111827;
        background:#fff; outline:none; transition:border-color .2s, box-shadow .2s;
    }
    .field-textarea { height:auto; padding:10px 12px; resize:vertical; }
    .field-select:focus, .field-input:focus, .field-textarea:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    /* BUG FIX: highlight invalid fields */
    .field-select.is-invalid, .field-input.is-invalid { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.08); }
    .field-error { font-size:12px; color:#ef4444; margin-top:5px; }

    .field-input.qty { width:70px; text-align:center; }

    /* Checked-in list */
    .booking-item {
        padding:12px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
        cursor:pointer; transition:all .15s; display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;
    }
    .booking-item:hover { border-color:#93c5fd; background:#f8faff; }
    .booking-item.selected { border-color:#1a56db; background:#eff6ff; }
    .booking-name { font-size:13.5px; font-weight:600; color:#111827; }
    .booking-sub  { font-size:12px; color:#9ca3af; margin-top:2px; }
    .booking-badge { padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; background:#eff6ff; color:#1d4ed8; flex-shrink:0; }

    /* Guest info responsive grid */
    .guest-info-grid {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:0;
    }
    @media (max-width:600px) {
        .guest-info-grid { grid-template-columns:1fr; }
    }

    /* Services table */
    .services-table { width:100%; border-collapse:collapse; font-size:13px; }
    .services-table th { font-size:11px; color:#9ca3af; font-weight:700; text-transform:uppercase; letter-spacing:.4px; padding:8px 10px; border-bottom:1px solid #f3f4f6; text-align:left; }
    .services-table td { padding:10px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
    .services-table tr:last-child td { border-bottom:none; }
    .btn-remove { width:26px; height:26px; border-radius:6px; border:1px solid #fca5a5; background:#fef2f2; color:#dc2626; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:11px; }
    .btn-remove:hover { background:#fee2e2; }

    /* Service add row */
    .service-add-row { display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; }
    .service-add-row .service-select-wrap { flex:1; min-width:160px; }
    .service-add-row .qty-wrap { width:80px; }
    @media (max-width:480px) {
        .service-add-row .service-select-wrap { min-width:100%; }
        .service-add-row .qty-wrap { width:100%; }
    }

    /* Bill summary */
    .bill-row {
        display:flex; justify-content:space-between; align-items:baseline;
        padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13.5px; gap:8px;
    }
    .bill-row:last-child { border-bottom:none; }
    .bill-key { color:#6b7280; }
    .bill-val { font-weight:600; color:#111827; }
    .bill-total {
        display:flex; justify-content:space-between; align-items:baseline;
        padding:12px 0 0; font-size:16px; font-weight:700; color:#111827; gap:8px;
    }
    .bill-settlement {
        background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;
        padding:14px 16px; display:flex; justify-content:space-between; align-items:center;
        margin-top:12px; flex-wrap:wrap; gap:6px;
    }
    .settlement-label { font-size:13px; font-weight:600; color:#1d4ed8; }
    .settlement-amount { font-size:20px; font-weight:700; color:#1d4ed8; }

    .btn-add-service {
        height:40px; padding:0 14px; border-radius:8px; border:none; background:#1a56db; color:#fff;
        font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; flex-shrink:0;
    }
    .btn-add-service:hover { background:#1447b5; }

    .btn-submit {
        padding:11px 28px; border-radius:9px; border:none; background:#1a56db; color:#fff;
        font-family:inherit; font-size:14px; font-weight:600; cursor:pointer;
        display:inline-flex; align-items:center; gap:8px; transition:background .15s; width:100%; justify-content:center;
    }
    .btn-submit:hover { background:#1447b5; }

    .info-box {
        padding:10px 14px; border-radius:8px; font-size:13px;
        display:flex; align-items:flex-start; gap:8px;
    }
    .info-box-blue { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .info-box-green { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }

    /* Discount UI */
    .disc-type-wrap { display:flex; gap:8px; margin-bottom:10px; }
    .disc-type-btn {
        flex:1; padding:8px 12px; border-radius:8px; border:1.5px solid #e5e7eb;
        background:#f9fafb; font-family:inherit; font-size:13px; font-weight:600;
        color:#6b7280; cursor:pointer; transition:all .15s; text-align:center;
    }
    .disc-type-btn.active { border-color:#1a56db; background:#eff6ff; color:#1a56db; }
    .disc-input-wrap { position:relative; }
    .disc-symbol {
        position:absolute; left:11px; top:50%; transform:translateY(-50%);
        font-size:13px; font-weight:700; color:#6b7280; pointer-events:none;
    }
    .disc-input {
        width:100%; height:40px; padding:0 12px 0 26px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:14px; font-weight:600;
        color:#111827; outline:none; background:#f9fafb;
        transition:border-color .2s, box-shadow .2s;
    }
    .disc-input:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); background:#fff; }
    .disc-saving {
        font-size:12px; font-weight:600; color:#15803d;
        background:#f0fdf4; border:1px solid #86efac; border-radius:6px;
        padding:4px 10px; margin-top:6px; display:none;
    }
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

{{-- BUG FIX: Show validation errors from the add-service form.
     Previously these were silently dropped, making it look like
     the "Add" button did nothing when no service was selected. --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Please fix the following:</strong>
        <ul style="margin:6px 0 0; padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="layout">

    {{-- ── Left ── --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        @if(!$booking)
        {{-- Step 1: Select a checked-in booking --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:24px;">
                <div class="section-title"><i class="fas fa-search"></i> Select Booking to Check Out</div>

                @if($checkedInBookings->isEmpty())
                    <div style="text-align:center; padding:24px; color:#9ca3af; font-size:13px;">
                        <i class="fas fa-bed" style="font-size:28px; display:block; margin-bottom:10px; color:#d1d5db;"></i>
                        No guests currently checked in.
                    </div>
                @else
                    @foreach($checkedInBookings as $b)
                    <a href="{{ route('check-outs.create', ['booking_id' => $b->id]) }}" style="text-decoration:none;">
                        <div class="booking-item">
                            <div>
                                <div class="booking-name">{{ $b->customer->name }}</div>
                                <div class="booking-sub">Room {{ $b->room->room_number }} · Due {{ $b->check_out_date->format('d M Y') }}</div>
                            </div>
                            <span class="booking-badge">{{ $b->booking_number }}</span>
                        </div>
                    </a>
                    @endforeach
                @endif
            </div>
        </div>

        @else
        {{-- Step 2: Booking selected — show services + form --}}

        {{-- Guest info --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:24px;">
                <div class="section-title"><i class="fas fa-user"></i> Guest Information</div>
                {{-- BUG FIX: added responsive class so the 2-column grid
                     collapses to 1 column on small screens --}}
                <div class="guest-info-grid">
                    <div style="padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13.5px;">
                        <div style="color:#6b7280; font-size:12px; margin-bottom:2px;">Customer</div>
                        <div style="font-weight:600; color:#111827;">{{ $booking->customer->name }}</div>
                    </div>
                    <div style="padding:8px 0 8px 16px; border-bottom:1px solid #f3f4f6; font-size:13.5px;">
                        <div style="color:#6b7280; font-size:12px; margin-bottom:2px;">Booking #</div>
                        <div style="font-weight:600; color:#1a56db; font-family:monospace;">{{ $booking->booking_number }}</div>
                    </div>
                    <div style="padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13.5px;">
                        <div style="color:#6b7280; font-size:12px; margin-bottom:2px;">Room</div>
                        <div style="font-weight:600; color:#111827;">Room {{ $booking->room->room_number }} · {{ $booking->room->roomType->name }}</div>
                    </div>
                    <div style="padding:8px 0 8px 16px; border-bottom:1px solid #f3f4f6; font-size:13.5px;">
                        <div style="color:#6b7280; font-size:12px; margin-bottom:2px;">Nights</div>
                        <div style="font-weight:600; color:#111827;">{{ $booking->check_in_date->diffInDays($booking->check_out_date) }} nights</div>
                    </div>
                    <div style="padding:8px 0; font-size:13.5px;">
                        <div style="color:#6b7280; font-size:12px; margin-bottom:2px;">Checked In</div>
                        <div style="font-weight:600; color:#111827;">{{ $booking->checkIn->check_in_time->format('d M Y, h:i A') }}</div>
                    </div>
                    <div style="padding:8px 0 8px 16px; font-size:13.5px;">
                        <div style="color:#6b7280; font-size:12px; margin-bottom:2px;">Due Check-Out</div>
                        <div style="font-weight:600; color:{{ $booking->check_out_date->isPast() ? '#dc2626' : '#111827' }};">
                            {{ $booking->check_out_date->format('d M Y') }}
                            @if($booking->check_out_date->isPast())
                                <span style="font-size:11px; background:#fef2f2; color:#dc2626; padding:1px 6px; border-radius:4px; font-weight:700; margin-left:4px;">Overdue</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add extra services --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:24px;">
                <div class="section-title"><i class="fas fa-concierge-bell"></i> Extra Services</div>

                @if($activeServices->isEmpty())
                    {{-- BUG FIX: show a clear message when no services are configured --}}
                    <div class="info-box info-box-blue mb-3">
                        <i class="fas fa-info-circle" style="flex-shrink:0; margin-top:1px;"></i>
                        <span>No active extra services are configured yet.
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('extra-services.create') }}" style="color:#1d4ed8; font-weight:600;">Create one →</a>
                            @else
                                Ask an admin to add services.
                            @endif
                        </span>
                    </div>
                @else
                {{-- Add service form --}}
                <form action="{{ route('check-outs.add-service') }}" method="POST">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <div class="service-add-row">
                        <div class="service-select-wrap">
                            <label class="field-label" for="service_id">Service</label>
                            <select name="service_id" id="service_id"
                                    class="field-select {{ $errors->has('service_id') ? 'is-invalid' : '' }}">
                                <option value="">Select service…</option>
                                @foreach($activeServices as $svc)
                                    <option value="{{ $svc->id }}" {{ old('service_id') == $svc->id ? 'selected' : '' }}>
                                        ${{ number_format($svc->price, 2) }} — {{ $svc->name }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- BUG FIX: show validation error so the user knows WHY the add failed --}}
                            @error('service_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="qty-wrap">
                            <label class="field-label" for="quantity">Qty</label>
                            <input type="number" name="quantity" id="quantity"
                                   class="field-input {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                                   value="{{ old('quantity', 1) }}" min="1">
                            @error('quantity')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn-add-service" style="margin-bottom:{{ ($errors->has('service_id') || $errors->has('quantity')) ? '22px' : '0' }};">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </form>
                @endif

                {{-- Services list --}}
                @if($booking->bookingServices->isEmpty())
                    <div style="text-align:center; padding:20px; color:#9ca3af; font-size:13px; margin-top:16px;">
                        <i class="fas fa-concierge-bell" style="font-size:24px; display:block; margin-bottom:8px; color:#e5e7eb;"></i>
                        No extra services added yet.
                    </div>
                @else
                    <div style="margin-top:16px; border:1px solid #f3f4f6; border-radius:8px; overflow:hidden;">
                        <table class="services-table">
                            <thead style="background:#f9fafb;">
                                <tr>
                                    <th>Service</th>
                                    <th style="text-align:center;">Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($booking->bookingServices as $bs)
                                <tr>
                                    <td style="font-weight:600; color:#111827;">{{ $bs->service->name }}</td>
                                    <td style="text-align:center; color:#374151;">{{ $bs->quantity }}</td>
                                    <td style="color:#374151;">${{ number_format($bs->unit_price, 2) }}</td>
                                    <td style="font-weight:700; color:#111827;">${{ number_format($bs->total_price, 2) }}</td>
                                    <td>
                                        <form action="{{ route('check-outs.remove-service', $bs) }}" method="POST"
                                              onsubmit="return confirm('Remove this service?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-remove" title="Remove">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>

        {{-- Check-out form (discount + notes) --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:24px;">
                <form action="{{ route('check-outs.store') }}" method="POST" id="checkout-form">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <input type="hidden" name="discount_type" id="discount_type_val" value="{{ old('discount_type', 'percent') }}">

                    {{-- ── Discount section ── --}}
                    <div class="section-title" style="margin-bottom:14px;">
                        <i class="fas fa-tag" style="color:#f59e0b;"></i>
                        Discount
                        @if($isAdmin)
                            <span style="font-size:11px;background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:10px;font-weight:600;text-transform:none;letter-spacing:0;margin-left:6px;">
                                <i class="fas fa-shield-alt"></i> Admin — no limit
                            </span>
                        @elseif($canDiscount)
                            <span style="font-size:11px;background:#fffbeb;color:#d97706;padding:2px 8px;border-radius:10px;font-weight:600;text-transform:none;letter-spacing:0;margin-left:6px;">
                                Staff max: {{ number_format($maxDiscountRate, 0) }}%
                            </span>
                        @else
                            <span style="font-size:11px;background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:10px;font-weight:600;text-transform:none;letter-spacing:0;margin-left:6px;">
                                <i class="fas fa-lock"></i> Admin only
                            </span>
                        @endif
                    </div>

                    @if($canDiscount)
                        <div class="disc-type-wrap">
                            <button type="button" id="btn-pct"
                                    class="disc-type-btn {{ old('discount_type','percent') === 'percent' ? 'active' : '' }}"
                                    onclick="setDiscType('percent')">
                                % Percentage
                            </button>
                            <button type="button" id="btn-fix"
                                    class="disc-type-btn {{ old('discount_type') === 'fixed' ? 'active' : '' }}"
                                    onclick="setDiscType('fixed')">
                                $ Fixed Amount
                            </button>
                        </div>

                        <div class="disc-input-wrap" style="margin-bottom:6px;">
                            <span class="disc-symbol" id="disc-sym">{{ old('discount_type') === 'fixed' ? '$' : '%' }}</span>
                            <input type="number" name="discount_value" id="discount_value"
                                   class="disc-input" min="0" step="0.01"
                                   value="{{ old('discount_value', 0) }}"
                                   oninput="calcDiscount()"
                                   @if(!$isAdmin && $maxDiscountRate > 0)
                                       max="{{ $maxDiscountRate }}"
                                       title="Maximum {{ $maxDiscountRate }}% allowed for staff"
                                   @endif>
                        </div>
                        <div class="disc-saving" id="disc-saving"></div>

                        <div class="field-group" style="margin-top:10px;">
                            <label class="field-label">Discount Reason</label>
                            <input type="text" name="discount_reason"
                                   class="field-input"
                                   value="{{ old('discount_reason') }}"
                                   placeholder="e.g. Loyalty discount, Long stay, Corporate rate…">
                        </div>

                    @else
                        {{-- Staff with no discount permission --}}
                        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:11px 14px;font-size:13px;color:#dc2626;display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                            <i class="fas fa-lock" style="flex-shrink:0;"></i>
                            Discounts are currently disabled for staff. Ask an admin to enable them in Settings.
                        </div>
                        <input type="hidden" name="discount_value" value="0">
                    @endif

                    <div style="border-top:1px solid #f3f4f6;margin:16px 0;"></div>

                    {{-- ── Notes section ── --}}
                    <div class="section-title" style="margin-bottom:10px;">
                        <i class="fas fa-sticky-note"></i> Notes
                    </div>
                    <div class="field-group" style="margin-bottom:0;">
                        <textarea name="notes" rows="3" class="field-textarea"
                                  placeholder="Room condition, guest feedback, items left behind…">{{ old('notes') }}</textarea>
                    </div>
                </form>
            </div>
        </div>

        @endif

    </div>

    {{-- ── Right: Bill Summary ── --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        @if($booking && $billSummary)
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:20px;">

                <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Bill Summary</div>

                <div class="bill-row">
                    <span class="bill-key">Room Total</span>
                    <span class="bill-val">${{ number_format($booking->room_total, 2) }}</span>
                </div>
                <div class="bill-row">
                    <span class="bill-key">Extra Services</span>
                    <span class="bill-val">${{ number_format($billSummary['extra_total'], 2) }}</span>
                </div>
                <div class="bill-row">
                    <span class="bill-key">Subtotal</span>
                    <span class="bill-val" id="bs-subtotal">${{ number_format($billSummary['subtotal'], 2) }}</span>
                </div>
                {{-- Discount rows — hidden until a discount is entered --}}
                <div class="bill-row" id="bs-disc-row" style="display:none;">
                    <span class="bill-key" style="color:#f59e0b;" id="bs-disc-lbl">Discount</span>
                    <span class="bill-val" style="color:#f59e0b;" id="bs-disc-val">−$0.00</span>
                </div>
                <div class="bill-row" id="bs-after-row" style="display:none;">
                    <span class="bill-key">After Discount</span>
                    <span class="bill-val" id="bs-after">$0.00</span>
                </div>
                <div class="bill-row">
                    <span class="bill-key">Tax ({{ number_format($billSummary['tax_rate'] * 100, 0) }}%)</span>
                    <span class="bill-val" id="bs-tax">${{ number_format($billSummary['tax_amount'], 2) }}</span>
                </div>
                <div class="bill-total">
                    <span>Grand Total</span>
                    <span id="bs-grand">${{ number_format($billSummary['grand_total'], 2) }}</span>
                </div>

                <div style="border-top:1px solid #f3f4f6; margin-top:12px; padding-top:12px;">
                    <div class="bill-row">
                        <span class="bill-key" style="color:#9ca3af;">Deposit Paid</span>
                        <span class="bill-val" style="color:#22c55e;">
                            @if($billSummary['deposit'] > 0)
                                − ${{ number_format($billSummary['deposit'], 2) }}
                            @else
                                <span style="color:#f59e0b; font-size:12px;">Not yet collected</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="bill-settlement">
                    <span class="settlement-label"><i class="fas fa-hand-holding-usd me-1"></i> Settlement Due</span>
                    <span class="settlement-amount">${{ number_format($billSummary['settlement'], 2) }}</span>
                </div>

            </div>
        </div>

        <div class="info-box info-box-blue">
            <i class="fas fa-info-circle" style="flex-shrink:0; margin-top:1px;"></i>
            <span>An invoice in <strong>Draft</strong> status will be generated automatically after check-out.</span>
        </div>

        <div class="info-box info-box-green">
            <i class="fas fa-check-circle" style="flex-shrink:0; margin-top:1px;"></i>
            <span>Room will be set to <strong>Cleaning</strong> status after check-out.</span>
        </div>

        {{-- Submit --}}
        <button type="submit" form="checkout-form" class="btn-submit"
                onclick="return confirm('Confirm check-out for {{ addslashes($booking->customer->name) }}? Settlement due: ${{ number_format($billSummary['settlement'], 2) }}')">
            <i class="fas fa-sign-out-alt"></i> Confirm Check-Out
        </button>

        @else

        {{-- No booking selected --}}
        <div class="card" style="border-radius:12px;">
            <div class="card-body" style="padding:24px; text-align:center; color:#9ca3af;">
                <i class="fas fa-file-invoice-dollar" style="font-size:36px; display:block; margin-bottom:12px; color:#e5e7eb;"></i>
                <p style="font-size:13px; margin:0;">Bill summary will appear here once a booking is selected.</p>
            </div>
        </div>

        @endif

    </div>

</div>


@push('scripts')
<script>
var discType   = '{{ old("discount_type", "percent") }}';
var subtotal   = {{ $billSummary ? $billSummary['subtotal'] : 0 }};
var taxRate    = {{ $billSummary ? $billSummary['tax_rate'] : 0 }};
var deposit    = {{ $billSummary ? $billSummary['deposit'] : 0 }};
var maxDisc    = {{ !$isAdmin && $maxDiscountRate > 0 ? $maxDiscountRate : 100 }};
var isAdmin    = {{ $isAdmin ? 'true' : 'false' }};
var canDiscount = {{ $canDiscount ? 'true' : 'false' }};

function setDiscType(type) {
    discType = type;
    document.getElementById('discount_type_val').value = type;
    document.getElementById('disc-sym').textContent    = type === 'fixed' ? '$' : '%';
    document.getElementById('btn-pct').classList.toggle('active', type === 'percent');
    document.getElementById('btn-fix').classList.toggle('active', type === 'fixed');

    // Update input max for percent only
    var inp = document.getElementById('discount_value');
    if (inp && !isAdmin) {
        if (type === 'percent') {
            inp.max  = maxDisc;
            inp.step = '0.1';
        } else {
            inp.removeAttribute('max');
            inp.step = '0.01';
        }
    }
    calcDiscount();
}

function calcDiscount() {
    if (!canDiscount) return;
    var val        = parseFloat(document.getElementById('discount_value').value) || 0;
    var discAmt    = 0;
    var discRate   = 0;
    var saving     = document.getElementById('disc-saving');

    if (val > 0) {
        if (discType === 'percent') {
            discRate = Math.min(val, 100);
            discAmt  = Math.round(subtotal * discRate / 100 * 100) / 100;
        } else {
            discAmt  = Math.min(val, subtotal);
            discRate = subtotal > 0 ? Math.round(discAmt / subtotal * 100 * 10) / 10 : 0;
        }
        saving.style.display = 'block';
        saving.textContent   = 'Saving $' + discAmt.toFixed(2) + ' (' + discRate.toFixed(1) + '% off)';
    } else {
        saving.style.display = 'none';
    }

    var afterDisc  = Math.round((subtotal - discAmt) * 100) / 100;
    var tax        = Math.round(afterDisc * taxRate * 100) / 100;
    var grand      = Math.round((afterDisc + tax) * 100) / 100;
    var settlement = Math.max(0, Math.round((grand - deposit) * 100) / 100);

    // Update bill summary
    var discRow  = document.getElementById('bs-disc-row');
    var afterRow = document.getElementById('bs-after-row');

    if (discAmt > 0) {
        document.getElementById('bs-disc-lbl').textContent = 'Discount (' + discRate.toFixed(1) + '%)';
        document.getElementById('bs-disc-val').textContent = '−$' + discAmt.toFixed(2);
        document.getElementById('bs-after').textContent    = '$' + afterDisc.toFixed(2);
        discRow.style.display  = 'flex';
        afterRow.style.display = 'flex';
    } else {
        discRow.style.display  = 'none';
        afterRow.style.display = 'none';
    }

    document.getElementById('bs-tax').textContent   = '$' + tax.toFixed(2);
    document.getElementById('bs-grand').textContent = '$' + grand.toFixed(2);

    var s = document.querySelector('.settlement-amount');
    if (s) s.textContent = '$' + settlement.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    if (canDiscount) {
        setDiscType(discType);
        calcDiscount();
    }
});
</script>
@endpush
@endsection
