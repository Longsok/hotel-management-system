@extends('layouts.app')

@section('title', $booking->booking_number)
@section('page-title', 'Booking ' . $booking->booking_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
    <li class="breadcrumb-item active">{{ $booking->booking_number }}</li>
@endsection

@section('header-actions')
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @if($booking->status === 'pending')
            <form action="{{ route('bookings.confirm', $booking) }}" method="POST">
                @csrf
                <button type="submit"
                    style="padding:7px 16px;border-radius:8px;border:none;background:#1a56db;color:#fff;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-check"></i> Confirm
                </button>
            </form>
        @endif
        @if($booking->status === 'confirmed')
            <a href="{{ route('check-ins.create', ['booking_id'=>$booking->id]) }}"
               style="padding:7px 16px;border-radius:8px;border:none;background:#10b981;color:#fff;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="fas fa-sign-in-alt"></i> Check In
            </a>
        @endif
        @if($booking->status === 'checked_in')
            <a href="{{ route('check-outs.create', ['booking_id'=>$booking->id]) }}"
               style="padding:7px 16px;border-radius:8px;border:none;background:#f59e0b;color:#fff;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="fas fa-sign-out-alt"></i> Check Out
            </a>
        @endif
        @if($booking->canCancel())
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                  onsubmit="return confirm('Cancel booking {{ $booking->booking_number }}?')">
                @csrf
                <button type="submit"
                    style="padding:7px 14px;border-radius:8px;border:1.5px solid #fca5a5;background:#fff;color:#dc2626;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </form>
        @endif
        <a href="{{ route('bookings.index') }}"
           style="padding:7px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@endsection

@push('styles')
<style>
    .show-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }

    .info-label { font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:#9ca3af; margin-bottom:4px; }
    .info-val   { font-size:14px; font-weight:600; color:#111827; }
    .info-grid  { display:grid; grid-template-columns:1fr 1fr; gap:16px; }

    .booking-status {
        display:inline-flex; align-items:center; gap:7px;
        padding:7px 16px; border-radius:10px; font-size:14px; font-weight:700;
    }
    .booking-status .bsdot { width:9px; height:9px; border-radius:50%; background:currentColor; }

    /* Progress timeline */
    .progress-track {
        display:flex; align-items:center; gap:0;
        margin:20px 0; overflow-x:auto;
    }
    .prog-step {
        display:flex; flex-direction:column; align-items:center; flex:1;
        position:relative; z-index:1; min-width:60px;
    }
    .prog-step::before {
        content:''; position:absolute; top:16px; left:-50%; right:50%;
        height:2px; background:#e5e7eb; z-index:0;
    }
    .prog-step:first-child::before { display:none; }
    .prog-step.done::before   { background:#1a56db; }
    .prog-step.active::before { background:#1a56db; }
    .prog-icon {
        width:32px; height:32px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        font-size:13px; font-weight:700; position:relative; z-index:2;
        border:2px solid #e5e7eb; background:#fff; color:#9ca3af;
    }
    .prog-icon.done   { background:#1a56db; border-color:#1a56db; color:#fff; }
    .prog-icon.active { background:#fff; border-color:#1a56db; color:#1a56db; box-shadow:0 0 0 4px rgba(26,86,219,.15); }
    .prog-label { font-size:11px; font-weight:600; color:#9ca3af; margin-top:6px; text-align:center; }
    .prog-label.done, .prog-label.active { color:#111827; }

    .service-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f3f4f6; }
    .service-row:last-child { border-bottom:none; }

    .payment-item { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f3f4f6; flex-wrap:wrap; gap:8px; }
    .payment-item:last-child { border-bottom:none; }

    .totals-row { display:flex; justify-content:space-between; padding:10px 18px; border-bottom:1px solid #f3f4f6; font-size:13.5px; gap:8px; }
    .totals-row:last-child { border-bottom:none; }
    .totals-row .tl { color:#6b7280; }
    .totals-row .tv { font-weight:700; color:#111827; }
    .totals-grand { display:flex; justify-content:space-between; align-items:center; padding:14px 18px; background:#f9fafb; border-top:2px solid #e5e7eb; flex-wrap:wrap; gap:8px; }

    .action-link {
        display:flex; align-items:center; gap:9px; padding:10px 14px;
        border:1.5px solid #e5e7eb; border-radius:9px; text-decoration:none;
        color:#374151; font-size:13.5px; font-weight:600;
        transition:all .15s; margin-bottom:8px;
    }
    .action-link:hover { border-color:#93c5fd; background:#eff6ff; color:#1d4ed8; }
    .action-link i { width:16px; color:#6b7280; }

    @media (max-width:960px) {
        .show-grid { grid-template-columns:1fr; }
        .info-grid { grid-template-columns:1fr 1fr; }
    }
    @media (max-width:480px) {
        .info-grid { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')

@php
    $invoice = $booking->invoice;

    if ($invoice) {
        // Checked-out: read all values from the invoice (discount-aware, locked at checkout)
        $taxRate         = (float) $invoice->tax_rate;
        $extraTotal      = (float) $invoice->extra_total;
        $subtotal        = (float) $invoice->subtotal;
        $discountRate    = (float) $invoice->discount_rate;
        $discountAmount  = (float) $invoice->discount_amount;
        $discountReason  = $invoice->discount_reason ?? '';
        $discountedTotal = (float) $invoice->discounted_total;
        $tax             = (float) $invoice->tax_amount;
        $grand           = (float) $invoice->grand_total;
        $hasDiscount     = $discountAmount > 0;
    } else {
        // Not checked out yet: calculate live
        $taxRate         = \App\Models\Setting::taxRate();
        $extraTotal      = $booking->bookingServices->sum('total_price');
        $subtotal        = (float) $booking->room_total + (float) $extraTotal;
        $discountRate    = 0;
        $discountAmount  = 0;
        $discountReason  = '';
        $discountedTotal = $subtotal;
        $tax             = round($subtotal * $taxRate / 100, 2);
        $grand           = $subtotal + $tax;
        $hasDiscount     = false;
    }

    $deposit = (float) $booking->payments
                   ->where('status', 'paid')
                   ->where('payment_type', 'deposit')
                   ->sum('amount');

    $settlementDue    = $invoice ? (float) $invoice->settlement_amount : max(0, $grand - $deposit);
    $settlementPaid   = (float) $booking->payments
                            ->where('status', 'paid')
                            ->where('payment_type', 'settlement')
                            ->sum('amount');
    $balanceRemaining = max(0, round($settlementDue - $settlementPaid, 2));
@endphp

<div class="show-grid">

    {{-- ── LEFT ── --}}
    <div>

        {{-- Overview card --}}
        <div class="card mb-4">
            @php
                $stripColor = match($booking->status) {
                    'confirmed'  => '#1a56db',
                    'checked_in' => '#10b981',
                    'checked_out'=> '#64748b',
                    'cancelled'  => '#ef4444',
                    'no_show'    => '#8b5cf6',
                    default      => '#f59e0b',
                };
            @endphp
            <div style="height:4px;background:{{ $stripColor }};"></div>
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <div style="font-size:22px;font-weight:800;color:#111827;letter-spacing:-.4px;margin-bottom:4px;">
                            {{ $booking->booking_number }}
                        </div>
                        <div style="font-size:12.5px;color:#9ca3af;">
                            Created {{ $booking->created_at->format('M d, Y H:i') }}
                            @if($booking->createdBy) by <strong style="color:#374151;">{{ $booking->createdBy->name }}</strong> @endif
                        </div>
                    </div>
                    <span class="booking-status
                        @if($booking->status==='confirmed')  " style="background:#eff6ff;color:#1d4ed8;"
                        @elseif($booking->status==='checked_in') " style="background:#f0fdf4;color:#15803d;"
                        @elseif($booking->status==='checked_out') " style="background:#f1f5f9;color:#475569;"
                        @elseif($booking->status==='cancelled') " style="background:#fef2f2;color:#dc2626;"
                        @elseif($booking->status==='no_show')   " style="background:#fdf4ff;color:#7e22ce;"
                        @else " style="background:#fffbeb;color:#d97706;"
                        @endif>
                        <span class="bsdot"></span>
                        {{ ucfirst(str_replace('_',' ',$booking->status)) }}
                    </span>
                </div>

                @php
                    $steps = [
                        ['label'=>'Pending',    'icon'=>'fa-hourglass-start', 'statuses'=>['pending','confirmed','checked_in','checked_out']],
                        ['label'=>'Confirmed',  'icon'=>'fa-check',           'statuses'=>['confirmed','checked_in','checked_out']],
                        ['label'=>'Checked In', 'icon'=>'fa-sign-in-alt',     'statuses'=>['checked_in','checked_out']],
                        ['label'=>'Checked Out','icon'=>'fa-sign-out-alt',    'statuses'=>['checked_out']],
                    ];
                    $activeStep = match($booking->status) {
                        'pending'     => 0,
                        'confirmed'   => 1,
                        'checked_in'  => 2,
                        'checked_out' => 3,
                        default       => -1,
                    };
                @endphp
                @if(!in_array($booking->status,['cancelled','no_show']))
                    <div class="progress-track">
                        @foreach($steps as $i => $step)
                            @php
                                $isDone   = $i < $activeStep;
                                $isActive = $i === $activeStep;
                            @endphp
                            <div class="prog-step {{ $isDone ? 'done' : ($isActive ? 'active' : '') }}">
                                <div class="prog-icon {{ $isDone ? 'done' : ($isActive ? 'active' : '') }}">
                                    <i class="fas {{ $step['icon'] }}"></i>
                                </div>
                                <div class="prog-label {{ $isDone ? 'done' : ($isActive ? 'active' : '') }}">
                                    {{ $step['label'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="info-grid">
                    <div>
                        <div class="info-label">Guest</div>
                        <div class="info-val">
                            <a href="{{ route('customers.show', $booking->customer) }}"
                               style="color:#1a56db;text-decoration:none;">{{ $booking->customer->name }}</a>
                        </div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                            {{ $booking->customer->phone ?? '' }}
                            {{ $booking->customer->email ? '· '.$booking->customer->email : '' }}
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Room</div>
                        <div class="info-val">
                            <a href="{{ route('rooms.show', $booking->room) }}"
                               style="color:#1a56db;text-decoration:none;">Room {{ $booking->room->room_number }}</a>
                        </div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                            {{ $booking->room->roomType->name ?? '' }}
                            · Floor {{ $booking->room->floor }}
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Check-In</div>
                        <div class="info-val">{{ $booking->check_in_date->format('M d, Y') }}</div>
                        @if($booking->checkIn)
                            <div style="font-size:12px;color:#10b981;margin-top:2px;">
                                <i class="fas fa-check-circle me-1"></i>
                                Actual: {{ $booking->checkIn->check_in_time->format('M d, Y H:i') }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="info-label">Check-Out</div>
                        <div class="info-val">{{ $booking->check_out_date->format('M d, Y') }}</div>
                        @if($booking->checkOut)
                            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                                <i class="fas fa-check-circle me-1"></i>
                                Actual: {{ $booking->checkOut->check_out_time->format('M d, Y H:i') }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="info-label">Duration</div>
                        <div class="info-val">{{ $booking->nights }} night{{ $booking->nights !== 1 ? 's':'' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Source</div>
                        <div class="info-val">
                            {{ $booking->booking_source === 'online' ? '🌐 Online' : '🚶 Walk-In' }}
                        </div>
                    </div>
                </div>

                @if($booking->special_requests)
                    <div style="margin-top:16px;padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;">
                        <div style="font-size:11px;font-weight:700;color:#a16207;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                            <i class="fas fa-comment-dots me-1"></i> Special Requests
                        </div>
                        <div style="font-size:13.5px;color:#374151;">{{ $booking->special_requests }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Extra Services --}}
        <div class="card mb-4">
            <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <span style="font-size:14px;font-weight:700;color:#111827;">
                    <i class="fas fa-concierge-bell me-2" style="color:#1a56db;"></i>Extra Services
                </span>
                @if($booking->status === 'checked_in')
                    <a href="{{ route('check-outs.create', ['booking_id'=>$booking->id]) }}"
                       style="font-size:12.5px;color:#1a56db;font-weight:600;text-decoration:none;">
                        + Add Service
                    </a>
                @endif
            </div>
            <div class="card-body" style="padding:4px 20px;">
                @forelse($booking->bookingServices as $bs)
                    <div class="service-row">
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:#111827;">{{ $bs->service->name }}</div>
                            <div style="font-size:12px;color:#9ca3af;">Qty: {{ $bs->quantity }} × ${{ number_format($bs->unit_price,2) }}</div>
                        </div>
                        <div style="font-weight:700;color:#111827;">${{ number_format($bs->total_price,2) }}</div>
                    </div>
                @empty
                    <div style="padding:20px 0;text-align:center;color:#9ca3af;font-size:13.5px;">
                        <i class="fas fa-concierge-bell d-block mb-2" style="font-size:22px;opacity:.25;"></i>
                        No extra services added.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Payments --}}
        <div class="card">
            <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;">
                <span style="font-size:14px;font-weight:700;color:#111827;">
                    <i class="fas fa-credit-card me-2" style="color:#1a56db;"></i>Payments
                </span>
            </div>
            <div class="card-body" style="padding:4px 20px;">
                @forelse($booking->payments as $pay)
                    <div class="payment-item">
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:#111827;">
                                {{ ucfirst($pay->payment_type) }}
                                <span style="font-size:12px;font-weight:500;color:#9ca3af;">via {{ ucfirst($pay->method) }}</span>
                            </div>
                            <div style="font-size:12px;color:#9ca3af;">
                                {{ $pay->paid_at ? $pay->paid_at->format('M d, Y H:i') : 'Pending' }}
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-weight:700;color:#111827;">${{ number_format($pay->amount,2) }}</span>
                            <span class="status-pill status-{{ $pay->status }}" style="font-size:11px;">{{ ucfirst($pay->status) }}</span>
                        </div>
                    </div>
                @empty
                    <div style="padding:20px 0;text-align:center;color:#9ca3af;font-size:13.5px;">
                        <i class="fas fa-receipt d-block mb-2" style="font-size:22px;opacity:.25;"></i>
                        No payments recorded yet.
                    </div>
                @endforelse
            </div>
        </div>

    </div>{{-- end left --}}

    {{-- ── RIGHT sidebar ── --}}
    <div>

        {{-- Bill summary --}}
        <div class="card mb-4" style="border-radius:12px;overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-receipt" style="color:#1a56db;"></i> Bill Summary
            </div>

            <div class="totals-row">
                <span class="tl">Room ({{ $booking->nights }}n × ${{ number_format($booking->room_price,0) }})</span>
                <span class="tv">${{ number_format($booking->room_total,2) }}</span>
            </div>
            @if($extraTotal > 0)
                <div class="totals-row">
                    <span class="tl">Extra Services</span>
                    <span class="tv">${{ number_format($extraTotal,2) }}</span>
                </div>
            @endif
            @if($hasDiscount)
                <div class="totals-row">
                    <span class="tl">Subtotal</span>
                    <span class="tv">${{ number_format($subtotal,2) }}</span>
                </div>
                <div class="totals-row">
                    <span class="tl" style="color:#f59e0b;">
                        Discount
                        <span style="font-size:11px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:1px 7px;font-weight:700;margin-left:4px;">{{ number_format($discountRate,1) }}%</span>
                    </span>
                    <span class="tv" style="color:#f59e0b;">-${{ number_format($discountAmount,2) }}</span>
                </div>
                <div class="totals-row">
                    <span class="tl">After Discount</span>
                    <span class="tv">${{ number_format($discountedTotal,2) }}</span>
                </div>
            @endif
            <div class="totals-row">
                <span class="tl">Tax ({{ number_format($taxRate,0) }}%)</span>
                <span class="tv">${{ number_format($tax,2) }}</span>
            </div>
            @if($deposit > 0)
                <div class="totals-row">
                    <span class="tl">Deposit Paid</span>
                    <span class="tv" style="color:#10b981;">-${{ number_format($deposit,2) }}</span>
                </div>
            @endif

            @if($booking->status === 'checked_out' && $invoice)
                {{-- Checked-out: show grand total from invoice, then settlement breakdown --}}
                <div class="totals-row" style="border-top:1px dashed #e5e7eb;margin-top:4px;padding-top:4px;">
                    <span class="tl" style="font-weight:700;color:#111827;">Grand Total</span>
                    <span class="tv" style="font-weight:700;color:#111827;">${{ number_format($invoice->grand_total, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span class="tl">Settlement Due</span>
                    <span class="tv">${{ number_format($settlementDue, 2) }}</span>
                </div>
                @if($settlementPaid > 0)
                <div class="totals-row">
                    <span class="tl">Settlement Paid</span>
                    <span class="tv" style="color:#10b981;">-${{ number_format($settlementPaid, 2) }}</span>
                </div>
                @endif
                <div class="totals-grand">
                    <span style="font-size:14px;font-weight:700;">
                        {{ $balanceRemaining > 0 ? 'Balance Remaining' : 'Fully Settled' }}
                    </span>
                    <span style="font-size:20px;font-weight:800;color:{{ $balanceRemaining > 0 ? '#ef4444' : '#10b981' }};">
                        @if($balanceRemaining > 0)
                            ${{ number_format($balanceRemaining, 2) }}
                        @else
                            <i class="fas fa-check-circle"></i>
                        @endif
                    </span>
                </div>
            @else
                {{-- Not yet checked out: show grand total and what's owed --}}
                <div class="totals-grand">
                    <span style="font-size:14px;font-weight:700;">Grand Total</span>
                    <span style="font-size:20px;font-weight:800;color:#1a56db;">
                        ${{ number_format($grand, 2) }}
                    </span>
                </div>
            @endif

            @if($booking->invoice)
                <div style="padding:12px 18px;border-top:1px solid #f3f4f6;">
                    <a href="{{ route('invoices.show', $booking->invoice) }}" class="action-link" style="margin-bottom:0;">
                        <i class="fas fa-file-invoice" style="color:#1a56db;"></i> View Invoice
                    </a>
                </div>
            @endif
        </div>

        {{-- Room info --}}
        <div class="card mb-4" style="border-radius:12px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;">
                <i class="fas fa-door-open me-2" style="color:#1a56db;"></i>Room Details
            </div>
            <div class="card-body p-3">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:44px;height:44px;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:22px;">🛏️</div>
                    <div>
                        <div style="font-size:16px;font-weight:800;color:#111827;">Room {{ $booking->room->room_number }}</div>
                        <div style="font-size:12.5px;color:#6b7280;">{{ $booking->room->roomType->name }} · Floor {{ $booking->room->floor }}</div>
                    </div>
                </div>
                @if($booking->room->amenities->isNotEmpty())
                    <div style="display:flex;flex-wrap:wrap;gap:5px;">
                        @foreach($booking->room->amenities->take(6) as $am)
                            <span style="background:#f3f4f6;color:#374151;font-size:11px;font-weight:500;padding:3px 8px;border-radius:5px;">{{ $am->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="card" style="border-radius:12px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;">
                <i class="fas fa-bolt me-2" style="color:#1a56db;"></i>Quick Actions
            </div>
            <div class="card-body p-3">
                @if(in_array($booking->status,['pending','confirmed']))
                    <a href="{{ route('bookings.edit', $booking) }}" class="action-link">
                        <i class="fas fa-pen"></i> Edit Booking
                    </a>
                @endif
                @if($booking->status === 'confirmed')
                    <a href="{{ route('check-ins.create', ['booking_id'=>$booking->id]) }}" class="action-link" style="border-color:#86efac;">
                        <i class="fas fa-sign-in-alt" style="color:#10b981;"></i> Process Check-In
                    </a>
                @endif
                @if($booking->status === 'checked_in')
                    <a href="{{ route('check-outs.create', ['booking_id'=>$booking->id]) }}" class="action-link" style="border-color:#fde68a;">
                        <i class="fas fa-sign-out-alt" style="color:#f59e0b;"></i> Process Check-Out
                    </a>
                @endif
                @if($booking->invoice)
                    <a href="{{ route('invoices.pdf', $booking->invoice) }}" class="action-link">
                        <i class="fas fa-file-pdf" style="color:#ef4444;"></i> Download Invoice
                    </a>
                @endif
                <a href="{{ route('payments.create', ['booking_id'=>$booking->id]) }}" class="action-link">
                    <i class="fas fa-credit-card" style="color:#1a56db;"></i> Record Payment
                </a>
                @if($booking->canCancel())
                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                          onsubmit="return confirm('Cancel booking {{ $booking->booking_number }}?')">
                        @csrf
                        <button type="submit"
                            style="width:100%;display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #fca5a5;border-radius:9px;background:#fff;color:#dc2626;font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .15s;"
                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                            <i class="fas fa-times" style="width:16px;"></i> Cancel Booking
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>{{-- end right --}}
</div>

@endsection