@extends('layouts.app')

@section('title', 'Payment #' . $payment->id)
@section('page-title', 'Payment #' . $payment->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item active">#{{ $payment->id }}</li>
@endsection

@section('header-actions')
    <div style="display:flex;gap:8px;">
        @if(auth()->user()->isAdmin() && $payment->status === 'paid')
            <form action="{{ route('payments.refund', $payment) }}" method="POST"
                  onsubmit="return confirm('Refund payment #{{ $payment->id }}? This cannot be undone.')">
                @csrf
                <button type="submit"
                    style="padding:7px 14px;border-radius:8px;border:1.5px solid #fca5a5;background:#fff;color:#dc2626;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;font-family:inherit;">
                    <i class="fas fa-undo-alt"></i> Refund
                </button>
            </form>
        @endif
        <a href="{{ route('payments.index') }}"
           style="padding:7px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@endsection

@push('styles')
<style>
    .detail-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
    .info-row:last-child { border-bottom: none; }
    .info-row .il { font-size: 12.5px; color: #6b7280; font-weight: 500; }
    .info-row .iv { font-size: 13.5px; color: #111827; font-weight: 600; }

    /* Status banner colors */
    .status-banner { border-radius: 12px; padding: 20px 24px; display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
    .status-banner.paid     { background: #f0fdf4; border: 1.5px solid #86efac; }
    .status-banner.pending  { background: #fffbeb; border: 1.5px solid #fde68a; }
    .status-banner.failed   { background: #fef2f2; border: 1.5px solid #fca5a5; }
    .status-banner.refunded { background: #f5f3ff; border: 1.5px solid #c4b5fd; }
    .status-icon { width: 48px; height: 48px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .status-icon.paid     { background: #dcfce7; color: #15803d; }
    .status-icon.pending  { background: #fef9c3; color: #a16207; }
    .status-icon.failed   { background: #fee2e2; color: #dc2626; }
    .status-icon.refunded { background: #ede9fe; color: #6d28d9; }

    @media (max-width: 960px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="detail-grid">

    {{-- ── LEFT ── --}}
    <div>

        {{-- Status banner --}}
        <div class="status-banner {{ $payment->status }}">
            <div class="status-icon {{ $payment->status }}">
                @if($payment->status === 'paid')     <i class="fas fa-check-circle"></i>
                @elseif($payment->status === 'pending') <i class="fas fa-clock"></i>
                @elseif($payment->status === 'failed')  <i class="fas fa-times-circle"></i>
                @else                                    <i class="fas fa-undo-alt"></i>
                @endif
            </div>
            <div>
                <div style="font-size:18px;font-weight:800;color:#111827;">
                    ${{ number_format($payment->amount, 2) }}
                    <span class="status-pill status-{{ $payment->status }}" style="font-size:12px;margin-left:6px;">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
                <div style="font-size:12.5px;color:#6b7280;margin-top:3px;">
                    {{ ucfirst($payment->payment_type) }} payment
                    via {{ ucfirst($payment->method) }}
                    @if($payment->paid_at)
                        · Paid on {{ $payment->paid_at->format('M d, Y \a\t H:i') }}
                    @elseif($payment->status === 'pending')
                        · Awaiting confirmation
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment details --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2" style="color:#1a56db;"></i>Payment Details
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="il">Payment ID</span>
                    <span class="iv">#{{ $payment->id }}</span>
                </div>
                <div class="info-row">
                    <span class="il">Type</span>
                    <span class="iv">{{ ucfirst($payment->payment_type) }}</span>
                </div>
                <div class="info-row">
                    <span class="il">Method</span>
                    <span class="iv">
                        @if($payment->method === 'stripe')
                            <i class="fab fa-stripe" style="color:#6772e5;font-size:18px;"></i>
                        @elseif($payment->method === 'cash')
                            <i class="fas fa-money-bill-wave" style="color:#10b981;margin-right:4px;"></i>Cash
                        @else
                            {{ ucfirst($payment->method) }}
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="il">Amount</span>
                    <span class="iv" style="font-size:16px;font-weight:800;color:#1a56db;">${{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="il">Status</span>
                    <span class="iv"><span class="status-pill status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></span>
                </div>
                @if($payment->transaction_ref)
                    <div class="info-row">
                        <span class="il">Transaction Ref</span>
                        <span class="iv" style="font-family:monospace;font-size:12px;color:#6b7280;">
                            {{ $payment->transaction_ref }}
                        </span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="il">Paid At</span>
                    <span class="iv">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y H:i') : '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="il">Created</span>
                    <span class="iv">{{ $payment->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="il">Recorded By</span>
                    <span class="iv">{{ $payment->createdBy->name ?? '—' }}</span>
                </div>
                @if($payment->notes)
                    <div style="margin-top:8px;padding:10px 12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:4px;">Notes</div>
                        <div style="font-size:13.5px;color:#374151;">{{ $payment->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Stripe status note --}}
        @if($payment->method === 'stripe' && $payment->status === 'pending')
            <div style="padding:14px 18px;background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;display:flex;align-items:flex-start;gap:12px;">
                <i class="fas fa-circle-notch fa-spin" style="color:#d97706;margin-top:3px;"></i>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#a16207;margin-bottom:3px;">Awaiting Stripe confirmation</div>
                    <div style="font-size:12.5px;color:#92400e;">The payment was initiated but not yet confirmed. Stripe will send a webhook once the card is charged. Refresh this page in a few seconds.</div>
                </div>
            </div>
        @endif

    </div>

    {{-- ── RIGHT sidebar ── --}}
    <div>

        {{-- Booking card --}}
        <div class="card mb-4" style="border-radius:12px;overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;">
                <i class="fas fa-calendar-check me-2" style="color:#1a56db;"></i>Booking
            </div>
            <div class="card-body p-4">
                <div style="margin-bottom:10px;">
                    <a href="{{ route('bookings.show', $payment->booking) }}"
                       style="font-size:17px;font-weight:800;color:#1a56db;text-decoration:none;">
                        {{ $payment->booking->booking_number }}
                    </a>
                    <span class="status-pill status-{{ $payment->booking->status }}" style="font-size:11px;margin-left:6px;">
                        {{ ucfirst(str_replace('_', ' ', $payment->booking->status)) }}
                    </span>
                </div>
                <div style="font-size:13px;color:#6b7280;line-height:1.8;">
                    <div><i class="fas fa-user me-2" style="width:14px;color:#9ca3af;"></i>{{ $payment->booking->customer->name }}</div>
                    <div><i class="fas fa-door-open me-2" style="width:14px;color:#9ca3af;"></i>Room {{ $payment->booking->room->room_number }}</div>
                    <div>
                        <i class="fas fa-calendar me-2" style="width:14px;color:#9ca3af;"></i>
                        {{ $payment->booking->check_in_date->format('M d') }} –
                        {{ $payment->booking->check_out_date->format('M d, Y') }}
                    </div>
                </div>
                <a href="{{ route('bookings.show', $payment->booking) }}"
                   style="display:block;margin-top:12px;text-align:center;padding:8px;border-radius:8px;border:1.5px solid #e5e7eb;font-size:13px;font-weight:600;color:#374151;text-decoration:none;transition:all .15s;"
                   onmouseover="this.style.borderColor='#93c5fd';this.style.background='#eff6ff';this.style.color='#1d4ed8';"
                   onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';this.style.color='#374151';">
                    View Full Booking <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="card" style="border-radius:12px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;">
                <i class="fas fa-bolt me-2" style="color:#1a56db;"></i>Quick Actions
            </div>
            <div class="card-body p-3">
                <a href="{{ route('payments.create', ['booking_id' => $payment->booking_id, 'type' => 'settlement']) }}"
                   style="display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;text-decoration:none;color:#374151;font-size:13.5px;font-weight:600;transition:all .15s;margin-bottom:8px;"
                   onmouseover="this.style.borderColor='#93c5fd';this.style.background='#eff6ff';this.style.color='#1d4ed8';"
                   onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';this.style.color='#374151';">
                    <i class="fas fa-plus-circle" style="width:16px;color:#1a56db;"></i> Add Another Payment
                </a>
                @if($payment->booking->invoice)
                    <a href="{{ route('invoices.show', $payment->booking->invoice) }}"
                       style="display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;text-decoration:none;color:#374151;font-size:13.5px;font-weight:600;transition:all .15s;"
                       onmouseover="this.style.borderColor='#93c5fd';this.style.background='#eff6ff';this.style.color='#1d4ed8';"
                       onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';this.style.color='#374151';">
                        <i class="fas fa-file-invoice" style="width:16px;color:#1a56db;"></i> View Invoice
                    </a>
                @endif
            </div>
        </div>

    </div>

</div>

@endsection