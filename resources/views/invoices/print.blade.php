<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #111827;
            background: #fff;
            padding: 0;
        }

        /* ── Page wrapper ─────────────────────────────────────────────── */
        .page {
            max-width: 780px;
            margin: 0 auto;
            padding: 48px 56px;
            background: #fff;
        }

        /* ── Header ───────────────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 36px;
            padding-bottom: 28px;
            border-bottom: 2px solid #f3f4f6;
        }
        .brand-name {
            font-size: 22px;
            font-weight: 900;
            color: #111827;
            letter-spacing: -0.5px;
        }
        .brand-name span { color: #1a56db; }
        .brand-sub { font-size: 11px; color: #9ca3af; margin-top: 2px; }
        .brand-contact { font-size: 11.5px; color: #6b7280; margin-top: 10px; line-height: 1.8; }

        .inv-meta { text-align: right; }
        .inv-title { font-size: 30px; font-weight: 900; color: #111827; letter-spacing: -1px; margin-bottom: 4px; }
        .inv-number { font-size: 15px; font-weight: 700; color: #1a56db; margin-bottom: 8px; }
        .inv-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-draft   { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-issued  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-paid    { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
        .badge-void    { background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }

        .inv-dates { font-size: 11.5px; color: #6b7280; margin-top: 8px; line-height: 1.8; }
        .inv-dates strong { color: #374151; }

        /* ── Bill-to / Stay section ───────────────────────────────────── */
        .addresses {
            display: flex;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .addr-block { width: 46%; }
        .addr-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #9ca3af;
            margin-bottom: 6px;
        }
        .addr-name { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 3px; }
        .addr-detail { font-size: 12px; color: #6b7280; line-height: 1.8; }

        /* ── Line items table ─────────────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .items-table thead th {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #9ca3af;
            padding: 8px 10px;
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }
        .items-table thead th:last-child { text-align: right; }
        .items-table thead th:nth-child(2),
        .items-table thead th:nth-child(3) { text-align: center; }
        .items-table tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .items-table tbody td:last-child { text-align: right; font-weight: 700; }
        .items-table tbody td:nth-child(2),
        .items-table tbody td:nth-child(3) { text-align: center; color: #6b7280; }
        .item-name { font-weight: 600; color: #111827; }
        .item-sub  { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        /* ── Totals ───────────────────────────────────────────────────── */
        .totals { display: flex; justify-content: flex-end; margin-bottom: 28px; }
        .totals-box { width: 260px; }
        .tot-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }
        .tot-row:last-child { border-bottom: none; }
        .tot-row .tl { color: #6b7280; }
        .tot-row .tv { font-weight: 700; }
        .tot-grand {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            padding: 12px 14px;
            background: #1a56db;
            border-radius: 9px;
            color: #fff;
        }
        .tot-grand .gl { font-size: 13px; font-weight: 700; opacity: .85; }
        .tot-grand .gv { font-size: 20px; font-weight: 900; }

        .balance-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 14px;
            border: 1.5px dashed #e5e7eb;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 13px;
        }
        .balance-row .bl { font-weight: 600; color: #374151; }
        .balance-row .bv { font-weight: 800; }

        /* ── Payments ─────────────────────────────────────────────────── */
        .pay-section { margin-bottom: 28px; }
        .section-title {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #9ca3af;
            margin-bottom: 10px;
        }
        .pay-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: #f9fafb;
            border-radius: 7px;
            margin-bottom: 5px;
        }
        .pay-row .pm { font-size: 12.5px; color: #374151; font-weight: 500; }
        .pay-row .pv { font-size: 13px; font-weight: 700; color: #15803d; }

        /* ── Footer ───────────────────────────────────────────────────── */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-note { font-size: 11px; color: #9ca3af; }
        .footer-brand { font-size: 11px; color: #d1d5db; font-weight: 600; }

        /* ── Print button (hidden on print) ──────────────────────────── */
        .print-bar {
            text-align: center;
            padding: 16px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .print-btn {
            padding: 9px 20px;
            border-radius: 8px;
            border: none;
            background: #1a56db;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }
        .close-btn {
            padding: 9px 20px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            background: #fff;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        @media print {
            .print-bar { display: none !important; }
            body { padding: 0; }
            .page { padding: 30px 36px; max-width: 100%; }
        }
    </style>
</head>
<body>

@php
    $booking    = $invoice->booking;
    $customer   = $booking->customer;
    $paidTotal  = $booking->payments->where('status','paid')->sum('amount');
    $balanceDue = max(0, (float)$invoice->grand_total - $paidTotal);
@endphp

{{-- Print bar (hidden in PDF, shown in browser only) --}}
@unless($isPdf ?? false)
<div class="print-bar">
    <button class="print-btn" onclick="window.print()">
        🖨️ Print
    </button>
    <a href="{{ route('invoices.pdf', $invoice) }}"
       style="padding:9px 20px;border-radius:8px;border:1.5px solid #fca5a5;background:#fff;color:#dc2626;font-size:13px;font-weight:600;text-decoration:none;">
        📥 Download PDF
    </a>
    <button class="close-btn" onclick="window.close()">✕ Close</button>
</div>
@endunless

<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="brand-name">{{ \App\Models\Setting::hotelName() }}</div>
            <div class="brand-sub">{{ strtoupper(\App\Models\Setting::hotelTagline()) }}</div>
            <div class="brand-contact">
                @if(\App\Models\Setting::hotelAddress())
                    {{ \App\Models\Setting::hotelAddress() }}<br>
                @endif
                @if(\App\Models\Setting::hotelCity())
                    {{ \App\Models\Setting::hotelCity() }}<br>
                @endif
                @if(\App\Models\Setting::hotelEmail() || \App\Models\Setting::hotelPhone())
                    {{ \App\Models\Setting::hotelEmail() }}
                    @if(\App\Models\Setting::hotelEmail() && \App\Models\Setting::hotelPhone()) · @endif
                    {{ \App\Models\Setting::hotelPhone() }}
                @endif
            </div>
        </div>
        <div class="inv-meta">
            <div class="inv-title">INVOICE</div>
            <div class="inv-number">{{ $invoice->invoice_number }}</div>
            <span class="inv-badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
            <div class="inv-dates">
                <strong>Issued:</strong> {{ $invoice->issued_at ? $invoice->issued_at->format('M d, Y') : '—' }}<br>
                <strong>Created:</strong> {{ $invoice->created_at->format('M d, Y') }}
            </div>
        </div>
    </div>

    {{-- Addresses --}}
    <div class="addresses">
        <div class="addr-block">
            <div class="addr-label">Bill To</div>
            <div class="addr-name">{{ $customer->name }}</div>
            <div class="addr-detail">
                @if($customer->email) {{ $customer->email }}<br> @endif
                @if($customer->phone) {{ $customer->phone }}<br> @endif
                @if($customer->address) {{ $customer->address }} @endif
            </div>
        </div>
        <div class="addr-block" style="text-align:right;">
            <div class="addr-label">Stay Details</div>
            <div class="addr-detail">
                <strong>Booking:</strong> {{ $booking->booking_number }}<br>
                <strong>Room:</strong> {{ $booking->room->room_number }} — {{ $booking->room->roomType->name }}<br>
                <strong>Check-In:</strong> {{ $booking->check_in_date->format('M d, Y') }}<br>
                <strong>Check-Out:</strong> {{ $booking->check_out_date->format('M d, Y') }}<br>
                <strong>Duration:</strong> {{ $booking->nights }} night{{ $booking->nights != 1 ? 's' : '' }}
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50%;text-align:left;">Description</th>
                <th style="width:10%;">Qty</th>
                <th style="width:20%;">Unit Price</th>
                <th style="width:20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="item-name">Room {{ $booking->room->room_number }} — {{ $booking->room->roomType->name }}</div>
                    <div class="item-sub">
                        {{ $booking->check_in_date->format('M d') }} – {{ $booking->check_out_date->format('M d, Y') }}
                    </div>
                </td>
                <td>{{ $booking->nights }}n</td>
                <td>${{ number_format($booking->room_price, 2) }}</td>
                <td>${{ number_format($invoice->room_total, 2) }}</td>
            </tr>
            @foreach($booking->bookingServices as $bs)
                <tr>
                    <td>
                        <div class="item-name">{{ $bs->service->name }}</div>
                        <div class="item-sub">Extra Service</div>
                    </td>
                    <td>{{ $bs->quantity }}</td>
                    <td>${{ number_format($bs->unit_price, 2) }}</td>
                    <td>${{ number_format($bs->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Payments --}}
    @if($booking->payments->where('status','paid')->isNotEmpty())
        <div class="pay-section">
            <div class="section-title">Payments Received</div>
            @foreach($booking->payments->where('status','paid') as $pay)
                <div class="pay-row">
                    <span class="pm">
                        {{ ucfirst($pay->payment_type) }} via {{ ucfirst($pay->method) }}
                        — {{ $pay->paid_at?->format('M d, Y') }}
                    </span>
                    <span class="pv">+${{ number_format($pay->amount, 2) }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Totals --}}
    <div class="totals">
        <div class="totals-box">
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
                    <span class="tl">Discount ({{ number_format($invoice->discount_rate, 0) }}%)</span>
                    <span class="tv" style="color:#10b981;">-${{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                <div class="tot-row">
                    <span class="tl">After Discount</span>
                    <span class="tv">${{ number_format($invoice->discounted_total, 2) }}</span>
                </div>
            @endif
            <div class="tot-row">
                <span class="tl">Tax ({{ number_format($invoice->tax_rate, 0) }}%)</span>
                <span class="tv">${{ number_format($invoice->tax_amount, 2) }}</span>
            </div>

            <div class="tot-grand">
                <span class="gl">Grand Total</span>
                <span class="gv">${{ number_format($invoice->grand_total, 2) }}</span>
            </div>

            @if($paidTotal > 0)
                <div class="tot-row" style="margin-top:8px;">
                    <span class="tl">Total Paid</span>
                    <span class="tv" style="color:#10b981;">-${{ number_format($paidTotal, 2) }}</span>
                </div>
                <div class="balance-row">
                    <span class="bl">Balance Due</span>
                    <span class="bv" style="color:{{ $balanceDue > 0 ? '#dc2626' : '#10b981' }};">
                        ${{ number_format($balanceDue, 2) }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- Discount note --}}
    @if($invoice->discount_reason)
        <div style="padding:10px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#92400e;margin-bottom:24px;">
            <strong>Discount Note:</strong> {{ $invoice->discount_reason }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-note">
            {{ \App\Models\Setting::invoiceFooterNote() }}
        </div>
        <div class="footer-brand">{{ \App\Models\Setting::hotelName() }} &copy; {{ date('Y') }}</div>
    </div>

</div>

</body>
</html>