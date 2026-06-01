@extends('layouts.app')

@section('title', 'Record Payment')
@section('page-title', 'Record Payment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item active">New Payment</li>
@endsection

@section('header-actions')
    <a href="{{ route('bookings.show', $booking) }}"
       style="padding:7px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
        <i class="fas fa-arrow-left"></i> Back to Booking
    </a>
@endsection

@push('styles')
<style>
    .pay-grid { display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start; }

    /* Method tabs */
    .method-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .method-tab {
        flex: 1; padding: 14px 16px; border-radius: 10px;
        border: 2px solid #e5e7eb; background: #fff;
        cursor: pointer; text-align: center; transition: all .2s;
        font-family: inherit;
    }
    .method-tab:hover { border-color: #93c5fd; background: #eff6ff; }
    .method-tab.active { border-color: #1a56db; background: #eff6ff; }
    .method-tab-icon { font-size: 22px; margin-bottom: 6px; }
    .method-tab-label { font-size: 13px; font-weight: 700; color: #111827; }
    .method-tab-sub   { font-size: 11.5px; color: #9ca3af; margin-top: 2px; }

    /* Bill summary sidebar */
    .bill-row { display: flex; justify-content: space-between; padding: 9px 18px; border-bottom: 1px solid #f3f4f6; font-size: 13.5px; }
    .bill-row .bl { color: #6b7280; }
    .bill-row .bv { font-weight: 700; color: #111827; }
    .bill-grand { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; background: #f9fafb; border-top: 2px solid #e5e7eb; }

    /* Amount input */
    .amount-input-wrap { position: relative; }
    .amount-input-wrap .currency { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #6b7280; font-size: 15px; }
    .amount-input-wrap input { padding-left: 28px !important; font-size: 22px !important; font-weight: 700 !important; height: 56px !important; }

    /* Stripe card element */
    #card-element {
        border: 1.5px solid #e5e7eb;
        border-radius: 9px;
        padding: 14px 14px;
        background: #fff;
        transition: border-color .2s;
    }
    #card-element.focused { border-color: #1a56db; }
    #card-errors { color: #dc2626; font-size: 12.5px; margin-top: 6px; min-height: 18px; }

    /* Stripe button states */
    #stripe-pay-btn:disabled { opacity: .65; cursor: not-allowed; }
    .spinner {
        display: inline-block; width: 14px; height: 14px;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
        margin-right: 6px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 960px) { .pay-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="pay-grid">

    {{-- ── LEFT: Payment form ── --}}
    <div>
        {{-- Booking summary banner --}}
        <div class="card mb-4" style="border-left: 4px solid #1a56db;">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#9ca3af;margin-bottom:3px;">Booking</div>
                        <div style="font-size:16px;font-weight:800;color:#111827;">{{ $booking->booking_number }}</div>
                        <div style="font-size:12.5px;color:#6b7280;margin-top:2px;">
                            {{ $booking->customer->name }}
                            · Room {{ $booking->room->room_number }}
                            · {{ $booking->check_in_date->format('M d') }} – {{ $booking->check_out_date->format('M d, Y') }}
                            ({{ $booking->nights }}n)
                        </div>
                    </div>
                    <span class="status-pill status-{{ $booking->status }}" style="font-size:12px;">
                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Payment type selector --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tag me-2" style="color:#1a56db;"></i>Payment Type
            </div>
            <div class="card-body">
                <div class="method-tabs" id="type-tabs">
                    <button type="button" class="method-tab {{ $type === 'deposit' ? 'active' : '' }}" onclick="selectType('deposit')">
                        <div class="method-tab-icon">💳</div>
                        <div class="method-tab-label">Deposit</div>
                        <div class="method-tab-sub">50% of room total</div>
                    </button>
                    <button type="button" class="method-tab {{ $type === 'settlement' ? 'active' : '' }}" onclick="selectType('settlement')">
                        <div class="method-tab-icon">🏁</div>
                        <div class="method-tab-label">Settlement</div>
                        <div class="method-tab-sub">Final balance due</div>
                    </button>
                </div>
                <input type="hidden" id="selected-type" value="{{ $type }}">
            </div>
        </div>

        {{-- Payment method tabs --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-credit-card me-2" style="color:#1a56db;"></i>Payment Method
            </div>
            <div class="card-body">

                {{-- Method switcher --}}
                <div class="method-tabs" id="method-tabs">
                    <button type="button" class="method-tab active" onclick="switchMethod('cash')" id="tab-cash">
                        <div class="method-tab-icon">💵</div>
                        <div class="method-tab-label">Cash</div>
                        <div class="method-tab-sub">Record manually</div>
                    </button>
                    <button type="button" class="method-tab" onclick="switchMethod('stripe')" id="tab-stripe">
                        <div class="method-tab-icon">
                            <svg height="20" viewBox="0 0 60 25" xmlns="http://www.w3.org/2000/svg">
                                <path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 01-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.45.94V6.27h3.96l.04 1.46c.62-.84 1.66-1.7 3.3-1.7 2.9 0 5.62 2.6 5.62 7.4-.01 5.01-2.71 6.87-5.55 6.87zm-.96-9.18c-.93 0-1.48.35-1.96.97v4.06c.46.59 1.01.93 1.96.93 1.5 0 2.54-1.65 2.54-2.98-.01-1.37-.97-2.98-2.54-2.98zM28.24 5.07L23.79 6l-.04-2.21 4.49-.94zm-4.45 1.2h4.45V20h-4.45zm-4.76 3.43c-.95 0-1.54.45-1.54 1.14 0 .72.75 1.07 2.15 1.51 2.29.72 4.24 1.77 4.24 4.35 0 2.87-2.31 4.3-5.47 4.3a10.18 10.18 0 01-4.82-1.24v-3.78c1.4.93 3.08 1.53 4.82 1.53.94 0 1.6-.37 1.6-1.14 0-.77-.7-1.12-2.19-1.6-2.31-.77-4.24-1.74-4.24-4.25 0-2.76 2.15-4.34 5.37-4.34a9.9 9.9 0 014.21.95v3.7a8.86 8.86 0 00-3.93-.93zm-8.5-3.43V2.85L6.08 3.8V6.27zm-4.45 0h4.45V20H6.08z" fill="#6772E5"/>
                            </svg>
                        </div>
                        <div class="method-tab-label">Stripe</div>
                        <div class="method-tab-sub">Card payment</div>
                    </button>
                </div>

                {{-- ── CASH FORM ── --}}
                <div id="cash-form">
                    <form action="{{ route('payments.cash') }}" method="POST">
                        @csrf
                        <input type="hidden" name="booking_id"   value="{{ $booking->id }}">
                        <input type="hidden" name="payment_type" id="cash-type" value="{{ $type }}">

                        <div class="mb-3">
                            <label style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;display:block;">
                                Amount (USD)
                            </label>
                            <div class="amount-input-wrap">
                                <span class="currency">$</span>
                                <input type="number" name="amount" id="cash-amount"
                                    value="{{ $amount }}" step="0.01" min="0.01"
                                    class="form-control"
                                    style="border-radius:9px;border:1.5px solid #e5e7eb;"
                                    required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;display:block;">
                                Notes <span style="font-weight:400;text-transform:none;">(optional)</span>
                            </label>
                            <textarea name="notes" rows="2" placeholder="e.g. Paid at front desk"
                                class="form-control"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:13.5px;resize:none;">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"
                            style="height:48px;font-size:14px;border-radius:10px;">
                            <i class="fas fa-check-circle me-2"></i>
                            Confirm Cash Payment · $<span id="cash-amount-display">{{ number_format($amount,2) }}</span>
                        </button>
                    </form>
                </div>

                {{-- ── STRIPE FORM ── --}}
                <div id="stripe-form" style="display:none;">
                    <div class="mb-3">
                        <label style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;display:block;">
                            Amount (USD)
                        </label>
                        <div class="amount-input-wrap">
                            <span class="currency">$</span>
                            <input type="number" id="stripe-amount"
                                value="{{ $amount }}" step="0.01" min="0.01"
                                class="form-control"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;"
                                readonly>
                        </div>
                        <div style="font-size:11.5px;color:#9ca3af;margin-top:5px;">
                            <i class="fas fa-lock me-1"></i>Amount is calculated from the booking. Contact admin to adjust.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;display:block;">
                            Card Details
                        </label>
                        <div id="card-element"></div>
                        <div id="card-errors" role="alert"></div>
                    </div>

                    <button id="stripe-pay-btn"
                        style="width:100%;height:48px;border-radius:10px;border:none;background:#1a56db;color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:6px;transition:background .2s;"
                        onmouseover="if(!this.disabled)this.style.background='#1447b5'"
                        onmouseout="if(!this.disabled)this.style.background='#1a56db'">
                        <i class="fas fa-lock me-1"></i>
                        Pay $<span id="stripe-amount-display">{{ number_format($amount,2) }}</span> with Stripe
                    </button>

                    <div style="text-align:center;margin-top:10px;font-size:11.5px;color:#9ca3af;">
                        <i class="fab fa-stripe me-1"></i> Secured by Stripe · Your card details are never stored.
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── RIGHT: Bill summary ── --}}
    <div>
        <div class="card" style="border-radius:12px;overflow:hidden;position:sticky;top:80px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;font-size:13.5px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-receipt" style="color:#1a56db;"></i> Bill Summary
            </div>

            <div class="bill-row">
                <span class="bl">Room <span style="color:#9ca3af;font-weight:400;">({{ $booking->nights }}n × ${{ number_format($booking->room_price, 0) }})</span></span>
                <span class="bv">${{ number_format($booking->room_total, 2) }}</span>
            </div>
            @php
                $extraTotal = $booking->bookingServices->sum('total_price');
            @endphp
            @if($extraTotal > 0)
                <div class="bill-row">
                    <span class="bl">Extra Services</span>
                    <span class="bv">${{ number_format($extraTotal, 2) }}</span>
                </div>
            @endif

            <div class="bill-row" id="bill-subtotal-row" style="display:none;">
                <span class="bl">Subtotal</span>
                <span class="bv" id="bill-subtotal">—</span>
            </div>
            <div class="bill-row" id="bill-tax-row" style="display:none;">
                <span class="bl">Tax</span>
                <span class="bv" id="bill-tax">—</span>
            </div>
            <div class="bill-row" id="bill-discount-row" style="display:none;">
                <span class="bl">Discount</span>
                <span class="bv" id="bill-discount" style="color:#10b981;">—</span>
            </div>
            <div class="bill-row" id="bill-grand-row" style="display:none;">
                <span class="bl">Grand Total</span>
                <span class="bv" id="bill-grand">—</span>
            </div>
            <div class="bill-row" id="bill-paid-row" style="display:none;">
                <span class="bl">Previously Paid</span>
                <span class="bv" id="bill-paid" style="color:#10b981;">—</span>
            </div>

            <div class="bill-grand">
                <span style="font-size:14px;font-weight:700;" id="due-label">
                    @if($type === 'deposit') Deposit Due @else Balance Due @endif
                </span>
                <span style="font-size:22px;font-weight:800;color:#1a56db;">
                    $<span id="due-amount">{{ number_format($amount, 2) }}</span>
                </span>
            </div>

            <div style="padding:14px 18px;border-top:1px solid #f3f4f6;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:8px;">Previous Payments</div>
                @forelse($booking->payments->where('status','paid') as $p)
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px;">
                        <span style="color:#6b7280;">{{ ucfirst($p->payment_type) }} via {{ ucfirst($p->method) }}</span>
                        <span style="font-weight:700;color:#10b981;">+${{ number_format($p->amount, 2) }}</span>
                    </div>
                @empty
                    <div style="font-size:12.5px;color:#9ca3af;">No payments yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
{{-- Stripe.js --}}
<script src="https://js.stripe.com/v3/"></script>

<script>
const BOOKING_ID  = {{ $booking->id }};
const STRIPE_KEY  = '{{ config('services.stripe.key') }}';
const SUMMARY_URL = '{{ route('payments.summary', $booking->id) }}';
const INTENT_URL  = '{{ route('payments.intent') }}';
const CSRF        = '{{ csrf_token() }}';
const SUCCESS_URL = (id) => `/payments/${id}`;

// ── Payment type ─────────────────────────────────────────────────────────────
let currentType   = '{{ $type }}';
let currentMethod = 'cash';

function selectType(type) {
    currentType = type;
    document.getElementById('selected-type').value = type;
    document.getElementById('cash-type').value     = type;

    document.querySelectorAll('#type-tabs .method-tab').forEach((t, i) => {
        t.classList.toggle('active', (i === 0 && type === 'deposit') || (i === 1 && type === 'settlement'));
    });

    document.getElementById('due-label').textContent = type === 'deposit' ? 'Deposit Due' : 'Balance Due';
    loadSummary();
}

// ── Method switch ─────────────────────────────────────────────────────────────
function switchMethod(method) {
    currentMethod = method;
    document.getElementById('cash-form').style.display   = method === 'cash'   ? 'block' : 'none';
    document.getElementById('stripe-form').style.display = method === 'stripe' ? 'block' : 'none';
    document.getElementById('tab-cash').classList.toggle('active',   method === 'cash');
    document.getElementById('tab-stripe').classList.toggle('active', method === 'stripe');

    if (method === 'stripe' && !stripeInitialized) initStripe();
}

// ── Live bill summary ─────────────────────────────────────────────────────────
async function loadSummary() {
    const res  = await fetch(SUMMARY_URL);
    const data = await res.json();

    const show = (id, val) => {
        document.getElementById(id).textContent = val;
        document.getElementById(id + '-row').style.display = 'flex';
    };

    show('bill-subtotal',  '$' + fmt(data.subtotal));
    show('bill-tax',       '$' + fmt(data.tax_amount) + ' (' + data.tax_rate + '%)');
    if (data.discount_amount > 0) show('bill-discount', '-$' + fmt(data.discount_amount));
    show('bill-grand',     '$' + fmt(data.grand_total));
    if (data.total_paid > 0) show('bill-paid', '-$' + fmt(data.total_paid));

    const due = currentType === 'deposit'
        ? data.room_total * 0.5
        : Math.max(0, data.grand_total - data.total_paid);

    document.getElementById('due-amount').textContent          = fmt(due);
    document.getElementById('cash-amount').value               = due.toFixed(2);
    document.getElementById('cash-amount-display').textContent = fmt(due);
    document.getElementById('stripe-amount').value             = due.toFixed(2);
    document.getElementById('stripe-amount-display').textContent = fmt(due);
}

function fmt(n) {
    return parseFloat(n).toLocaleString('en-US', { minimumFractionDigits:2, maximumFractionDigits:2 });
}

// sync cash display
document.getElementById('cash-amount').addEventListener('input', function() {
    document.getElementById('cash-amount-display').textContent = fmt(this.value || 0);
});

// ── Stripe ────────────────────────────────────────────────────────────────────
let stripe, cardElement, stripeInitialized = false;

function initStripe() {
    stripeInitialized = true;
    stripe     = Stripe(STRIPE_KEY);
    const elements = stripe.elements();
    cardElement    = elements.create('card', {
        style: {
            base: {
                fontFamily: "'Plus Jakarta Sans', sans-serif",
                fontSize:   '14px',
                color:      '#111827',
                '::placeholder': { color: '#9ca3af' },
            },
        },
    });
    cardElement.mount('#card-element');

    cardElement.on('focus', ()  => document.getElementById('card-element').classList.add('focused'));
    cardElement.on('blur',  ()  => document.getElementById('card-element').classList.remove('focused'));
    cardElement.on('change', e  => {
        document.getElementById('card-errors').textContent = e.error ? e.error.message : '';
    });
}

document.getElementById('stripe-pay-btn').addEventListener('click', async () => {
    const btn = document.getElementById('stripe-pay-btn');
    const errEl = document.getElementById('card-errors');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Processing…';
    errEl.textContent = '';

    try {
        // 1. Create a PaymentIntent
        const intentRes = await fetch(INTENT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ booking_id: BOOKING_ID, payment_type: currentType }),
        });
        const intentData = await intentRes.json();

        if (!intentRes.ok) {
            throw new Error(intentData.message || 'Could not create payment intent.');
        }

        // 2. Confirm card payment
        const { paymentIntent, error } = await stripe.confirmCardPayment(
            intentData.client_secret,
            { payment_method: { card: cardElement } }
        );

        if (error) {
            throw new Error(error.message);
        }

        // 3. Redirect to payment detail (webhook will update status async, show pending)
        window.location.href = SUCCESS_URL(intentData.payment_id);

    } catch (err) {
        errEl.textContent = err.message;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-1"></i> Pay $<span id="stripe-amount-display">' +
            document.getElementById('stripe-amount-display').textContent + '</span> with Stripe';
    }
});

// ── Init ──────────────────────────────────────────────────────────────────────
loadSummary();
</script>
@endpush