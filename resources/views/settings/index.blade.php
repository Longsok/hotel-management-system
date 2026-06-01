@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@push('styles')
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* Sticky side nav */
    .settings-nav {
        position: sticky;
        top: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }
    .settings-nav-hdr {
        padding: 14px 18px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: #9ca3af;
        border-bottom: 1px solid #f3f4f6;
    }
    .settings-nav a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 18px;
        font-size: 13.5px;
        font-weight: 500;
        color: #374151;
        text-decoration: none;
        border-left: 3px solid transparent;
        transition: all .15s;
    }
    .settings-nav a:hover { background: #f9fafb; color: #111827; }
    .settings-nav a.active {
        background: #eff6ff;
        color: #1a56db;
        font-weight: 600;
        border-left-color: #1a56db;
    }
    .settings-nav a i { width: 16px; text-align: center; font-size: 13px; color: #9ca3af; }
    .settings-nav a.active i { color: #1a56db; }

    /* Sections */
    .settings-section {
        scroll-margin-top: 20px;
        margin-bottom: 24px;
    }
    .section-hdr {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 24px;
        border-bottom: 1px solid #f3f4f6;
    }
    .section-hdr-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .section-hdr-title { font-size: 15px; font-weight: 700; color: #111827; }
    .section-hdr-sub   { font-size: 12px; color: #9ca3af; margin-top: 1px; }

    .section-body { padding: 24px; }
    .fields-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .field-full   { grid-column: span 2; }

    .field-label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .field-label .req { color: #ef4444; margin-left: 2px; }
    .field-hint { font-size: 11.5px; color: #9ca3af; margin-top: 4px; }

    .field-input, .field-select, .field-textarea {
        width: 100%;
        padding: 10px 13px;
        border: 1.5px solid #e5e7eb;
        border-radius: 9px;
        font-family: inherit;
        font-size: 13.5px;
        color: #111827;
        background: #f9fafb;
        outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .field-input:focus, .field-select:focus, .field-textarea:focus {
        border-color: #1a56db;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(26,86,219,.08);
    }
    .field-input.is-invalid, .field-select.is-invalid { border-color: #ef4444; background: #fff5f5; }
    .err-msg { font-size: 11.5px; color: #ef4444; margin-top: 4px; display: block; }
    .field-textarea { resize: vertical; min-height: 80px; }

    /* Input with prefix/suffix */
    .input-addon-wrap { display: flex; }
    .input-addon {
        padding: 10px 13px;
        background: #f3f4f6;
        border: 1.5px solid #e5e7eb;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        white-space: nowrap;
    }
    .input-addon-left  { border-right: none; border-radius: 9px 0 0 9px; }
    .input-addon-right { border-left: none;  border-radius: 0 9px 9px 0; }
    .input-addon-wrap .field-input {
        border-radius: 0;
        flex: 1;
    }
    .input-addon-wrap .field-input:first-child { border-radius: 9px 0 0 9px; }
    .input-addon-wrap .field-input:last-child  { border-radius: 0 9px 9px 0; }

    /* Save bar */
    .save-bar {
        position: sticky;
        bottom: 0;
        background: rgba(255,255,255,.95);
        backdrop-filter: blur(8px);
        border-top: 1px solid #e5e7eb;
        padding: 14px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: 0 0 14px 14px;
        margin: 0 -1px -1px;
    }
    .btn-save {
        padding: 10px 28px;
        border-radius: 9px;
        border: none;
        background: #1a56db;
        color: #fff;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 12px rgba(26,86,219,.25);
        transition: background .2s, transform .15s;
    }
    .btn-save:hover { background: #1447b5; transform: translateY(-1px); }

    @media (max-width: 768px) {
        .settings-layout { grid-template-columns: 1fr; }
        .settings-nav    { position: static; }
        .fields-grid     { grid-template-columns: 1fr; }
        .field-full      { grid-column: span 1; }
    }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4"
         role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4"
         role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-exclamation-circle me-2"></i>
        Please fix the errors below before saving.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('settings.update') }}" method="POST">
    @csrf @method('PUT')

    <div class="settings-layout">

        {{-- ── Side nav ── --}}
        <div class="settings-nav">
            <div class="settings-nav-hdr">Sections</div>
            <a href="#hotel-info" class="active">
                <i class="fas fa-hotel"></i> Hotel Info
            </a>
            <a href="#financial">
                <i class="fas fa-dollar-sign"></i> Financial
            </a>
            <a href="#discount">
                <i class="fas fa-tag"></i> Discounts
            </a>
            <a href="#operations">
                <i class="fas fa-clock"></i> Operations
            </a>
            <a href="#invoice">
                <i class="fas fa-file-invoice"></i> Invoice
            </a>
        </div>

        {{-- ── Main content ── --}}
        <div>

            {{-- Hotel Information --}}
            <div class="card settings-section" id="hotel-info">
                <div class="section-hdr">
                    <div class="section-hdr-icon" style="background:#eff6ff;">
                        <i class="fas fa-hotel" style="color:#1a56db;"></i>
                    </div>
                    <div>
                        <div class="section-hdr-title">Hotel Information</div>
                        <div class="section-hdr-sub">Displayed in the sidebar, invoices, and printed documents</div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="fields-grid">

                        <div>
                            <label class="field-label">Hotel Name <span class="req">*</span></label>
                            <input type="text" name="hotel_name"
                                   class="field-input {{ $errors->has('hotel_name') ? 'is-invalid' : '' }}"
                                   value="{{ old('hotel_name', $settings['hotel_name']->value ?? 'HotelPro') }}">
                            @error('hotel_name') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">Shown in the sidebar and on all invoices</p>
                        </div>

                        <div>
                            <label class="field-label">Tagline</label>
                            <input type="text" name="hotel_tagline"
                                   class="field-input {{ $errors->has('hotel_tagline') ? 'is-invalid' : '' }}"
                                   value="{{ old('hotel_tagline', $settings['hotel_tagline']->value ?? 'Management System') }}">
                            @error('hotel_tagline') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">Subtitle shown below hotel name in the sidebar</p>
                        </div>

                        <div class="field-full">
                            <label class="field-label">Street Address</label>
                            <input type="text" name="hotel_address"
                                   class="field-input {{ $errors->has('hotel_address') ? 'is-invalid' : '' }}"
                                   value="{{ old('hotel_address', $settings['hotel_address']->value ?? '') }}"
                                   placeholder="123 Hotel Street, Suite 1">
                            @error('hotel_address') <span class="err-msg">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="field-label">City / State / Postal Code</label>
                            <input type="text" name="hotel_city"
                                   class="field-input {{ $errors->has('hotel_city') ? 'is-invalid' : '' }}"
                                   value="{{ old('hotel_city', $settings['hotel_city']->value ?? '') }}"
                                   placeholder="City, State 10001">
                            @error('hotel_city') <span class="err-msg">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="field-label">Phone Number</label>
                            <div class="input-addon-wrap">
                                <span class="input-addon input-addon-left">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="text" name="hotel_phone"
                                       class="field-input {{ $errors->has('hotel_phone') ? 'is-invalid' : '' }}"
                                       value="{{ old('hotel_phone', $settings['hotel_phone']->value ?? '') }}"
                                       placeholder="+1 (555) 000-0000">
                            </div>
                            @error('hotel_phone') <span class="err-msg">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="field-label">Email Address</label>
                            <div class="input-addon-wrap">
                                <span class="input-addon input-addon-left">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" name="hotel_email"
                                       class="field-input {{ $errors->has('hotel_email') ? 'is-invalid' : '' }}"
                                       value="{{ old('hotel_email', $settings['hotel_email']->value ?? '') }}"
                                       placeholder="billing@hotel.com">
                            </div>
                            @error('hotel_email') <span class="err-msg">{{ $message }}</span> @enderror
                        </div>

                        <div class="field-full">
                            <label class="field-label">Website</label>
                            <div class="input-addon-wrap">
                                <span class="input-addon input-addon-left">https://</span>
                                <input type="url" name="hotel_website"
                                       class="field-input {{ $errors->has('hotel_website') ? 'is-invalid' : '' }}"
                                       value="{{ old('hotel_website', $settings['hotel_website']->value ?? '') }}"
                                       placeholder="www.yourhotel.com">
                            </div>
                            @error('hotel_website') <span class="err-msg">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Financial --}}
            <div class="card settings-section" id="financial">
                <div class="section-hdr">
                    <div class="section-hdr-icon" style="background:#f0fdf4;">
                        <i class="fas fa-dollar-sign" style="color:#16a34a;"></i>
                    </div>
                    <div>
                        <div class="section-hdr-title">Financial Settings</div>
                        <div class="section-hdr-sub">Tax rates, currency, and deposit rules applied at checkout</div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="fields-grid">

                        <div>
                            <label class="field-label">Tax Rate <span class="req">*</span></label>
                            <div class="input-addon-wrap">
                                <input type="number" name="tax_rate" min="0" max="100" step="0.01"
                                       class="field-input {{ $errors->has('tax_rate') ? 'is-invalid' : '' }}"
                                       value="{{ old('tax_rate', $settings['tax_rate']->value ?? '10') }}">
                                <span class="input-addon input-addon-right">%</span>
                            </div>
                            @error('tax_rate') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">Applied to subtotal at checkout</p>
                        </div>

                        <div>
                            <label class="field-label">Deposit Rate <span class="req">*</span></label>
                            <div class="input-addon-wrap">
                                <input type="number" name="deposit_rate" min="0" max="100" step="1"
                                       class="field-input {{ $errors->has('deposit_rate') ? 'is-invalid' : '' }}"
                                       value="{{ old('deposit_rate', $settings['deposit_rate']->value ?? '30') }}">
                                <span class="input-addon input-addon-right">%</span>
                            </div>
                            @error('deposit_rate') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">Percentage of total collected at check-in</p>
                        </div>

                        <div>
                            <label class="field-label">Currency Code <span class="req">*</span></label>
                            <select name="currency"
                                    class="field-select {{ $errors->has('currency') ? 'is-invalid' : '' }}">
                                @php
                                    $currencies = [
                                        'USD' => 'USD – US Dollar',
                                        'EUR' => 'EUR – Euro',
                                        'GBP' => 'GBP – British Pound',
                                        'KHR' => 'KHR – Cambodian Riel',
                                        'SGD' => 'SGD – Singapore Dollar',
                                        'JPY' => 'JPY – Japanese Yen',
                                        'AUD' => 'AUD – Australian Dollar',
                                        'CHF' => 'CHF – Swiss Franc',
                                    ];
                                    $currentCurrency = old('currency', $settings['currency']->value ?? 'USD');
                                @endphp
                                @foreach($currencies as $code => $label)
                                    <option value="{{ $code }}" {{ $currentCurrency === $code ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('currency') <span class="err-msg">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="field-label">Currency Symbol <span class="req">*</span></label>
                            <input type="text" name="currency_symbol" maxlength="5"
                                   class="field-input {{ $errors->has('currency_symbol') ? 'is-invalid' : '' }}"
                                   value="{{ old('currency_symbol', $settings['currency_symbol']->value ?? '$') }}"
                                   placeholder="$">
                            @error('currency_symbol') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">Symbol used throughout the UI (e.g. $, €, £, ៛)</p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Discount Rules --}}
            <div class="card settings-section" id="discount">
                <div class="section-hdr">
                    <div class="section-hdr-icon" style="background:#fffbeb;">
                        <i class="fas fa-tag" style="color:#d97706;"></i>
                    </div>
                    <div>
                        <div class="section-hdr-title">Discount Rules</div>
                        <div class="section-hdr-sub">Control whether staff can apply discounts and how much</div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="fields-grid">

                        <div class="field-full">
                            <label class="field-label">Enable Discounts for Staff</label>
                            <div style="display:flex;align-items:center;gap:14px;padding:12px 14px;border:1.5px solid #e5e7eb;border-radius:9px;background:#f9fafb;">
                                <label style="position:relative;display:inline-block;width:44px;height:24px;cursor:pointer;flex-shrink:0;">
                                    <input type="checkbox" name="discount_enabled" value="1" id="discount_enabled_toggle"
                                           {{ old('discount_enabled', $settings['discount_enabled']->value ?? '0') == '1' ? 'checked' : '' }}
                                           onchange="toggleDiscountMax(this.checked)"
                                           style="opacity:0;width:0;height:0;">
                                    <span style="position:absolute;inset:0;border-radius:24px;background:#d1d5db;transition:.2s;"></span>
                                    <span id="toggle-knob" style="position:absolute;left:3px;top:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:.2s;transform:{{ old('discount_enabled', $settings['discount_enabled']->value ?? '0') == '1' ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                                </label>
                                <div>
                                    <div style="font-size:13.5px;font-weight:600;color:#111827;" id="toggle-label">
                                        {{ old('discount_enabled', $settings['discount_enabled']->value ?? '0') == '1' ? 'Discounts enabled for staff' : 'Discounts disabled for staff' }}
                                    </div>
                                    <div style="font-size:12px;color:#9ca3af;margin-top:1px;">
                                        Admins can always apply discounts regardless of this setting
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field-full" id="max-discount-wrap"
                             style="{{ old('discount_enabled', $settings['discount_enabled']->value ?? '0') == '1' ? '' : 'opacity:.45;pointer-events:none;' }}">
                            <label class="field-label">Maximum Staff Discount <span class="req">*</span></label>
                            <div class="input-addon-wrap">
                                <input type="number" name="max_discount_rate" min="0" max="100" step="1"
                                       class="field-input {{ $errors->has('max_discount_rate') ? 'is-invalid' : '' }}"
                                       value="{{ old('max_discount_rate', $settings['max_discount_rate']->value ?? '0') }}">
                                <span class="input-addon input-addon-right">%</span>
                            </div>
                            @error('max_discount_rate') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">
                                The highest discount percentage a staff member can apply at checkout.
                                Set to <strong>100</strong> for no limit.
                                Admins always have full access.
                            </p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Operations --}}
            <div class="card settings-section" id="operations">
                <div class="section-hdr">
                    <div class="section-hdr-icon" style="background:#fff7ed;">
                        <i class="fas fa-clock" style="color:#ea580c;"></i>
                    </div>
                    <div>
                        <div class="section-hdr-title">Operations</div>
                        <div class="section-hdr-sub">Standard check-in and check-out times</div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="fields-grid">

                        <div>
                            <label class="field-label">Check-in Time <span class="req">*</span></label>
                            <input type="time" name="check_in_time"
                                   class="field-input {{ $errors->has('check_in_time') ? 'is-invalid' : '' }}"
                                   value="{{ old('check_in_time', $settings['check_in_time']->value ?? '14:00') }}">
                            @error('check_in_time') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">Guests may check in from this time</p>
                        </div>

                        <div>
                            <label class="field-label">Check-out Time <span class="req">*</span></label>
                            <input type="time" name="check_out_time"
                                   class="field-input {{ $errors->has('check_out_time') ? 'is-invalid' : '' }}"
                                   value="{{ old('check_out_time', $settings['check_out_time']->value ?? '12:00') }}">
                            @error('check_out_time') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">Guests must check out by this time</p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Invoice --}}
            <div class="card settings-section" id="invoice">
                <div class="section-hdr">
                    <div class="section-hdr-icon" style="background:#fef2f2;">
                        <i class="fas fa-file-invoice" style="color:#dc2626;"></i>
                    </div>
                    <div>
                        <div class="section-hdr-title">Invoice Settings</div>
                        <div class="section-hdr-sub">Numbering prefix and footer message on printed invoices</div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="fields-grid">

                        <div>
                            <label class="field-label">Invoice Number Prefix <span class="req">*</span></label>
                            <div class="input-addon-wrap">
                                <input type="text" name="invoice_prefix" maxlength="10"
                                       class="field-input {{ $errors->has('invoice_prefix') ? 'is-invalid' : '' }}"
                                       value="{{ old('invoice_prefix', $settings['invoice_prefix']->value ?? 'INV') }}"
                                       style="text-transform:uppercase;">
                                <span class="input-addon input-addon-right" style="font-family:monospace;">
                                    -000123
                                </span>
                            </div>
                            @error('invoice_prefix') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">e.g. INV → INV-000123</p>
                        </div>

                        <div></div>{{-- spacer --}}

                        <div class="field-full">
                            <label class="field-label">Invoice Footer Note</label>
                            <textarea name="invoice_footer_note" rows="3"
                                      class="field-textarea {{ $errors->has('invoice_footer_note') ? 'is-invalid' : '' }}"
                                      placeholder="Thank you for staying with us.">{{ old('invoice_footer_note', $settings['invoice_footer_note']->value ?? 'Thank you for staying with us.') }}</textarea>
                            @error('invoice_footer_note') <span class="err-msg">{{ $message }}</span> @enderror
                            <p class="field-hint">Printed at the bottom of every invoice</p>
                        </div>

                    </div>
                </div>

                {{-- Save bar --}}
                <div class="save-bar">
                    <span style="font-size:13px;color:#6b7280;">
                        <i class="fas fa-shield-alt me-1" style="color:#9ca3af;"></i>
                        Changes take effect immediately
                    </span>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </div>

        </div>{{-- end main content --}}
    </div>{{-- end settings-layout --}}
</form>


@push('scripts')
<script>
function toggleDiscountMax(enabled) {
    var wrap  = document.getElementById('max-discount-wrap');
    var label = document.getElementById('toggle-label');
    var knob  = document.getElementById('toggle-knob');
    var track = knob.previousElementSibling;

    if (enabled) {
        wrap.style.opacity        = '1';
        wrap.style.pointerEvents  = 'auto';
        label.textContent         = 'Discounts enabled for staff';
        track.style.background    = '#1a56db';
        knob.style.transform      = 'translateX(20px)';
    } else {
        wrap.style.opacity        = '.45';
        wrap.style.pointerEvents  = 'none';
        label.textContent         = 'Discounts disabled for staff';
        track.style.background    = '#d1d5db';
        knob.style.transform      = 'translateX(0)';
    }
}

// Init toggle appearance on page load
document.addEventListener('DOMContentLoaded', function() {
    var cb = document.getElementById('discount_enabled_toggle');
    if (cb) {
        var track = document.querySelector('#discount_enabled_toggle + span');
        if (cb.checked && track) track.style.background = '#1a56db';
    }
});
</script>
@endpush
@endsection

@push('scripts')
<script>
    // Highlight active nav item on scroll
    const sections = document.querySelectorAll('.settings-section');
    const navLinks = document.querySelectorAll('.settings-nav a');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                navLinks.forEach(link => {
                    link.classList.toggle(
                        'active',
                        link.getAttribute('href') === '#' + entry.target.id
                    );
                });
            }
        });
    }, { threshold: 0.4 });

    sections.forEach(s => observer.observe(s));
</script>
@endpush
