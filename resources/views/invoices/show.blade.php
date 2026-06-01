@extends('layouts.app')

@section('title', $invoice->invoice_number)
@section('page-title', 'Invoice ' . $invoice->invoice_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
    <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('header-actions')
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
           style="padding:7px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-print"></i> Print
        </a>
        <a href="{{ route('invoices.pdf', $invoice) }}"
           style="padding:7px 14px;border-radius:8px;border:1.5px solid #fca5a5;background:#fff;color:#dc2626;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        @if(auth()->user()->isAdmin())
            @if($invoice->status === 'draft')
                <form action="{{ route('invoices.issue', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit"
                        style="padding:7px 14px;border-radius:8px;border:none;background:#1a56db;color:#fff;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;font-family:inherit;">
                        <i class="fas fa-paper-plane"></i> Issue Invoice
                    </button>
                </form>
                <a href="{{ route('invoices.discount', $invoice) }}"
                   style="padding:7px 14px;border-radius:8px;border:1.5px solid #fde68a;background:#fff;color:#d97706;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-tag"></i> Apply Discount
                </a>
            @endif
            @if(in_array($invoice->status, ['draft','issued']))
                <form action="{{ route('invoices.void', $invoice) }}" method="POST"
                      onsubmit="return confirm('Void invoice {{ $invoice->invoice_number }}?')">
                    @csrf
                    <button type="submit"
                        style="padding:7px 14px;border-radius:8px;border:1.5px solid #fca5a5;background:#fff;color:#dc2626;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;font-family:inherit;">
                        <i class="fas fa-ban"></i> Void
                    </button>
                </form>
            @endif
        @endif
        <a href="{{ route('invoices.index') }}"
           style="padding:7px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@endsection

@push('styles')
<style>
    .inv-grid { display: grid; grid-template-columns: 1fr 300px; gap: 24px; align-items: start; }

    /* Line items table */
    .line-th { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; padding: 10px 0; border-bottom: 2px solid #e5e7eb; }
    .line-td { padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 13.5px; }
    .line-td:last-child { text-align: right; font-weight: 700; color: #111827; }

    /* Totals sidebar */
    .tot-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #f3f4f6; font-size: 13.5px; }
    .tot-row:last-child { border-bottom: none; }
    .tot-row .tl { color: #6b7280; }
    .tot-row .tv { font-weight: 700; color: #111827; }
    .tot-grand { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; background: linear-gradient(135deg, #1a56db, #3b82f6); border-radius: 10px; margin-top: 4px; }

    /* Status badge */
    .inv-status-draft   { background: #f1f5f9; color: #475569; }
    .inv-status-issued  { background: #eff6ff; color: #1d4ed8; }
    .inv-status-paid    { background: #f0fdf4; color: #15803d; }
    .inv-status-void    { background: #fef2f2; color: #dc2626; }

    /* Payment pill */
    .pay-pill {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 14px; border-radius: 9px; border: 1px solid #e5e7eb;
        margin-bottom: 8px; font-size: 13px;
    }

    @media (max-width: 960px) { .inv-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

@php
    $booking  = $invoice->booking;
    $customer = $booking->customer;
    $paidTotal = $booking->payments->where('status','paid')->sum('amount');
    $balanceDue = max(0, (float)$invoice->grand_total - $paidTotal);
@endphp

<div class="inv-grid">

    {{-- ── LEFT: Invoice document ── --}}
    <div>

        {{-- Header card --}}
        <div class="card mb-4" style="overflow:hidden;">
            {{-- Status colour strip --}}
            @php
                $stripColor = match($invoice->status) {
                    'issued' => '#1a56db',
                    'paid'   => '#10b981',
                    'void'   => '#ef4444',
                    default  => '#94a3b8',
                };
            @endphp
            <div style="height:4px;background:{{ $stripColor }};"></div>

            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        {{-- Logo / Brand --}}
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                            <div style="width:40px;height:40px;background:#1a56db;border-radius:11px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;box-shadow:0 4px 12px rgba(26,86,219,.3);">
                                <i class="fas fa-hotel"></i>
                            </div>
                            <div>
                                <div style="font-size:17px;font-weight:800;color:#111827;letter-spacing:-.3px;">HotelPro</div>
                                <div style="font-size:11px;color:#9ca3af;letter-spacing:.5px;text-transform:uppercase;">Management System</div>
                            </div>
                        </div>
                        <div style="font-size:11px;color:#9ca3af;line-height:1.7;">
                            123 Hotel Street, Suite 1<br>
                            City, State 10001<br>
                            billing@hotelpro.com
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <div style="font-size:28px;font-weight:900;color:#111827;letter-spacing:-1px;margin-bottom:4px;">INVOICE</div>
                        <div style="font-size:15px;font-weight:700;color:#1a56db;margin-bottom:8px;">{{ $invoice->invoice_number }}</div>
                        <span class="status-pill inv-status-{{ $invoice->status }}" style="font-size:12px;">
                            {{ ucfirst($invoice->status) }}
                        </span>
                        <div style="margin-top:10px;font-size:12px;color:#9ca3af;line-height:1.8;">
                            <span style="font-weight:600;color:#374151;">Issued:</span>
                            {{ $invoice->issued_at ? $invoice->issued_at->format('M d, Y') : '—' }}<br>
                            <span style="font-weight:600;color:#374151;">Created:</span>
                            {{ $invoice->created_at->format('M d, Y') }}<br>
                            @if($invoice->createdBy)
                                <span style="font-weight:600;color:#374151;">By:</span> {{ $invoice->createdBy->name }}
                            @endif
                        </div>
                    </div>
                </div>

                <hr style="border-color:#f3f4f6;margin:20px 0;">

                {{-- Bill To / Booking Info --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div>
                        <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#9ca3af;margin-bottom:6px;">Bill To</div>
                        <div style="font-size:15px;font-weight:700;color:#111827;">{{ $customer->name }}</div>
                        <div style="font-size:13px;color:#6b7280;line-height:1.7;margin-top:3px;">
                            @if($customer->email) {{ $customer->email }}<br> @endif
                            @if($customer->phone) {{ $customer->phone }}<br> @endif
                            @if($customer->address) {{ $customer->address }} @endif
                        </div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#9ca3af;margin-bottom:6px;">Stay Details</div>
                        <div style="font-size:13px;color:#374151;line-height:1.9;">
                            <div><span style="font-weight:600;">Booking:</span>
                                <a href="{{ route('bookings.show', $booking) }}" style="color:#1a56db;text-decoration:none;">{{ $booking->booking_number }}</a>
                            </div>
                            <div><span style="font-weight:600;">Room:</span> {{ $booking->room->room_number }} — {{ $booking->room->roomType->name }}</div>
                            <div><span style="font-weight:600;">Check-In:</span> {{ $booking->check_in_date->format('M d, Y') }}</div>
                            <div><span style="font-weight:600;">Check-Out:</span> {{ $booking->check_out_date->format('M d, Y') }}</div>
                            <div><span style="font-weight:600;">Duration:</span> {{ $booking->nights }} night{{ $booking->nights != 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Line items --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-list me-2" style="color:#1a56db;"></i>Line Items
            </div>
            <div class="card-body px-4 py-3">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th class="line-th" style="width:40%;">Description</th>
                            <th class="line-th" style="text-align:center;">Qty</th>
                            <th class="line-th" style="text-align:right;">Unit Price</th>
                            <th class="line-th" style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Room charge --}}
                        <tr>
                            <td class="line-td">
                                <div style="font-weight:600;color:#111827;">Room {{ $booking->room->room_number }} — {{ $booking->room->roomType->name }}</div>
                                <div style="font-size:12px;color:#9ca3af;">
                                    {{ $booking->check_in_date->format('M d') }} – {{ $booking->check_out_date->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="line-td" style="text-align:center;color:#6b7280;">{{ $booking->nights }}n</td>
                            <td class="line-td" style="text-align:right;color:#6b7280;">${{ number_format($booking->room_price, 2) }}</td>
                            <td class="line-td">${{ number_format($invoice->room_total, 2) }}</td>
                        </tr>

                        {{-- Extra services --}}
                        @foreach($booking->bookingServices as $bs)
                            <tr>
                                <td class="line-td">
                                    <div style="font-weight:600;color:#111827;">{{ $bs->service->name }}</div>
                                    <div style="font-size:12px;color:#9ca3af;">Extra Service</div>
                                </td>
                                <td class="line-td" style="text-align:center;color:#6b7280;">{{ $bs->quantity }}</td>
                                <td class="line-td" style="text-align:right;color:#6b7280;">${{ number_format($bs->unit_price, 2) }}</td>
                                <td class="line-td">${{ number_format($bs->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payments received --}}
        @if($booking->payments->where('status','paid')->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-check-circle me-2" style="color:#10b981;"></i>Payments Received
                </div>
                <div class="card-body px-4 py-3">
                    @foreach($booking->payments->where('status','paid') as $pay)
                        <div class="pay-pill">
                            <div>
                                <div style="font-weight:600;color:#111827;font-size:13.5px;">
                                    {{ ucfirst($pay->payment_type) }}
                                    <span style="font-weight:400;color:#9ca3af;font-size:12px;">via {{ ucfirst($pay->method) }}</span>
                                </div>
                                <div style="font-size:12px;color:#9ca3af;">{{ $pay->paid_at?->format('M d, Y H:i') }}</div>
                            </div>
                            <div style="font-weight:800;font-size:14px;color:#10b981;">
                                +${{ number_format($pay->amount, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- ── RIGHT: Totals & actions ── --}}
    <div>

        {{-- Totals card --}}
        <div class="card mb-4" style="border-radius:12px;overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;">
                <i class="fas fa-calculator me-2" style="color:#1a56db;"></i>Summary
            </div>
            <div style="padding:10px 18px;">
                <div class="tot-row">
                    <span class="tl">Room Total</span>
                    <span class="tv">${{ number_format($invoice->room_total, 2) }}</span>
                </div>
                @if((float)$invoice->extra_total > 0)
                    <div class="tot-row">
                        <span class="tl">Extra Services</span>
                        <span class="tv">${{ number_format($invoice->extra_total, 2) }}</span>
                    </div>
                @endif
                <div class="tot-row">
                    <span class="tl">Subtotal</span>
                    <span class="tv">${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if((float)$invoice->discount_amount > 0)
                    <div class="tot-row">
                        <span class="tl">
                            Discount
                            <span style="font-size:11px;background:#fef9c3;color:#a16207;border-radius:4px;padding:1px 5px;margin-left:4px;">
                                {{ number_format($invoice->discount_rate, 0) }}%
                            </span>
                        </span>
                        <span class="tv" style="color:#10b981;">-${{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                    @if($invoice->discount_reason)
                        <div style="font-size:11.5px;color:#9ca3af;padding:2px 0 8px;font-style:italic;">
                            "{{ $invoice->discount_reason }}"
                        </div>
                    @endif
                    <div class="tot-row">
                        <span class="tl">After Discount</span>
                        <span class="tv">${{ number_format($invoice->discounted_total, 2) }}</span>
                    </div>
                @endif
                <div class="tot-row">
                    <span class="tl">Tax ({{ number_format($invoice->tax_rate, 0) }}%)</span>
                    <span class="tv">${{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
            </div>

            <div style="padding:10px 18px 18px;">
                <div class="tot-grand">
                    <span style="font-size:13px;font-weight:700;color:rgba(255,255,255,.8);">Grand Total</span>
                    <span style="font-size:22px;font-weight:900;color:#fff;">${{ number_format($invoice->grand_total, 2) }}</span>
                </div>

                @if($paidTotal > 0)
                    <div class="tot-row" style="margin-top:10px;">
                        <span class="tl">Total Paid</span>
                        <span class="tv" style="color:#10b981;">-${{ number_format($paidTotal, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-top:2px dashed #e5e7eb;margin-top:4px;">
                        <span style="font-size:13.5px;font-weight:700;color:#111827;">Balance Due</span>
                        <span style="font-size:18px;font-weight:800;color:{{ $balanceDue > 0 ? '#dc2626' : '#10b981' }};">
                            ${{ number_format($balanceDue, 2) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions card --}}
        <div class="card" style="border-radius:12px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;">
                <i class="fas fa-bolt me-2" style="color:#1a56db;"></i>Actions
            </div>
            <div class="card-body p-3">
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
                   style="display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;text-decoration:none;color:#374151;font-size:13.5px;font-weight:600;transition:all .15s;margin-bottom:8px;"
                   onmouseover="this.style.borderColor='#93c5fd';this.style.background='#eff6ff';this.style.color='#1d4ed8';"
                   onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';this.style.color='#374151';">
                    <i class="fas fa-print" style="width:16px;color:#6b7280;"></i> Print Invoice
                </a>
                <a href="{{ route('invoices.pdf', $invoice) }}"
                   style="display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #fca5a5;border-radius:9px;text-decoration:none;color:#dc2626;font-size:13.5px;font-weight:600;transition:all .15s;margin-bottom:8px;"
                   onmouseover="this.style.background='#fef2f2';"
                   onmouseout="this.style.background='#fff';">
                    <i class="fas fa-file-pdf" style="width:16px;"></i> Download PDF
                </a>
                @if($balanceDue > 0 && $invoice->status !== 'void')
                    <a href="{{ route('payments.create', ['booking_id' => $booking->id, 'type' => 'settlement']) }}"
                       style="display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #86efac;border-radius:9px;text-decoration:none;color:#15803d;font-size:13.5px;font-weight:600;transition:all .15s;margin-bottom:8px;"
                       onmouseover="this.style.background='#f0fdf4';"
                       onmouseout="this.style.background='#fff';">
                        <i class="fas fa-credit-card" style="width:16px;"></i>
                        Collect ${{ number_format($balanceDue, 2) }}
                    </a>
                @endif
                <a href="{{ route('bookings.show', $booking) }}"
                   style="display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;text-decoration:none;color:#374151;font-size:13.5px;font-weight:600;transition:all .15s;"
                   onmouseover="this.style.borderColor='#93c5fd';this.style.background='#eff6ff';this.style.color='#1d4ed8';"
                   onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';this.style.color='#374151';">
                    <i class="fas fa-calendar-check" style="width:16px;color:#6b7280;"></i> View Booking
                </a>
            </div>
        </div>

    </div>
</div>

@endsection