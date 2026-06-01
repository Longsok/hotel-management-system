@extends('layouts.app')

@section('title', 'Room ' . $room->room_number)
@section('page-title', 'Room ' . $room->room_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item active">{{ $room->room_number }}</li>
@endsection

@section('header-actions')
    <div style="display:flex;gap:8px;">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm"
               style="border:1.5px solid #e5e7eb;background:#fff;color:#374151;border-radius:8px;font-weight:600;font-size:13px;padding:6px 14px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-pen"></i> Edit Room
            </a>
        @endif
        <a href="{{ route('rooms.index') }}" class="btn btn-sm"
           style="border:1.5px solid #e5e7eb;background:#fff;color:#374151;border-radius:8px;font-weight:600;font-size:13px;padding:6px 14px;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@endsection

@push('styles')
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }
    .info-row {
        display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        margin-bottom: 14px;
    }
    .info-item { }
    .info-label {
        font-size: 11px; font-weight: 700; letter-spacing: .6px;
        text-transform: uppercase; color: #9ca3af; margin-bottom: 4px;
    }
    .info-val { font-size: 14.5px; font-weight: 600; color: #111827; }

    /* amenity tags */
    .amenity-tag {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 11px; border-radius: 7px;
        background: #f3f4f6; color: #374151;
        font-size: 12.5px; font-weight: 500; margin: 3px;
    }
    .amenity-tag i { color: #6b7280; font-size: 12px; }

    /* Status change panel */
    .status-panel {
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 12px; overflow: hidden;
    }
    .status-panel-hdr {
        padding: 14px 18px; border-bottom: 1px solid #e5e7eb;
        font-size: 13.5px; font-weight: 700; color: #111827;
        display: flex; align-items: center; gap: 8px;
    }
    .status-option {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 18px; cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background .15s;
    }
    .status-option:last-child { border-bottom: none; }
    .status-option:hover { background: #f9fafb; }
    .status-option input[type=radio] { accent-color: #1a56db; width: 15px; height: 15px; }
    .status-option .so-label { font-size: 13.5px; font-weight: 600; color: #111827; }
    .status-option .so-sub   { font-size: 11.5px; color: #9ca3af; margin-top: 1px; }

    /* booking mini-table */
    .mini-table th {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .4px; color: #9ca3af; padding: 8px 12px;
        background: #f9fafb; border: none;
    }
    .mini-table td { font-size: 13px; padding: 10px 12px; border-color: #f3f4f6; vertical-align: middle; }

    /* timeline log */
    .log-list { padding: 0; list-style: none; }
    .log-item {
        display: flex; gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
        position: relative;
    }
    .log-item:last-child { border-bottom: none; }
    .log-dot {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; flex-shrink: 0; margin-top: 2px;
    }
    .log-content { flex: 1; }
    .log-arrow {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; font-weight: 600; color: #111827;
    }
    .log-meta { font-size: 12px; color: #9ca3af; margin-top: 3px; }
    .log-note { font-size: 12px; color: #6b7280; margin-top: 4px; font-style: italic; }

    .big-status {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 16px; border-radius: 10px;
        font-size: 14px; font-weight: 700;
    }
    .big-status .bsdot { width: 9px; height: 9px; border-radius: 50%; }

    @media (max-width: 960px) {
        .detail-grid { grid-template-columns: 1fr; }
        .info-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="detail-grid">

    {{-- ── LEFT COLUMN ── --}}
    <div>

        {{-- Room overview card --}}
        <div class="card mb-4">
            {{-- Colored top strip by status --}}
            <div style="height:4px; background:
                @if($room->status==='available') #22c55e
                @elseif($room->status==='occupied') #ef4444
                @elseif($room->status==='reserved') #eab308
                @elseif($room->status==='cleaning') #f97316
                @else #8b5cf6
                @endif;
            "></div>

            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-4">
                    <div>
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
                            <span style="font-size:28px;font-weight:800;color:#111827;">Room {{ $room->room_number }}</span>
                            <span class="big-status
                                @if($room->status==='available') " style="background:#f0fdf4;color:#15803d;"
                                @elseif($room->status==='occupied') " style="background:#fef2f2;color:#dc2626;"
                                @elseif($room->status==='reserved') " style="background:#fefce8;color:#a16207;"
                                @elseif($room->status==='cleaning') " style="background:#fff7ed;color:#c2410c;"
                                @else " style="background:#f5f3ff;color:#6d28d9;"
                                @endif>
                                <span class="bsdot" style="background:currentColor;"></span>
                                {{ ucfirst($room->status) }}
                            </span>
                        </div>
                        <div style="font-size:13.5px;color:#6b7280;">
                            <i class="fas fa-building me-1"></i> Floor {{ $room->floor }} &nbsp;·&nbsp;
                            <i class="fas fa-tag me-1"></i> {{ $room->roomType->name ?? '–' }} &nbsp;·&nbsp;
                            <i class="fas fa-dollar-sign me-1"></i>${{ number_format($room->roomType->base_price ?? 0, 0) }}/night
                        </div>
                    </div>
                    <div>
                        @if($room->image)
                            <img src="{{ asset('storage/' . $room->image) }}"
                                 alt="Room {{ $room->room_number }}"
                                 style="width:80px;height:80px;object-fit:cover;border-radius:12px;border:1.5px solid #e5e7eb;">
                        @else
                            <div style="width:60px;height:60px;border-radius:14px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:28px;">
                                🛏️
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Info grid --}}
                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">Room Number</div>
                        <div class="info-val">{{ $room->room_number }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Floor</div>
                        <div class="info-val">Floor {{ $room->floor }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Room Type</div>
                        <div class="info-val">{{ $room->roomType->name ?? '–' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Base Price</div>
                        <div class="info-val" style="color:#1a56db;">${{ number_format($room->roomType->base_price ?? 0, 2) }} / night</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Max Occupancy</div>
                        <div class="info-val">{{ $room->roomType->max_people ?? '–' }} person{{ ($room->roomType->max_people ?? 1) > 1 ? 's' : '' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Last Updated</div>
                        <div class="info-val">{{ $room->updated_at->diffForHumans() }}</div>
                    </div>
                </div>

                {{-- Notes --}}
                @if($room->notes)
                    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:9px;padding:12px 14px;margin-top:8px;">
                        <div style="font-size:11px;font-weight:700;color:#a16207;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                            <i class="fas fa-sticky-note me-1"></i> Notes
                        </div>
                        <div style="font-size:13.5px;color:#374151;">{{ $room->notes }}</div>
                    </div>
                @endif

                {{-- Amenities --}}
                @if($room->amenities->isNotEmpty())
                    <div style="margin-top:20px;">
                        <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">
                            Amenities
                        </div>
                        <div>
                            @foreach($room->amenities as $am)
                                <span class="amenity-tag">
                                    <i class="{{ $am->icon ?? 'fas fa-check-circle' }}"></i>
                                    {{ $am->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Bookings --}}
        <div class="card mb-4">
            <div class="card-body p-0">
                <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:between;">
                    <span style="font-size:14px;font-weight:700;color:#111827;">
                        <i class="fas fa-calendar-check me-2" style="color:#1a56db;"></i>Recent Bookings
                    </span>
                </div>
                @if($room->bookings->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table mb-0 mini-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Guest</th>
                                    <th>Check-In</th>
                                    <th>Check-Out</th>
                                    <th>Nights</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($room->bookings as $booking)
                                    <tr>
                                        <td>
                                            <a href="{{ route('bookings.show', $booking) }}"
                                               style="font-weight:700;color:#1a56db;font-size:13px;">
                                                {{ $booking->booking_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:7px;">
                                                <div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#818cf8,#1a56db);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;">
                                                    {{ strtoupper(substr($booking->customer->name ?? 'G', 0, 1)) }}
                                                </div>
                                                <span style="font-size:13px;font-weight:500;">{{ $booking->customer->name ?? '–' }}</span>
                                            </div>
                                        </td>
                                        <td style="font-size:12.5px;color:#374151;">{{ $booking->check_in_date?->format('M d, Y') }}</td>
                                        <td style="font-size:12.5px;color:#374151;">{{ $booking->check_out_date?->format('M d, Y') }}</td>
                                        <td style="font-weight:600;text-align:center;">{{ $booking->nights }}</td>
                                        <td>
                                            <span class="status-pill status-{{ $booking->status }}" style="font-size:11px;">
                                                {{ ucfirst(str_replace('_',' ',$booking->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4" style="color:#9ca3af;font-size:13.5px;">
                        <i class="fas fa-calendar-times d-block mb-2" style="font-size:26px;opacity:.25;"></i>
                        No bookings for this room yet.
                    </div>
                @endif
            </div>
        </div>

        {{-- Status History --}}
        <div class="card">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;">
                <span style="font-size:14px;font-weight:700;color:#111827;">
                    <i class="fas fa-history me-2" style="color:#1a56db;"></i>Status History
                </span>
            </div>
            <div class="card-body" style="padding:0 18px;">
                @if($room->statusLogs->isNotEmpty())
                    <ul class="log-list">
                        @foreach($room->statusLogs as $log)
                            <li class="log-item">
                                <div class="log-dot
                                    @if($log->new_status==='available')   " style="background:#f0fdf4;color:#15803d;"
                                    @elseif($log->new_status==='occupied') " style="background:#fef2f2;color:#dc2626;"
                                    @elseif($log->new_status==='reserved') " style="background:#fefce8;color:#a16207;"
                                    @elseif($log->new_status==='cleaning') " style="background:#fff7ed;color:#c2410c;"
                                    @else " style="background:#f5f3ff;color:#6d28d9;"
                                    @endif>
                                    <i class="fas
                                        @if($log->new_status==='available')   fa-check
                                        @elseif($log->new_status==='occupied') fa-user
                                        @elseif($log->new_status==='reserved') fa-clock
                                        @elseif($log->new_status==='cleaning') fa-broom
                                        @else fa-wrench
                                        @endif
                                    "></i>
                                </div>
                                <div class="log-content">
                                    <div class="log-arrow">
                                        <span class="status-pill status-{{ $log->old_status }}" style="font-size:11px;padding:2px 8px;">
                                            {{ ucfirst($log->old_status) }}
                                        </span>
                                        <i class="fas fa-arrow-right" style="font-size:10px;color:#9ca3af;"></i>
                                        <span class="status-pill status-{{ $log->new_status }}" style="font-size:11px;padding:2px 8px;">
                                            {{ ucfirst($log->new_status) }}
                                        </span>
                                    </div>
                                    <div class="log-meta">
                                        <i class="fas fa-user-circle me-1"></i>{{ $log->changedBy->name ?? 'System' }}
                                        &nbsp;·&nbsp;
                                        <i class="far fa-clock me-1"></i>{{ $log->changed_at->format('M d, Y H:i') }}
                                    </div>
                                    @if($log->notes)
                                        <div class="log-note">"{{ $log->notes }}"</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4" style="color:#9ca3af;font-size:13.5px;">
                        <i class="fas fa-history d-block mb-2" style="font-size:26px;opacity:.25;"></i>
                        No status changes recorded.
                    </div>
                @endif
            </div>
        </div>

    </div>{{-- end left --}}

    {{-- ── RIGHT COLUMN ── --}}
    <div>

        {{-- Quick Status Change --}}
        <div class="status-panel mb-4">
            <div class="status-panel-hdr">
                <i class="fas fa-exchange-alt" style="color:#1a56db;"></i>
                Update Room Status
            </div>

            <form action="{{ route('rooms.status', $room) }}" method="POST" id="statusForm">
                @csrf
                @foreach($statuses as $st)
                    <label class="status-option">
                        <input type="radio" name="status" value="{{ $st }}"
                               {{ $room->status === $st ? 'checked' : '' }}
                               onchange="document.getElementById('statusForm').submit()">
                        <div>
                            <div class="so-label">
                                @if($st==='available')   ✅ Available
                                @elseif($st==='occupied') 🔴 Occupied
                                @elseif($st==='reserved') 🟡 Reserved
                                @elseif($st==='cleaning') 🧹 Cleaning
                                @else 🔧 Maintenance
                                @endif
                            </div>
                            <div class="so-sub">
                                @if($st==='available')   Room is ready for new bookings
                                @elseif($st==='occupied') Guest currently checked in
                                @elseif($st==='reserved') Booking confirmed, pending check-in
                                @elseif($st==='cleaning') Being cleaned after check-out
                                @else Undergoing maintenance, not bookable
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </form>
        </div>

        {{-- Quick Stats --}}
        <div class="card mb-4">
            <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;">
                <span style="font-size:13.5px;font-weight:700;color:#111827;">
                    <i class="fas fa-chart-bar me-2" style="color:#1a56db;"></i>Room Stats
                </span>
            </div>
            <div class="card-body p-0">
                @php
                    $totalBookings = $room->bookings->count();
                    $completedBookings = $room->bookings->where('status','checked_out')->count();
                    $totalRevenue = $room->bookings->where('status','checked_out')->sum('room_total');
                @endphp
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;border-bottom:1px solid #f3f4f6;">
                    <div style="padding:16px 18px;border-right:1px solid #f3f4f6;">
                        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Total Bookings</div>
                        <div style="font-size:22px;font-weight:800;color:#111827;margin-top:3px;">{{ $totalBookings }}</div>
                    </div>
                    <div style="padding:16px 18px;">
                        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Completed</div>
                        <div style="font-size:22px;font-weight:800;color:#15803d;margin-top:3px;">{{ $completedBookings }}</div>
                    </div>
                </div>
                <div style="padding:16px 18px;">
                    <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Total Revenue</div>
                    <div style="font-size:22px;font-weight:800;color:#1a56db;margin-top:3px;">${{ number_format($totalRevenue, 0) }}</div>
                </div>
            </div>
        </div>

        {{-- Admin actions --}}
        @if(auth()->user()->isAdmin())
            <div class="card">
                <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;">
                    <span style="font-size:13.5px;font-weight:700;color:#111827;">
                        <i class="fas fa-cog me-2" style="color:#6b7280;"></i>Admin Actions
                    </span>
                </div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                    <a href="{{ route('rooms.edit', $room) }}"
                       style="display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;text-decoration:none;color:#374151;font-size:13.5px;font-weight:600;transition:all .15s;"
                       onmouseover="this.style.borderColor='#93c5fd';this.style.background='#eff6ff';this.style.color='#1d4ed8';"
                       onmouseout="this.style.borderColor='#e5e7eb';this.style.background='';this.style.color='#374151';">
                        <i class="fas fa-pen" style="width:16px;color:#6b7280;"></i> Edit Room Details
                    </a>
                    <a href="{{ route('rooms.logs', $room) }}"
                       style="display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;text-decoration:none;color:#374151;font-size:13.5px;font-weight:600;transition:all .15s;"
                       onmouseover="this.style.borderColor='#93c5fd';this.style.background='#eff6ff';this.style.color='#1d4ed8';"
                       onmouseout="this.style.borderColor='#e5e7eb';this.style.background='';this.style.color='#374151';">
                        <i class="fas fa-history" style="width:16px;color:#6b7280;"></i> Full Status Log
                    </a>
                    <form action="{{ route('rooms.destroy', $room) }}" method="POST"
                          onsubmit="return confirm('Permanently delete room {{ $room->room_number }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                style="width:100%;display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid #fca5a5;border-radius:9px;background:#fff;color:#dc2626;font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .15s;"
                                onmouseover="this.style.background='#fef2f2';"
                                onmouseout="this.style.background='#fff';">
                            <i class="fas fa-trash" style="width:16px;"></i> Delete Room
                        </button>
                    </form>
                </div>
            </div>
        @endif

    </div>{{-- end right --}}
</div>

@endsection
