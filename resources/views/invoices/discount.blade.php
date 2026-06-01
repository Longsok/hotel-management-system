@extends('layouts.app')

@section('title', 'Apply Discount — ' . $invoice->invoice_number)
@section('page-title', 'Apply Discount')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
    <li class="breadcrumb-item"><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></li>
    <li class="breadcrumb-item active">Apply Discount</li>
@endsection

@section('header-actions')
    <a href="{{ route('invoices.show', $invoice) }}"
       style="padding:7px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
        <i class="fas fa-arrow-left"></i> Back to Invoice
    </a>
@endsection

@push('styles')
<style>
    .discount-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }

    .preview-row {
        display: flex; justify-content: space-between;
        padding: 10px 0; border-bottom: 1px solid #f3f4f6;
        font-size: 13.5px; transition: all .2s;
    }
    .preview-row:last-child { border-bottom: none; }
    .preview-row .pl { color: #6b7280; }
    .preview-row .pv { font-weight: 700; color: #111827; }

    .rate-slider { width: 100%; accent-color: #1a56db; height: 6px; cursor: pointer; }

    @media (max-width: 960px) { .discount-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

@php
    $subtotal = (float)$invoice->subtotal;
    $taxRate  = (float)$invoice->tax_rate;
@endphp

<div class="discount-grid">

    {{-- ── LEFT: Form ── --}}
    <div>

        {{-- Invoice banner --}}
        <div class="card mb-4" style="border-left:4px solid #f59e0b;">
            <div class="card-body py-3 px-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:2px;">Invoice</div>
                        <div style="font-size:16px;font-weight:800;color:#111827;">{{ $invoice->invoice_number }}</div>
                        <div style="font-size:12.5px;color:#6b7280;margin-top:2px;">
                            {{ $invoice->booking->customer->name }}
                            · Room {{ $invoice->booking->room->room_number }}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:11px;color:#9ca3af;margin-bottom:2px;">Current Subtotal</div>
                        <div style="font-size:20px;font-weight:800;color:#111827;">${{ number_format($subtotal, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Discount form --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tag me-2" style="color:#f59e0b;"></i>Discount Settings
            </div>
            <div class="card-body p-4">
                <form action="{{ route('invoices.discount.apply', $invoice) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Slider + input --}}
                    <div class="mb-4">
                        <label style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:8px;display:flex;justify-content:space-between;">
                            <span>Discount Rate</span>
                            <span style="color:#1a56db;font-size:15px;font-weight:800;" id="rate-display">
                                {{ old('discount_rate', $invoice->discount_rate ?? 0) }}%
                            </span>
                        </label>
                        <input type="range" class="rate-slider" id="rate-slider"
                            min="0" max="100" step="1"
                            value="{{ old('discount_rate', $invoice->discount_rate ?? 0) }}"
                            oninput="updateRate(this.value)">
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:#d1d5db;margin-top:4px;">
                            <span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span>
                        </div>

                        {{-- Hidden + visible number input --}}
                        <div style="margin-top:14px;display:flex;align-items:center;gap:10px;">
                            <input type="number" name="discount_rate" id="rate-input"
                                value="{{ old('discount_rate', $invoice->discount_rate ?? 0) }}"
                                min="0" max="100" step="0.5"
                                oninput="updateRate(this.value)"
                                class="form-control"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;width:100px;font-size:16px;font-weight:700;text-align:center;">
                            <span style="font-size:15px;color:#6b7280;font-weight:700;">%</span>
                            <span style="font-size:13px;color:#9ca3af;">Enter a value between 0 and 100</span>
                        </div>
                        @error('discount_rate')
                            <div style="color:#dc2626;font-size:12.5px;margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Reason --}}
                    <div class="mb-4">
                        <label style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;display:block;">
                            Reason for Discount <span style="color:#ef4444;">*</span>
                        </label>
                        <textarea name="discount_reason" rows="3"
                            placeholder="e.g. Loyalty reward, long stay, management discretion…"
                            class="form-control"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:13.5px;resize:none;">{{ old('discount_reason', $invoice->discount_reason) }}</textarea>
                        @error('discount_reason')
                            <div style="color:#dc2626;font-size:12.5px;margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Warning if reducing below paid amount --}}
                    <div id="overcharge-warning"
                         style="display:none;padding:12px 14px;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:9px;margin-bottom:16px;font-size:13px;color:#dc2626;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> The grand total after discount will be less than the amount already paid. A refund may be required.
                    </div>

                    <button type="submit" class="btn btn-primary w-100"
                        style="height:48px;font-size:14px;border-radius:10px;">
                        <i class="fas fa-check-circle me-2"></i>
                        Apply Discount
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Live preview ── --}}
    <div>
        <div class="card" style="border-radius:12px;overflow:hidden;position:sticky;top:80px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;">
                <i class="fas fa-eye me-2" style="color:#1a56db;"></i>Live Preview
            </div>
            <div style="padding:10px 18px;">
                <div class="preview-row">
                    <span class="pl">Subtotal</span>
                    <span class="pv">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="preview-row" id="prev-discount-row">
                    <span class="pl">Discount (<span id="prev-rate">0</span>%)</span>
                    <span class="pv" style="color:#10b981;" id="prev-discount">-$0.00</span>
                </div>
                <div class="preview-row" id="prev-after-row">
                    <span class="pl">After Discount</span>
                    <span class="pv" id="prev-after">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="preview-row">
                    <span class="pl">Tax ({{ number_format($taxRate, 0) }}%)</span>
                    <span class="pv" id="prev-tax">${{ number_format($subtotal * $taxRate / 100, 2) }}</span>
                </div>
            </div>
            <div style="padding:14px 18px;background:linear-gradient(135deg,#1a56db,#3b82f6);border-radius:0 0 12px 12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:rgba(255,255,255,.8);font-size:13px;font-weight:700;">Grand Total</span>
                    <span style="color:#fff;font-size:22px;font-weight:900;" id="prev-grand">
                        ${{ number_format($subtotal * (1 + $taxRate / 100), 2) }}
                    </span>
                </div>
            </div>
        </div>

        @php $paidTotal = $invoice->booking->payments->where('status','paid')->sum('amount'); @endphp
        @if($paidTotal > 0)
            <div style="margin-top:12px;padding:12px 14px;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;">
                <div style="font-weight:700;color:#374151;margin-bottom:4px;">
                    <i class="fas fa-info-circle me-1" style="color:#1a56db;"></i>Already Paid
                </div>
                <div style="color:#6b7280;">
                    ${{ number_format($paidTotal, 2) }} has been collected against this invoice.
                </div>
            </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
const SUBTOTAL = {{ $subtotal }};
const TAX_RATE = {{ $taxRate }};
const PAID     = {{ (float)($invoice->booking->payments->where('status','paid')->sum('amount')) }};

function fmt(n) {
    return parseFloat(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateRate(val) {
    val = Math.min(100, Math.max(0, parseFloat(val) || 0));

    document.getElementById('rate-slider').value = val;
    document.getElementById('rate-input').value  = val;
    document.getElementById('rate-display').textContent = val + '%';

    const discountAmt    = SUBTOTAL * (val / 100);
    const afterDiscount  = SUBTOTAL - discountAmt;
    const taxAmt         = afterDiscount * (TAX_RATE / 100);
    const grand          = afterDiscount + taxAmt;

    document.getElementById('prev-rate').textContent    = val;
    document.getElementById('prev-discount').textContent = '-$' + fmt(discountAmt);
    document.getElementById('prev-after').textContent   = '$' + fmt(afterDiscount);
    document.getElementById('prev-tax').textContent     = '$' + fmt(taxAmt);
    document.getElementById('prev-grand').textContent   = '$' + fmt(grand);

    // Show/hide rows
    const showDiscount = val > 0;
    document.getElementById('prev-discount-row').style.opacity = showDiscount ? '1' : '.3';
    document.getElementById('prev-after-row').style.opacity    = showDiscount ? '1' : '.3';

    // Overcharge warning
    document.getElementById('overcharge-warning').style.display =
        (PAID > 0 && grand < PAID) ? 'block' : 'none';
}

// Init with current value
updateRate(document.getElementById('rate-input').value);
</script>
@endpush