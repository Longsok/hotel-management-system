@extends('layouts.guest')
@section('title', 'Book Your Stay')

@push('styles')
<style>
/* ── Hero ── */
.hero {
    background: linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1a56db 100%);
    padding: 72px 24px 100px; text-align: center; position: relative; overflow: hidden;
}
.hero::before {
    content:''; position:absolute; inset:0;
    background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.hero-title { font-size:44px;font-weight:800;color:#fff;line-height:1.15;margin-bottom:12px;position:relative; }
.hero-title span { color:#93c5fd; }
.hero-sub { font-size:16px;color:rgba(255,255,255,.7);margin-bottom:40px;position:relative; }

/* Search bar */
.search-bar {
    background:#fff; border-radius:16px; padding:20px 24px;
    max-width:820px; margin:0 auto;
    display:flex; gap:14px; align-items:flex-end; flex-wrap:wrap;
    box-shadow:0 20px 50px rgba(0,0,0,.25); position:relative;
}
.sf { flex:1; min-width:170px; }
.sf label { display:block;font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px; }
.sf-input {
    width:100%;height:44px;padding:0 14px;
    border:1.5px solid #e5e7eb;border-radius:10px;
    font-family:inherit;font-size:14px;color:#111827;
    outline:none;background:#f9fafb;transition:border-color .2s,box-shadow .2s;
}
.sf-input:focus { border-color:#1a56db;background:#fff;box-shadow:0 0 0 3px rgba(26,86,219,.09); }
.search-btn {
    height:44px;padding:0 28px;border-radius:10px;border:none;
    background:#1a56db;color:#fff;font-family:inherit;font-size:14px;font-weight:700;
    cursor:pointer;display:flex;align-items:center;gap:8px;white-space:nowrap;
    box-shadow:0 4px 14px rgba(26,86,219,.4);transition:background .15s,transform .15s;
}
.search-btn:hover { background:#1447b5;transform:translateY(-1px); }

/* ── Page wrap ── */
.page-wrap { max-width:1100px;margin:0 auto;padding:0 24px; }

/* ── How it works ── */
.steps-section { padding:60px 0 50px; }
.section-label { font-size:11px;font-weight:700;color:#1a56db;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;text-align:center; }
.section-title { font-size:28px;font-weight:800;color:#111827;text-align:center;margin-bottom:8px; }
.section-sub   { font-size:14px;color:#6b7280;text-align:center;margin-bottom:40px; }
.steps-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:24px; }
.step-card { text-align:center;padding:24px 16px; }
.step-num {
    width:52px;height:52px;border-radius:50%;
    background:linear-gradient(135deg,#eff6ff,#dbeafe);border:2px solid #bfdbfe;
    display:flex;align-items:center;justify-content:center;
    font-size:20px;font-weight:800;color:#1a56db;
    margin:0 auto 16px;
}
.step-icon { font-size:22px;color:#1a56db;margin-bottom:12px;display:block; }
.step-title { font-size:14px;font-weight:700;color:#111827;margin-bottom:6px; }
.step-desc  { font-size:13px;color:#6b7280;line-height:1.6; }
.step-arrow { display:flex;align-items:center;justify-content:center;padding-top:26px;font-size:20px;color:#d1d5db; }

/* ── Rooms section ── */
.rooms-section { padding:10px 0 50px; }
.rooms-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:22px; }
.room-card {
    background:#fff;border:2px solid #e5e7eb;border-radius:14px;
    overflow:hidden;cursor:pointer;transition:all .2s;
}
.room-card:hover { border-color:#93c5fd;box-shadow:0 8px 28px rgba(26,86,219,.1);transform:translateY(-3px); }
.room-img { width:100%;height:190px;object-fit:cover;background:linear-gradient(135deg,#e0e7ff,#dbeafe);display:flex;align-items:center;justify-content:center;font-size:44px;color:#93c5fd; }
.room-img img { width:100%;height:100%;object-fit:cover; }
.room-body { padding:18px 20px 20px; }
.room-type  { font-size:11px;font-weight:700;color:#1a56db;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px; }
.room-num   { font-size:18px;font-weight:700;color:#111827;margin-bottom:3px; }
.room-floor { font-size:12px;color:#9ca3af;margin-bottom:10px; }
.room-tags  { display:flex;gap:5px;flex-wrap:wrap;margin-bottom:14px; }
.room-tag   { padding:3px 9px;background:#f1f5f9;border-radius:20px;font-size:11.5px;color:#475569;font-weight:500; }
.room-foot  { display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid #f3f4f6; }
.room-price { font-size:20px;font-weight:800;color:#1a56db; }
.room-price-sub { font-size:11px;color:#9ca3af; }
.book-btn { padding:9px 18px;border-radius:9px;border:none;background:#1a56db;color:#fff;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;transition:background .15s; }
.book-btn:hover { background:#1447b5; }

/* ── Features section ── */
.features-section { background:#f8faff;border-top:1px solid #e0e7ff;border-bottom:1px solid #e0e7ff;padding:60px 0; }
.features-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:28px; }
.feat-card { background:#fff;border-radius:12px;padding:24px;border:1px solid #e5e7eb;text-align:center; }
.feat-icon { width:52px;height:52px;border-radius:12px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:22px;color:#1a56db;margin:0 auto 14px; }
.feat-title { font-size:15px;font-weight:700;color:#111827;margin-bottom:6px; }
.feat-desc  { font-size:13px;color:#6b7280;line-height:1.6; }

/* ── Location section ── */
.location-section { padding:60px 0; }
.location-grid { display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center; }
.loc-map {
    background:linear-gradient(135deg,#e0e7ff,#dbeafe);
    border-radius:16px;height:320px;
    display:flex;align-items:center;justify-content:center;
    border:1px solid #bfdbfe;overflow:hidden;
}
.loc-map iframe { width:100%;height:100%;border:none;border-radius:16px; }
.loc-info-row { display:flex;align-items:flex-start;gap:14px;margin-bottom:20px; }
.loc-icon { width:40px;height:40px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:16px;color:#1a56db;flex-shrink:0; }
.loc-title { font-size:14px;font-weight:700;color:#111827;margin-bottom:3px; }
.loc-val   { font-size:13.5px;color:#6b7280; }

/* ── Room detail modal ── */
.modal-backdrop { display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1000;backdrop-filter:blur(3px); }
.modal-backdrop.open { display:flex;align-items:center;justify-content:center;padding:20px; }
.modal {
    background:#fff;border-radius:18px;max-width:680px;width:100%;
    max-height:90vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.2);
}
.modal-img { width:100%;height:240px;object-fit:cover;border-radius:18px 18px 0 0;background:linear-gradient(135deg,#e0e7ff,#dbeafe);display:flex;align-items:center;justify-content:center;font-size:52px;color:#93c5fd; }
.modal-img img { width:100%;height:100%;object-fit:cover;border-radius:18px 18px 0 0; }
.modal-body { padding:24px 28px 32px; }
.modal-type  { font-size:11px;font-weight:700;color:#1a56db;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px; }
.modal-title { font-size:24px;font-weight:800;color:#111827;margin-bottom:6px; }
.modal-floor { font-size:13px;color:#9ca3af;margin-bottom:14px; }
.modal-desc  { font-size:14px;color:#6b7280;line-height:1.7;margin-bottom:18px; }
.modal-amenities { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:22px; }
.modal-tag { padding:5px 12px;background:#f1f5f9;border-radius:20px;font-size:12.5px;color:#475569;font-weight:500; }
.modal-price-row { display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-top:1px solid #f3f4f6;border-bottom:1px solid #f3f4f6;margin-bottom:20px; }
.modal-price { font-size:28px;font-weight:800;color:#1a56db; }
.modal-close { position:absolute;top:14px;right:14px;width:34px;height:34px;border-radius:50%;background:rgba(0,0,0,.45);border:none;color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center; }
.modal-header-wrap { position:relative; }
.btn-book-modal { width:100%;padding:14px;border-radius:11px;border:none;background:linear-gradient(135deg,#1a56db,#3b82f6);color:#fff;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 4px 18px rgba(26,86,219,.35);transition:opacity .2s; }
.btn-book-modal:hover { opacity:.9; }

/* ── Booking form ── */
.booking-panel { background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:28px;box-shadow:0 4px 24px rgba(0,0,0,.07);margin:40px 0 60px; }
.panel-hd { font-size:18px;font-weight:700;color:#111827;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:10px; }
.panel-hd i { color:#1a56db; }
.form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
.form-full { grid-column:span 2; }
.f-label { display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px; }
.f-label .req { color:#ef4444;margin-left:2px; }
.f-input,.f-textarea { width:100%;padding:10px 13px;border:1.5px solid #e5e7eb;border-radius:9px;font-family:inherit;font-size:13.5px;color:#111827;background:#f9fafb;outline:none;transition:border-color .2s,box-shadow .2s; }
.f-input:focus,.f-textarea:focus { border-color:#1a56db;background:#fff;box-shadow:0 0 0 3px rgba(26,86,219,.08); }
.f-textarea { resize:vertical;min-height:80px; }
.f-hint { font-size:11.5px;color:#9ca3af;margin-top:4px; }
.booking-summary { background:#f8faff;border:1px solid #dbeafe;border-radius:12px;padding:18px 20px;margin-bottom:22px; }
.bs-title { font-size:12px;font-weight:700;color:#1a56db;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px; }
.bs-row { display:flex;justify-content:space-between;font-size:13.5px;padding:5px 0;border-bottom:1px solid #e0e7ff; }
.bs-row:last-child { border-bottom:none; }
.bs-lbl { color:#6b7280; }
.bs-val { font-weight:600;color:#111827; }
.bs-deposit { font-size:16px;font-weight:800;color:#1a56db; }
.stripe-wrap { border:1.5px solid #e5e7eb;border-radius:9px;padding:12px 14px;background:#f9fafb;transition:border-color .2s; }
.stripe-wrap.focused { border-color:#1a56db;background:#fff;box-shadow:0 0 0 3px rgba(26,86,219,.08); }
.btn-pay { width:100%;padding:14px;border-radius:11px;border:none;background:linear-gradient(135deg,#1a56db,#3b82f6);color:#fff;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 4px 18px rgba(26,86,219,.35);transition:opacity .2s,transform .15s;margin-top:20px; }
.btn-pay:hover:not(:disabled) { opacity:.92;transform:translateY(-1px); }
.btn-pay:disabled { opacity:.6;cursor:not-allowed;transform:none; }
.pay-error { background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;font-size:13px;color:#dc2626;display:none;margin-top:12px;align-items:center;gap:8px; }
.pay-error.visible { display:flex; }
.secure-note { text-align:center;font-size:12px;color:#9ca3af;margin-top:12px;display:flex;align-items:center;justify-content:center;gap:6px; }
.spinner { width:18px;height:18px;border:2.5px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none; }
@keyframes spin { to{transform:rotate(360deg)} }

/* Login prompt */
.login-prompt { background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; }
.login-prompt-text { font-size:14px;color:#92400e;font-weight:500; }
.login-prompt-btns { display:flex;gap:10px; }
.lp-btn-sign { padding:9px 18px;border-radius:8px;background:#1a56db;color:#fff;font-size:13px;font-weight:700;text-decoration:none; }
.lp-btn-reg  { padding:9px 18px;border-radius:8px;border:1.5px solid #1a56db;color:#1a56db;font-size:13px;font-weight:700;text-decoration:none; }

.state-box { text-align:center;padding:48px 20px;color:#9ca3af; }
.state-box i { font-size:36px;margin-bottom:14px;display:block; }
.info-strip { background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px 16px;font-size:13px;color:#15803d;display:flex;align-items:flex-start;gap:10px;margin-bottom:20px; }

@media(max-width:860px) {
    .steps-grid    { grid-template-columns:1fr 1fr; }
    .features-grid { grid-template-columns:1fr 1fr; }
    .location-grid { grid-template-columns:1fr; }
    .step-arrow    { display:none; }
    .search-bar    { gap:10px; }
    .sf            { min-width:140px; flex:1 1 45%; }
    .search-btn    { flex:1 1 100%; justify-content:center; }
}
@media(max-width:600px) {
    .hero { padding:48px 16px 80px; }
    .hero-title { font-size:28px; }
    .search-bar { flex-direction:column;padding:16px; }
    .page-wrap { padding:0 16px; }
    .steps-grid { grid-template-columns:1fr; }
    .features-grid { grid-template-columns:1fr; }
    .form-grid { grid-template-columns:1fr; }
    .form-full { grid-column:span 1; }
}

/* ── Scroll reveal animations ── */
.reveal { opacity:0;transform:translateY(30px);transition:opacity .7s ease,transform .7s ease; }
.reveal.visible { opacity:1;transform:translateY(0); }
.reveal-stagger > * { opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease; }
.reveal-stagger.visible > * { opacity:1;transform:translateY(0); }
.reveal-stagger.visible > *:nth-child(1){transition-delay:.05s;}
.reveal-stagger.visible > *:nth-child(2){transition-delay:.15s;}
.reveal-stagger.visible > *:nth-child(3){transition-delay:.25s;}
.reveal-stagger.visible > *:nth-child(4){transition-delay:.35s;}
.reveal-stagger.visible > *:nth-child(5){transition-delay:.45s;}
.reveal-stagger.visible > *:nth-child(6){transition-delay:.55s;}
@media (prefers-reduced-motion: reduce){ .reveal,.reveal-stagger > *{opacity:1;transform:none;transition:none;} }
</style>
@endpush

@section('content')

{{-- ── Hero ── --}}
<div class="hero">
    <div class="hero-title">Your Perfect Stay<br><span>Awaits You</span></div>
    <div class="hero-sub">
        {{ $hotel['address'] }}{{ $hotel['city'] ? ', ' . $hotel['city'] : '' }}
        &nbsp;·&nbsp; Check-in from {{ $checkInTime }} · Check-out by {{ $checkOutTime }}
    </div>

    <div class="search-bar">
        <div class="sf">
            <label>Check-in Date</label>
            <input type="date" id="check_in" class="sf-input"
                   min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
        </div>
        <div class="sf">
            <label>Check-out Date</label>
            <input type="date" id="check_out" class="sf-input"
                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                   value="{{ date('Y-m-d', strtotime('+1 day')) }}">
        </div>
        <button class="search-btn" onclick="filterRooms()">
            <i class="fas fa-search"></i> Search
        </button>
    </div>
</div>

<div class="page-wrap">

    {{-- ── How it Works ── --}}
    <div class="steps-section reveal">
        <div class="section-label">Simple & Fast</div>
        <div class="section-title">How to Book in 4 Easy Steps</div>
        <div class="section-sub">Reserve your room in minutes and pay only the deposit online.</div>

        <div class="steps-grid reveal-stagger">
            <div class="step-card">
                <div class="step-num">1</div>
                <i class="fas fa-user-plus step-icon"></i>
                <div class="step-title">Create Account</div>
                <div class="step-desc">Sign up for free to manage your bookings and view your history anytime.</div>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <i class="fas fa-calendar-alt step-icon"></i>
                <div class="step-title">Pick Your Dates</div>
                <div class="step-desc">Choose your check-in and check-out dates to see available rooms.</div>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <i class="fas fa-door-open step-icon"></i>
                <div class="step-title">Select a Room</div>
                <div class="step-desc">Browse room options with photos, amenities and nightly rates.</div>
            </div>
            <div class="step-card">
                <div class="step-num">4</div>
                <i class="fas fa-credit-card step-icon"></i>
                <div class="step-title">Pay Deposit</div>
                <div class="step-desc">Secure your booking with a {{ number_format($depositRate, 0) }}% deposit. Pay the rest at check-in.</div>
            </div>
        </div>
    </div>

    {{-- ── Rooms ── --}}
    <div class="rooms-section">
        <div class="section-label">Availability</div>
        <div class="section-title" id="rooms-heading">All Available Rooms</div>
        <div class="section-sub" id="rooms-sub">Browse our rooms — select dates to filter availability</div>

        <div id="rooms-loading" class="state-box" style="display:none;">
            <i class="fas fa-spinner fa-spin"></i><p>Checking availability…</p>
        </div>
        <div id="rooms-empty" class="state-box" style="display:none;">
            <i class="fas fa-door-closed"></i>
            <p>No rooms available for these dates. Please try different dates.</p>
        </div>
        <div class="rooms-grid" id="rooms-grid"></div>
    </div>

    {{-- ── Booking form (shown after room selected) ── --}}
    <div id="booking-section" style="display:none;">
        <div class="booking-panel">
            <div class="panel-hd"><i class="fas fa-calendar-check"></i> Complete Your Booking</div>

            @auth('customer')
            <div class="info-strip">
                <i class="fas fa-shield-alt" style="flex-shrink:0;margin-top:1px;"></i>
                <span>A <strong>{{ number_format($depositRate,0) }}% deposit</strong> is charged now to secure your room. Balance is due at check-in.</span>
            </div>
            @else
            <div class="login-prompt">
                <div class="login-prompt-text">
                    <i class="fas fa-lock" style="margin-right:6px;"></i>
                    You need to sign in or create an account to complete your booking.
                </div>
                <div class="login-prompt-btns">
                    <a href="{{ route('guest.login') }}" class="lp-btn-sign">Sign In</a>
                    <a href="{{ route('guest.register') }}" class="lp-btn-reg">Register</a>
                </div>
            </div>
            @endauth

            <div id="booking-form-wrap" style="{{ Auth::guard('customer')->check() ? '' : 'display:none;' }}">
                <div class="booking-summary" id="booking-summary"></div>

                @auth('customer')
                <form action="#" id="checkout-prefill" style="display:none;">
                    <input type="hidden" id="pf-name"  value="{{ Auth::guard('customer')->user()->name }}">
                    <input type="hidden" id="pf-email" value="{{ Auth::guard('customer')->user()->email }}">
                    <input type="hidden" id="pf-phone" value="{{ Auth::guard('customer')->user()->phone }}">
                </form>
                @endauth

                <div class="form-grid" style="margin-bottom:20px;">
                    <div>
                        <label class="f-label">Full Name <span class="req">*</span></label>
                        <input type="text" id="g-name" class="f-input"
                               value="{{ Auth::guard('customer')->user()?->name ?? '' }}">
                    </div>
                    <div>
                        <label class="f-label">Phone <span class="req">*</span></label>
                        <input type="tel" id="g-phone" class="f-input"
                               value="{{ Auth::guard('customer')->user()?->phone ?? '' }}">
                    </div>
                    <div>
                        <label class="f-label">Email <span class="req">*</span></label>
                        <input type="email" id="g-email" class="f-input"
                               value="{{ Auth::guard('customer')->user()?->email ?? '' }}">
                        <p class="f-hint">Confirmation sent here</p>
                    </div>
                    <div>
                        <label class="f-label">Nationality</label>
                        <input type="text" id="g-nationality" class="f-input"
                               value="{{ Auth::guard('customer')->user()?->nationality ?? '' }}"
                               placeholder="e.g. Cambodian">
                    </div>
                    <div class="form-full">
                        <label class="f-label">Address</label>
                        <input type="text" id="g-address" class="f-input"
                               value="{{ Auth::guard('customer')->user()?->address ?? '' }}"
                               placeholder="Street, city, country">
                    </div>
                    <div class="form-full">
                        <label class="f-label">Special Requests</label>
                        <textarea id="g-requests" class="f-textarea" placeholder="Early check-in, extra pillows, quiet room…"></textarea>
                    </div>
                </div>

                <div>
                    <label class="f-label">Card Details <span class="req">*</span></label>
                    <div class="stripe-wrap" id="card-wrap"><div id="card-element"></div></div>
                    <p class="f-hint" style="margin-top:6px;">Your card will be charged the deposit amount shown above.</p>
                </div>

                <div class="pay-error" id="pay-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="pay-error-msg"></span>
                </div>

                <button class="btn-pay" id="pay-btn" onclick="submitBooking()" disabled>
                    <div class="spinner" id="pay-spinner"></div>
                    <i class="fas fa-lock" id="pay-icon"></i>
                    <span id="pay-label">Select a room to continue</span>
                </button>

                <div class="secure-note">
                    <i class="fas fa-lock"></i> Secured by Stripe · SSL encrypted
                </div>
            </div>
        </div>
    </div>

</div>{{-- /.page-wrap --}}

{{-- ── Hotel Features ── --}}
<div class="features-section">
    <div class="page-wrap">
        <div class="section-label">Why Choose Us</div>
        <div class="section-title">Hotel Features</div>
        <div class="section-sub" style="margin-bottom:36px;">Everything you need for a comfortable stay</div>
        <div class="features-grid reveal-stagger">
            <div class="feat-card">
                <div class="feat-icon"><i class="fas fa-wifi"></i></div>
                <div class="feat-title">Free High-Speed WiFi</div>
                <div class="feat-desc">Stay connected with complimentary high-speed internet throughout the hotel.</div>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="fas fa-concierge-bell"></i></div>
                <div class="feat-title">24/7 Room Service</div>
                <div class="feat-desc">Our team is available around the clock to attend to your every need.</div>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="fas fa-parking"></i></div>
                <div class="feat-title">Free Parking</div>
                <div class="feat-desc">Complimentary secure parking available for all hotel guests.</div>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="fas fa-utensils"></i></div>
                <div class="feat-title">Restaurant & Bar</div>
                <div class="feat-desc">Enjoy local and international cuisine at our in-house restaurant.</div>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="feat-title">24/7 Security</div>
                <div class="feat-desc">Round-the-clock security ensures the safety of all our guests.</div>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="fas fa-hand-sparkles"></i></div>
                <div class="feat-title">Daily Housekeeping</div>
                <div class="feat-desc">Rooms serviced daily to keep your stay fresh and comfortable.</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Location ── --}}
<div class="location-section">
    <div class="page-wrap">
        <div class="location-grid reveal">
            <div>
                <div class="section-label">Find Us</div>
                <div class="section-title" style="text-align:left;">Our Location</div>
                <p style="font-size:14px;color:#6b7280;margin:12px 0 32px;line-height:1.7;">
                    Conveniently located in the heart of the city, we're close to major attractions, shopping centers, and dining options.
                </p>
                @if($hotel['address'])
                <div class="loc-info-row">
                    <div class="loc-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="loc-title">Address</div>
                        <div class="loc-val">{{ $hotel['address'] }}{{ $hotel['city'] ? ', ' . $hotel['city'] : '' }}</div>
                    </div>
                </div>
                @endif
                @if($hotel['phone'])
                <div class="loc-info-row">
                    <div class="loc-icon"><i class="fas fa-phone"></i></div>
                    <div>
                        <div class="loc-title">Phone</div>
                        <div class="loc-val">{{ $hotel['phone'] }}</div>
                    </div>
                </div>
                @endif
                @if($hotel['email'])
                <div class="loc-info-row">
                    <div class="loc-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <div class="loc-title">Email</div>
                        <div class="loc-val">{{ $hotel['email'] }}</div>
                    </div>
                </div>
                @endif
                <div class="loc-info-row">
                    <div class="loc-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="loc-title">Check-in / Check-out</div>
                        <div class="loc-val">Check-in from {{ $checkInTime }} · Check-out by {{ $checkOutTime }}</div>
                    </div>
                </div>
            </div>
            <div class="loc-map">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d62603.10314391398!2d104.89188!3d11.5564!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310951e669892259%3A0x6bc4d1eaa6eb1877!2sPhnom+Penh%2C+Cambodia!5e0!3m2!1sen!2skh!4v1234567890"
                    allowfullscreen="" loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>

{{-- ── Room Detail Modal ── --}}
<div class="modal-backdrop" id="room-modal" onclick="closeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header-wrap">
            <div class="modal-img" id="modal-img-wrap"></div>
            <button class="modal-close" onclick="closeModalBtn()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="modal-type"  id="modal-type"></div>
            <div class="modal-title" id="modal-title"></div>
            <div class="modal-floor" id="modal-floor"></div>
            <div class="modal-desc"  id="modal-desc"></div>
            <div class="modal-amenities" id="modal-amenities"></div>
            <div class="modal-price-row">
                <div>
                    <div style="font-size:12px;color:#9ca3af;margin-bottom:3px;">Per night</div>
                    <div class="modal-price" id="modal-price"></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:12px;color:#9ca3af;margin-bottom:3px;">Deposit ({{ number_format($depositRate,0) }}%)</div>
                    <div style="font-size:16px;font-weight:700;color:#f59e0b;" id="modal-deposit"></div>
                </div>
            </div>
            <button class="btn-book-modal" id="modal-book-btn">
                <i class="fas fa-calendar-check" style="margin-right:8px;"></i> Book This Room
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const STRIPE_KEY   = '{{ $stripePublicKey }}';
const DEPOSIT_RATE = {{ $depositRate }};
const SYMBOL       = '{{ $hotel['symbol'] }}';
const CSRF         = document.querySelector('meta[name="csrf-token"]').content;
const IS_LOGGED_IN = {{ Auth::guard('customer')->check() ? 'true' : 'false' }};

var stripe, cardElement;
var allRooms      = [];
var selectedRoom  = null;
var currentDates  = {
    checkIn:  document.getElementById('check_in').value,
    checkOut: document.getElementById('check_out').value,
    nights:   1
};

// ── Init Stripe ──────────────────────────────────────────────────────────────
if (STRIPE_KEY && IS_LOGGED_IN) {
    stripe = Stripe(STRIPE_KEY);
    var elements = stripe.elements({
        fonts: [{ cssSrc: 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans&display=swap' }]
    });
    cardElement = elements.create('card', {
        style: {
            base: { fontFamily:'Plus Jakarta Sans,sans-serif', fontSize:'14px', color:'#111827', '::placeholder':{ color:'#9ca3af' } },
            invalid: { color:'#ef4444' }
        },
        hidePostalCode: true,
    });
    cardElement.mount('#card-element');
    cardElement.on('focus', () => document.getElementById('card-wrap').classList.add('focused'));
    cardElement.on('blur',  () => document.getElementById('card-wrap').classList.remove('focused'));
}

// ── Load rooms on page load ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadRooms);

document.getElementById('check_in').addEventListener('change', function() {
    var cout = document.getElementById('check_out');
    if (this.value >= cout.value) {
        var d = new Date(this.value); d.setDate(d.getDate()+1);
        cout.value = d.toISOString().split('T')[0];
    }
    cout.min = new Date(new Date(this.value).getTime()+86400000).toISOString().split('T')[0];
});
document.getElementById('check_out').addEventListener('change', function() {});

function filterRooms() {
    loadRooms();
    setTimeout(function() {
        var roomsSection = document.querySelector('.rooms-section');
        if (roomsSection) roomsSection.scrollIntoView({ behavior:'smooth', block:'start' });
    }, 150);
}

function loadRooms() {
    var cin  = document.getElementById('check_in').value;
    var cout = document.getElementById('check_out').value;
    currentDates.checkIn  = cin;
    currentDates.checkOut = cout;
    currentDates.nights   = Math.max(1, Math.round((new Date(cout)-new Date(cin))/86400000));

    document.getElementById('rooms-loading').style.display = 'block';
    document.getElementById('rooms-empty').style.display   = 'none';
    document.getElementById('rooms-grid').innerHTML        = '';
    selectedRoom = null;
    document.getElementById('booking-section').style.display = 'none';

    fetch('/book/rooms?check_in='+cin+'&check_out='+cout, {
        headers:{ 'Accept':'application/json','X-CSRF-TOKEN':CSRF }
    })
    .then(r => r.json())
    .then(rooms => {
        document.getElementById('rooms-loading').style.display = 'none';
        allRooms = rooms;

        if (!rooms.length) {
            document.getElementById('rooms-empty').style.display = 'block';
            document.getElementById('rooms-heading').textContent = 'No Rooms Available';
            document.getElementById('rooms-sub').textContent     = 'Try different dates.';
            return;
        }

        document.getElementById('rooms-heading').textContent = rooms.length + ' Room' + (rooms.length>1?'s':'') + ' Available';
        document.getElementById('rooms-sub').textContent     = formatDate(cin)+' → '+formatDate(cout)+' · '+currentDates.nights+' night'+(currentDates.nights>1?'s':'');

        renderRooms(rooms);
    })
    .catch(() => {
        document.getElementById('rooms-loading').style.display = 'none';
        document.getElementById('rooms-empty').style.display   = 'block';
    });
}

function renderRooms(rooms) {
    var grid = document.getElementById('rooms-grid');
    grid.innerHTML = '';
    rooms.forEach(room => {
        var deposit = calcDeposit(room.base_price, currentDates.nights);
        var card = document.createElement('div');
        card.className = 'room-card';
        card.innerHTML = `
            <div class="room-img">
                ${room.image ? `<img src="${room.image}" alt="Room ${room.room_number}">` : '<i class="fas fa-bed"></i>'}
            </div>
            <div class="room-body">
                <div class="room-type">${room.type||'Standard'}</div>
                <div class="room-num">Room ${room.room_number}</div>
                <div class="room-floor">Floor ${room.floor}</div>
                <div class="room-tags">
                    <span class="room-tag"><i class="fas fa-user" style="font-size:10px;"></i> ${room.max_people} guest${room.max_people>1?'s':''}</span>
                    ${room.amenities.slice(0,3).map(a=>`<span class="room-tag">${a}</span>`).join('')}
                    ${room.amenities.length>3?`<span class="room-tag">+${room.amenities.length-3} more</span>`:''}
                </div>
                <div class="room-foot">
                    <div>
                        <div class="room-price">${SYMBOL}${room.base_price.toFixed(0)}</div>
                        <div class="room-price-sub">per night · deposit ${SYMBOL}${deposit.toFixed(2)}</div>
                    </div>
                    <button class="book-btn" onclick="event.stopPropagation();selectRoom(${JSON.stringify(room).split('"').join("'")})">Book</button>
                </div>
            </div>`;
        card.addEventListener('click', () => openModal(room));
        grid.appendChild(card);
    });
}

// ── Modal ────────────────────────────────────────────────────────────────────
function openModal(room) {
    var nights  = currentDates.nights;
    var deposit = calcDeposit(room.base_price, nights);

    var imgWrap = document.getElementById('modal-img-wrap');
    imgWrap.innerHTML = room.image
        ? `<img src="${room.image}" style="width:100%;height:100%;object-fit:cover;border-radius:18px 18px 0 0;">`
        : '<i class="fas fa-bed"></i>';

    document.getElementById('modal-type').textContent    = room.type || 'Standard';
    document.getElementById('modal-title').textContent   = 'Room ' + room.room_number;
    document.getElementById('modal-floor').textContent   = 'Floor ' + room.floor + ' · Max ' + room.max_people + ' guests';
    document.getElementById('modal-desc').textContent    = room.description || '';
    document.getElementById('modal-price').textContent   = SYMBOL + room.base_price.toFixed(0) + ' / night';
    document.getElementById('modal-deposit').textContent = SYMBOL + deposit.toFixed(2) + ' deposit';

    var amenWrap = document.getElementById('modal-amenities');
    amenWrap.innerHTML = room.amenities.map(a => `<span class="modal-tag">${a}</span>`).join('');

    document.getElementById('modal-book-btn').onclick = () => { closeModalBtn(); selectRoom(room); };
    document.getElementById('room-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(e) {
    if (e.target === document.getElementById('room-modal')) closeModalBtn();
}
function closeModalBtn() {
    document.getElementById('room-modal').classList.remove('open');
    document.body.style.overflow = '';
}

// ── Select room for booking ──────────────────────────────────────────────────
function selectRoom(room) {
    selectedRoom = room;
    var nights  = currentDates.nights;
    var total   = room.base_price * nights;
    var deposit = calcDeposit(room.base_price, nights);
    var balance = Math.round((total - deposit)*100)/100;

    document.getElementById('booking-summary').innerHTML = `
        <div class="bs-title">Booking Summary</div>
        <div class="bs-row"><span class="bs-lbl">Room</span><span class="bs-val">Room ${room.room_number} · ${room.type}</span></div>
        <div class="bs-row"><span class="bs-lbl">Check-in</span><span class="bs-val">${formatDate(currentDates.checkIn)}</span></div>
        <div class="bs-row"><span class="bs-lbl">Check-out</span><span class="bs-val">${formatDate(currentDates.checkOut)}</span></div>
        <div class="bs-row"><span class="bs-lbl">Duration</span><span class="bs-val">${nights} night${nights>1?'s':''}</span></div>
        <div class="bs-row"><span class="bs-lbl">Room Total</span><span class="bs-val">${SYMBOL}${total.toFixed(2)}</span></div>
        <div class="bs-row"><span class="bs-lbl">Balance at check-in</span><span class="bs-val">${SYMBOL}${balance.toFixed(2)}</span></div>
        <div class="bs-row" style="padding-top:10px;margin-top:4px;border-top:2px solid #dbeafe;border-bottom:none;">
            <span class="bs-lbl" style="font-weight:700;color:#1a56db;">Deposit Due Now (${DEPOSIT_RATE}%)</span>
            <span class="bs-deposit">${SYMBOL}${deposit.toFixed(2)}</span>
        </div>`;

    if (IS_LOGGED_IN) {
        var btn = document.getElementById('pay-btn');
        btn.disabled = false;
        document.getElementById('pay-label').textContent = `Pay ${SYMBOL}${deposit.toFixed(2)} Deposit & Confirm`;
    }

    document.getElementById('booking-section').style.display = 'block';
    setTimeout(() => document.getElementById('booking-section').scrollIntoView({ behavior:'smooth' }), 100);
}

// ── Submit payment ────────────────────────────────────────────────────────────
async function submitBooking() {
    if (!selectedRoom || !IS_LOGGED_IN) return;

    var name  = document.getElementById('g-name').value.trim();
    var email = document.getElementById('g-email').value.trim();
    var phone = document.getElementById('g-phone').value.trim();

    if (!name || !email || !phone) { showError('Please fill in your name, email, and phone.'); return; }
    if (!/^\S+@\S+\.\S+$/.test(email)) { showError('Please enter a valid email address.'); return; }

    setLoading(true); hideError();

    var res = await fetch('/book/intent', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({
            room_id:   selectedRoom.id,
            check_in:  currentDates.checkIn,
            check_out: currentDates.checkOut,
            name, email, phone,
            nationality: document.getElementById('g-nationality').value.trim(),
            address:     document.getElementById('g-address').value.trim(),
            requests:    document.getElementById('g-requests').value.trim(),
        })
    });

    var intentData = await res.json();
    if (!res.ok) { showError(intentData.error || 'Something went wrong.'); setLoading(false); return; }

    var { error, paymentIntent } = await stripe.confirmCardPayment(intentData.client_secret, {
        payment_method: { card: cardElement, billing_details:{ name, email } }
    });

    if (error) { showError(error.message); setLoading(false); return; }
    window.location.href = '/book/complete?payment_intent=' + paymentIntent.id;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function calcDeposit(price, nights) {
    return Math.round(price * nights * DEPOSIT_RATE / 100 * 100) / 100;
}
function setLoading(on) {
    var btn = document.getElementById('pay-btn');
    btn.disabled = on;
    document.getElementById('pay-spinner').style.display = on ? 'block' : 'none';
    document.getElementById('pay-icon').style.display    = on ? 'none'  : 'inline';
    document.getElementById('pay-label').textContent     = on ? 'Processing…' : 'Pay Deposit & Confirm';
}
function showError(msg) {
    var el = document.getElementById('pay-error');
    document.getElementById('pay-error-msg').textContent = msg;
    el.classList.add('visible');
}
function hideError() { document.getElementById('pay-error').classList.remove('visible'); }
function formatDate(str) {
    return new Date(str+'T00:00:00').toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric',year:'numeric'});
}

// ── Scroll reveal observer ──
document.addEventListener('DOMContentLoaded', function() {
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    document.querySelectorAll('.reveal, .reveal-stagger').forEach(function(el){ observer.observe(el); });
});
</script>
@endpush