@extends('layouts.app')

@section('title', 'New Booking')
@section('page-title', 'New Booking')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
    <li class="breadcrumb-item active">New Booking</li>
@endsection

@section('header-actions')
    <a href="{{ route('bookings.index') }}"
       style="padding:7px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@push('styles')
<style>
    .create-layout { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }

    .section-hdr {
        font-size:13px; font-weight:700; color:#374151; letter-spacing:.3px;
        margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #e5e7eb;
        display:flex; align-items:center; gap:7px;
    }

    .f-label { display:block; font-size:12.5px; font-weight:600; color:#374151; margin-bottom:6px; }
    .f-label .req { color:#ef4444; margin-left:2px; }
    .f-hint  { font-size:11.5px; color:#9ca3af; margin-top:4px; }

    .f-input, .f-select, .f-textarea {
        width:100%; padding:10px 13px;
        border:1.5px solid #e5e7eb; border-radius:9px;
        font-family:inherit; font-size:13.5px; color:#111827;
        background:#f9fafb; outline:none;
        transition:border-color .2s, box-shadow .2s, background .2s;
    }
    .f-input:focus, .f-select:focus, .f-textarea:focus {
        border-color:#1a56db; background:#fff;
        box-shadow:0 0 0 3px rgba(26,86,219,.08);
    }
    .f-input.err, .f-select.err { border-color:#ef4444; background:#fff5f5; }
    .err-msg { font-size:11.5px; color:#ef4444; margin-top:4px; display:block; }
    .f-textarea { resize:vertical; min-height:80px; }

    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
    .form-row-single { margin-bottom:16px; }

    /* Customer select with search */
    .customer-option { display:flex; flex-direction:column; }
    .customer-option .c-name  { font-weight:600; font-size:13.5px; }
    .customer-option .c-phone { font-size:12px; color:#6b7280; }

    /* Room card picker */
    #rooms-loading { display:none; text-align:center; padding:20px; color:#9ca3af; font-size:13px; }
    #rooms-grid    { display:none; }
    #rooms-empty   { display:none; text-align:center; padding:20px; color:#9ca3af; font-size:13px; }
    #rooms-prompt  { text-align:center; padding:24px; color:#9ca3af; font-size:13px; }

    .room-card {
        border:2px solid #e5e7eb; border-radius:10px; padding:14px;
        cursor:pointer; transition:all .18s; background:#fff;
    }
    .room-card:hover   { border-color:#93c5fd; background:#f8faff; }
    .room-card.selected{ border-color:#1a56db; background:#eff6ff; box-shadow:0 0 0 3px rgba(26,86,219,.1); }
    .room-card-num  { font-size:15px; font-weight:700; color:#111827; }
    .room-card-type { font-size:12px; color:#6b7280; margin-top:2px; }
    .room-card-price{ font-size:13px; font-weight:700; color:#1a56db; margin-top:6px; }
    .room-card-floor{ font-size:11px; color:#9ca3af; }

    /* Source radio */
    .source-options { display:flex; gap:10px; flex-wrap:wrap; }
    .source-opt {
        flex:1; min-width:120px;
        border:2px solid #e5e7eb; border-radius:9px; padding:11px 14px;
        cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:9px;
        font-size:13.5px; font-weight:500; color:#374151; background:#f9fafb;
    }
    .source-opt:hover   { border-color:#93c5fd; background:#f0f9ff; }
    .source-opt.active  { border-color:#1a56db; background:#eff6ff; color:#1a56db; font-weight:600; }
    .source-opt input   { display:none; }
    .source-dot { width:14px; height:14px; border-radius:50%; border:2px solid #d1d5db; flex-shrink:0; transition:all .15s; }
    .source-opt.active .source-dot { border-color:#1a56db; background:#1a56db; }

    /* Summary card */
    .summary-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; position:sticky; top:20px; }
    .summary-hdr  { background:linear-gradient(135deg,#1a56db,#3b82f6); padding:16px 20px; }
    .summary-hdr h3 { color:#fff; font-size:14px; font-weight:700; margin:0; }
    .summary-body { padding:18px 20px; }
    .summary-row  { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13.5px; }
    .summary-row:last-child { border-bottom:none; }
    .summary-lbl  { color:#6b7280; }
    .summary-val  { font-weight:600; color:#111827; }
    .summary-empty{ text-align:center; color:#9ca3af; font-size:13px; padding:12px 0; }
    .summary-total{ display:flex; justify-content:space-between; align-items:center; padding:14px 20px; background:#f9fafb; border-top:1px solid #e5e7eb; font-size:14px; font-weight:700; color:#111827; }
    .summary-price{ color:#1a56db; font-size:18px; }

    .btn-submit {
        width:100%; padding:12px; border-radius:10px; border:none;
        background:#1a56db; color:#fff; font-family:inherit;
        font-size:14px; font-weight:700; cursor:pointer;
        display:flex; align-items:center; justify-content:center; gap:8px;
        box-shadow:0 3px 12px rgba(26,86,219,.28);
        transition:background .2s, transform .15s;
        margin-top:14px;
    }
    .btn-submit:hover { background:#1447b5; transform:translateY(-1px); }
    .btn-submit:disabled { background:#9ca3af; box-shadow:none; cursor:not-allowed; transform:none; }

    @media (max-width:900px) {
        .create-layout { grid-template-columns:1fr; }
        .summary-card  { position:static; }
    }
</style>
@endpush

@section('content')

@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13.5px;font-weight:500;display:flex;align-items:center;gap:9px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<form action="{{ route('bookings.store') }}" method="POST" id="booking-form">
@csrf

<div class="create-layout">

    {{-- ── Left: form ── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- 1. Customer --}}
        <div class="card">
            <div class="card-body p-4">
                <div class="section-hdr">
                    <i class="fas fa-user" style="color:#1a56db;"></i> Guest Information
                </div>

                <div class="form-row-single">
                    <label class="f-label">Select Customer <span class="req">*</span></label>
                    <select name="customer_id" id="customer_id"
                            class="f-select {{ $errors->has('customer_id') ? 'err' : '' }}">
                        <option value="">— Search or select a customer —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}"
                                    data-phone="{{ $c->phone }}"
                                    data-email="{{ $c->email ?? '' }}"
                                    {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} — {{ $c->phone }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')<span class="err-msg">{{ $message }}</span>@enderror
                    <p class="f-hint">
                        Customer not listed?
                        <a href="{{ route('customers.create') }}" target="_blank" style="color:#1a56db;font-weight:600;">
                            Add new customer →
                        </a>
                    </p>
                </div>

                {{-- Customer info preview --}}
                <div id="customer-preview" style="display:none;background:#f0fdf4;border:1px solid #86efac;border-radius:9px;padding:12px 14px;">
                    <div style="font-size:12px;font-weight:700;color:#15803d;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Selected Guest</div>
                    <div style="display:flex;gap:20px;flex-wrap:wrap;">
                        <div style="font-size:13.5px;font-weight:600;color:#111827;" id="prev-name"></div>
                        <div style="font-size:13px;color:#6b7280;" id="prev-phone"></div>
                        <div style="font-size:13px;color:#6b7280;" id="prev-email"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Dates --}}
        <div class="card">
            <div class="card-body p-4">
                <div class="section-hdr">
                    <i class="fas fa-calendar-alt" style="color:#1a56db;"></i> Stay Dates
                </div>

                <div class="form-row">
                    <div>
                        <label class="f-label">Check-in Date <span class="req">*</span></label>
                        <input type="date" name="check_in_date" id="check_in_date"
                               class="f-input {{ $errors->has('check_in_date') ? 'err' : '' }}"
                               value="{{ old('check_in_date', date('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}">
                        @error('check_in_date')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="f-label">Check-out Date <span class="req">*</span></label>
                        <input type="date" name="check_out_date" id="check_out_date"
                               class="f-input {{ $errors->has('check_out_date') ? 'err' : '' }}"
                               value="{{ old('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        @error('check_out_date')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div style="margin-top:-8px;">
                    <button type="button" id="search-rooms-btn"
                            style="padding:9px 18px;border-radius:8px;border:none;background:#1a56db;color:#fff;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:7px;">
                        <i class="fas fa-search"></i> Search Available Rooms
                    </button>
                </div>
            </div>
        </div>

        {{-- 3. Room picker --}}
        <div class="card">
            <div class="card-body p-4">
                <div class="section-hdr">
                    <i class="fas fa-door-open" style="color:#1a56db;"></i> Select Room
                </div>

                <input type="hidden" name="room_id" id="room_id" value="{{ old('room_id') }}">
                @error('room_id')<span class="err-msg" style="display:block;margin-bottom:10px;">{{ $message }}</span>@enderror

                <div id="rooms-prompt"><i class="fas fa-search" style="display:block;font-size:28px;opacity:.25;margin-bottom:8px;"></i>Pick dates above and click Search to see available rooms.</div>
                <div id="rooms-loading"><i class="fas fa-spinner fa-spin"></i> Finding available rooms…</div>
                <div id="rooms-empty"><i class="fas fa-door-closed" style="display:block;font-size:28px;opacity:.25;margin-bottom:8px;"></i>No rooms available for the selected dates.</div>
                <div id="rooms-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;"></div>
            </div>
        </div>

        {{-- 4. Details --}}
        <div class="card">
            <div class="card-body p-4">
                <div class="section-hdr">
                    <i class="fas fa-info-circle" style="color:#1a56db;"></i> Booking Details
                </div>

                <div class="form-row-single">
                    <label class="f-label">Booking Source</label>
                    <div class="source-options" id="source-options">
                        <label class="source-opt {{ old('booking_source','walk_in') === 'walk_in' ? 'active' : '' }}" onclick="setSource('walk_in',this)">
                            <input type="radio" name="booking_source" value="walk_in" {{ old('booking_source','walk_in') === 'walk_in' ? 'checked' : '' }}>
                            <span class="source-dot"></span>
                            <i class="fas fa-walking" style="color:#6b7280;"></i> Walk-in
                        </label>
                        <label class="source-opt {{ old('booking_source') === 'online' ? 'active' : '' }}" onclick="setSource('online',this)">
                            <input type="radio" name="booking_source" value="online" {{ old('booking_source') === 'online' ? 'checked' : '' }}>
                            <span class="source-dot"></span>
                            <i class="fas fa-globe" style="color:#6b7280;"></i> Online
                        </label>
                    </div>
                </div>

                <div class="form-row-single">
                    <label class="f-label">Special Requests</label>
                    <textarea name="special_requests" class="f-textarea"
                              placeholder="Any special requests or notes from the guest…">{{ old('special_requests') }}</textarea>
                    @error('special_requests')<span class="err-msg">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

    </div>

    {{-- ── Right: summary ── --}}
    <div>
        <div class="summary-card">
            <div class="summary-hdr">
                <h3><i class="fas fa-receipt" style="margin-right:7px;"></i>Booking Summary</h3>
            </div>
            <div class="summary-body" id="summary-body">
                <div class="summary-empty">Fill in the form to see a summary.</div>
            </div>
            <div class="summary-total" id="summary-total" style="display:none;">
                <span>Estimated Total</span>
                <span class="summary-price" id="summary-price">$0.00</span>
            </div>
            <div style="padding:0 16px 16px;">
                <button type="submit" class="btn-submit" id="submit-btn" disabled>
                    <i class="fas fa-calendar-check"></i> Create Booking
                </button>
            </div>
        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
var selectedRoom = null;

// ── Customer preview ──────────────────────────────────────────────────────────
document.getElementById('customer_id').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    var preview = document.getElementById('customer-preview');
    if (!this.value) { preview.style.display = 'none'; updateSummary(); return; }
    document.getElementById('prev-name').textContent  = opt.text.split(' — ')[0];
    document.getElementById('prev-phone').textContent = opt.getAttribute('data-phone') || '';
    document.getElementById('prev-email').textContent = opt.getAttribute('data-email') || '';
    preview.style.display = 'block';
    updateSummary();
});

// ── Date validation ───────────────────────────────────────────────────────────
document.getElementById('check_in_date').addEventListener('change', function() {
    var cout = document.getElementById('check_out_date');
    if (this.value >= cout.value) {
        var d = new Date(this.value); d.setDate(d.getDate()+1);
        cout.value = d.toISOString().split('T')[0];
    }
    cout.min = new Date(new Date(this.value).getTime()+86400000).toISOString().split('T')[0];
    clearRooms();
    updateSummary();
});
document.getElementById('check_out_date').addEventListener('change', function() {
    clearRooms(); updateSummary();
});

// ── Room search ───────────────────────────────────────────────────────────────
document.getElementById('search-rooms-btn').addEventListener('click', searchRooms);

function searchRooms() {
    var cin  = document.getElementById('check_in_date').value;
    var cout = document.getElementById('check_out_date').value;
    if (!cin || !cout) { alert('Please select both dates first.'); return; }

    document.getElementById('rooms-prompt').style.display  = 'none';
    document.getElementById('rooms-empty').style.display   = 'none';
    document.getElementById('rooms-grid').style.display    = 'none';
    document.getElementById('rooms-loading').style.display = 'block';

    selectedRoom = null;
    document.getElementById('room_id').value = '';

    fetch('/bookings/available-rooms?check_in='+cin+'&check_out='+cout, {
        headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(rooms => {
        document.getElementById('rooms-loading').style.display = 'none';
        if (!rooms.length) {
            document.getElementById('rooms-empty').style.display = 'block';
            return;
        }
        var grid = document.getElementById('rooms-grid');
        grid.innerHTML = '';
        rooms.forEach(function(room) {
            var card = document.createElement('div');
            card.className = 'room-card';
            card.dataset.id    = room.id;
            card.dataset.num   = room.room_number;
            card.dataset.type  = room.room_type ? room.room_type.name : '';
            card.dataset.price = room.room_type ? room.room_type.base_price : 0;
            card.dataset.floor = room.floor;
            card.innerHTML =
                '<div class="room-card-num">Room '+room.room_number+'</div>'+
                '<div class="room-card-floor">Floor '+room.floor+'</div>'+
                '<div class="room-card-type">'+(room.room_type ? room.room_type.name : '')+'</div>'+
                '<div class="room-card-price">$'+(room.room_type ? parseFloat(room.room_type.base_price).toFixed(0) : 0)+'/night</div>';
            card.addEventListener('click', function() { selectRoom(this); });
            grid.appendChild(card);
        });
        grid.style.display = 'grid';
    })
    .catch(function() {
        document.getElementById('rooms-loading').style.display = 'none';
        document.getElementById('rooms-empty').style.display   = 'block';
    });
}

function clearRooms() {
    selectedRoom = null;
    document.getElementById('room_id').value = '';
    document.getElementById('rooms-grid').style.display    = 'none';
    document.getElementById('rooms-loading').style.display = 'none';
    document.getElementById('rooms-empty').style.display   = 'none';
    document.getElementById('rooms-prompt').style.display  = 'block';
}

function selectRoom(card) {
    document.querySelectorAll('.room-card').forEach(function(c) { c.classList.remove('selected'); });
    card.classList.add('selected');
    selectedRoom = { id: card.dataset.id, num: card.dataset.num, type: card.dataset.type, price: parseFloat(card.dataset.price), floor: card.dataset.floor };
    document.getElementById('room_id').value = selectedRoom.id;
    updateSummary();
}

// ── Source radio ──────────────────────────────────────────────────────────────
function setSource(val, el) {
    document.querySelectorAll('.source-opt').forEach(function(o) { o.classList.remove('active'); });
    el.classList.add('active');
}

// ── Summary update ────────────────────────────────────────────────────────────
function updateSummary() {
    var custSel  = document.getElementById('customer_id');
    var cin      = document.getElementById('check_in_date').value;
    var cout     = document.getElementById('check_out_date').value;
    var custName = custSel.value ? custSel.options[custSel.selectedIndex].text.split(' — ')[0] : null;

    var nights = 0;
    if (cin && cout) {
        nights = Math.round((new Date(cout) - new Date(cin)) / 86400000);
    }

    var body  = document.getElementById('summary-body');
    var total = document.getElementById('summary-total');
    var price = document.getElementById('summary-price');
    var btn   = document.getElementById('submit-btn');

    var ready = custName && cin && cout && nights > 0 && selectedRoom;

    if (!ready) {
        body.innerHTML  = '<div class="summary-empty">Fill in the form to see a summary.</div>';
        total.style.display = 'none';
        btn.disabled = true;
        return;
    }

    var roomTotal = selectedRoom.price * nights;

    body.innerHTML =
        '<div class="summary-row"><span class="summary-lbl">Guest</span><span class="summary-val">'+custName+'</span></div>'+
        '<div class="summary-row"><span class="summary-lbl">Room</span><span class="summary-val">'+selectedRoom.num+' · '+selectedRoom.type+'</span></div>'+
        '<div class="summary-row"><span class="summary-lbl">Check-in</span><span class="summary-val">'+formatDate(cin)+'</span></div>'+
        '<div class="summary-row"><span class="summary-lbl">Check-out</span><span class="summary-val">'+formatDate(cout)+'</span></div>'+
        '<div class="summary-row"><span class="summary-lbl">Duration</span><span class="summary-val">'+nights+' night'+(nights>1?'s':'')+'</span></div>'+
        '<div class="summary-row"><span class="summary-lbl">Rate</span><span class="summary-val">$'+selectedRoom.price.toFixed(0)+'/night</span></div>';

    price.textContent   = '$'+roomTotal.toFixed(2);
    total.style.display = 'flex';
    btn.disabled        = false;
}

function formatDate(str) {
    var d = new Date(str+'T00:00:00');
    return d.toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'});
}

// Run on load for old() values
document.addEventListener('DOMContentLoaded', function() {
    var preselect = '{{ old('room_id') }}';
    if (preselect) {
        searchRooms();
    }
    updateSummary();
    // Trigger customer preview if old value
    var custSel = document.getElementById('customer_id');
    if (custSel.value) custSel.dispatchEvent(new Event('change'));
});
</script>
@endpush