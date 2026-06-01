@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
    /* ── dashboard-specific ─────────────────────────────────────────────── */
    .dash-grid-top {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    .dash-grid-bottom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* stat row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }

    /* floor section */
    .floor-section { margin-bottom: 18px; }
    .floor-label {
        font-size: 12.5px; font-weight: 700;
        color: #374151; margin-bottom: 10px;
        display: flex; align-items: center; gap: 6px;
        letter-spacing: .3px;
    }
    .floor-label::after {
        content: '';
        flex: 1; height: 1px;
        background: #e5e7eb;
    }
    .floor-rooms { display: flex; flex-wrap: wrap; gap: 8px; }
    .room-chip {
        min-width: 62px; height: 44px;
        border-radius: 9px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        font-weight: 700; font-size: 13px;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s;
        text-decoration: none;
        line-height: 1;
    }
    .room-chip .chip-sub { font-size: 9px; font-weight: 500; margin-top: 2px; opacity: .75; }
    .room-chip:hover { transform: translateY(-2px); box-shadow: 0 5px 14px rgba(0,0,0,.12); }
    .room-chip.available    { background: #dcfce7; border: 1.5px solid #86efac; color: #15803d; }
    .room-chip.occupied     { background: #fee2e2; border: 1.5px solid #fca5a5; color: #dc2626; }
    .room-chip.reserved     { background: #fef9c3; border: 1.5px solid #fde047; color: #a16207; }
    .room-chip.cleaning     { background: #ffedd5; border: 1.5px solid #fdba74; color: #c2410c; }
    .room-chip.maintenance,
    .room-chip.out_of_service { background: #ede9fe; border: 1.5px solid #c4b5fd; color: #6d28d9; }

    /* legend */
    .legend { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 14px; }
    .legend-item { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #6b7280; font-weight: 500; }
    .legend-dot { width: 10px; height: 10px; border-radius: 3px; }

    /* today overview */
    .today-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
    .today-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px;
        text-align: center;
    }
    .today-card .tc-val { font-size: 32px; font-weight: 800; color: #111827; line-height: 1; }
    .today-card .tc-lbl { font-size: 12px; color: #6b7280; margin-top: 4px; font-weight: 500; }
    .today-card .tc-link { font-size: 12px; color: #1a56db; margin-top: 8px; display: block; font-weight: 600; }

    /* mini chart area */
    .mini-chart-wrap {
        background: #f9fafb; border: 1px solid #e5e7eb;
        border-radius: 10px; padding: 14px;
        margin-top: 4px;
    }

    /* recent bookings table */
    .action-btn {
        width: 30px; height: 30px;
        border-radius: 7px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #e5e7eb;
        color: #6b7280; background: #fff;
        transition: all .15s; cursor: pointer;
        font-size: 12px; text-decoration: none;
    }
    .action-btn:hover { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }

    /* card header with action */
    .card-hdr {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    .card-hdr-title { font-size: 14px; font-weight: 700; color: #111827; }
    .card-hdr-link { font-size: 12.5px; color: #1a56db; font-weight: 600; text-decoration: none; }
    .card-hdr-link:hover { text-decoration: underline; }

    @media (max-width: 1200px) {
        .stats-row { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 900px) {
        .dash-grid-top, .dash-grid-bottom { grid-template-columns: 1fr; }
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')

{{-- ── Stat Cards Row ── --}}
<div class="stats-row">

    {{-- Available Rooms --}}
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-door-open"></i></div>
        <div>
            <div class="stat-value">{{ $roomStats['available'] ?? 0 }}</div>
            <div class="stat-label">Available Rooms</div>
            @php
                $total = array_sum($roomStats->toArray());
                $pct   = $total > 0 ? round((($roomStats['available'] ?? 0) / $total) * 100) : 0;
            @endphp
            <span class="stat-badge up"><i class="fas fa-arrow-up" style="font-size:9px;"></i> {{ $pct }}% of total</span>
        </div>
    </div>

    {{-- Occupied --}}
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-bed"></i></div>
        <div>
            <div class="stat-value">{{ $roomStats['occupied'] ?? 0 }}</div>
            <div class="stat-label">Occupied Rooms</div>
            @php $pct2 = $total > 0 ? round((($roomStats['occupied'] ?? 0) / $total) * 100) : 0; @endphp
            <span class="stat-badge down"><i class="fas fa-circle" style="font-size:6px;"></i> {{ $pct2 }}% of total</span>
        </div>
    </div>

    {{-- Reserved --}}
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-calendar-alt"></i></div>
        <div>
            <div class="stat-value">{{ $roomStats['reserved'] ?? 0 }}</div>
            <div class="stat-label">Reserved Rooms</div>
            @php $pct3 = $total > 0 ? round((($roomStats['reserved'] ?? 0) / $total) * 100) : 0; @endphp
            <span class="stat-badge" style="background:#fefce8;color:#a16207;"><i class="fas fa-circle" style="font-size:6px;"></i> {{ $pct3 }}% of total</span>
        </div>
    </div>

    {{-- Cleaning --}}
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-broom"></i></div>
        <div>
            <div class="stat-value">{{ $roomStats['cleaning'] ?? 0 }}</div>
            <div class="stat-label">Cleaning Rooms</div>
            @php $pct4 = $total > 0 ? round((($roomStats['cleaning'] ?? 0) / $total) * 100) : 0; @endphp
            <span class="stat-badge" style="background:#f5f3ff;color:#6d28d9;"><i class="fas fa-circle" style="font-size:6px;"></i> {{ $pct4 }}% of total</span>
        </div>
    </div>

    {{-- Revenue Today --}}
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div>
            <div class="stat-value">${{ number_format($revenueToday, 0) }}</div>
            <div class="stat-label">Today's Revenue</div>
            <span class="stat-badge up"><i class="fas fa-arrow-up" style="font-size:9px;"></i> Live</span>
        </div>
    </div>

</div>

{{-- ── Two-column main grid ── --}}
<div class="dash-grid-top">

    {{-- Left: Room Status (Floor Overview) --}}
    <div class="card" style="overflow:visible;">
        <div class="card-hdr">
            <span class="card-hdr-title">
                <i class="fas fa-map-marked-alt me-2" style="color:#1a56db;"></i>
                Room Status <span style="font-weight:400;color:#6b7280;font-size:13px;">(Floor Overview)</span>
            </span>
            <a href="{{ route('rooms.index') }}" class="card-hdr-link">View all →</a>
        </div>
        <div class="card-body p-3">
            @forelse($roomsByFloor as $floor => $rooms)
                <div class="floor-section">
                    <div class="floor-label">Floor {{ $floor }}</div>
                    <div class="floor-rooms">
                        @foreach($rooms as $room)
                            <a href="{{ route('rooms.show', $room) }}" class="room-chip {{ $room->status }}">
                                {{ $room->room_number }}
                                <span class="chip-sub">{{ ucfirst($room->status) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted" style="font-size:13.5px;">
                    <i class="fas fa-door-open mb-2 d-block" style="font-size:24px;opacity:.3;"></i>
                    No rooms configured yet.
                </div>
            @endforelse

            <div class="legend">
                <div class="legend-item"><div class="legend-dot" style="background:#86efac;"></div> Available</div>
                <div class="legend-item"><div class="legend-dot" style="background:#fca5a5;"></div> Occupied</div>
                <div class="legend-item"><div class="legend-dot" style="background:#fde047;"></div> Reserved</div>
                <div class="legend-item"><div class="legend-dot" style="background:#fdba74;"></div> Cleaning</div>
                <div class="legend-item"><div class="legend-dot" style="background:#c4b5fd;"></div> Out of Service</div>
            </div>
        </div>
    </div>

    {{-- Right: Today Overview --}}
    <div class="card">
        <div class="card-hdr">
            <span class="card-hdr-title">
                <i class="fas fa-calendar-day me-2" style="color:#1a56db;"></i>
                Today Overview
            </span>
            <span style="font-size:12px; color:#6b7280; font-weight:500;">
                <i class="far fa-calendar-alt me-1"></i>{{ now()->format('M d, Y') }}
            </span>
        </div>
        <div class="card-body p-3">
            <div class="today-grid">
                <div class="today-card">
                    <div class="tc-val" style="color:#1a56db;">{{ $checkInsToday }}</div>
                    <div class="tc-lbl">
                        <i class="fas fa-sign-in-alt me-1" style="color:#1a56db;"></i>Check-Ins
                    </div>
                    <a href="{{ route('check-ins.index') }}" class="tc-link">View all</a>
                </div>
                <div class="today-card">
                    <div class="tc-val" style="color:#dc2626;">{{ $checkOutsToday }}</div>
                    <div class="tc-lbl">
                        <i class="fas fa-sign-out-alt me-1" style="color:#dc2626;"></i>Check-Outs
                    </div>
                    <a href="{{ route('check-outs.index') }}" class="tc-link">View all</a>
                </div>
            </div>

            {{-- Activity mini chart --}}
            <div class="mini-chart-wrap">
                <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:10px;">Activity (Today)</div>
                <canvas id="miniChart" height="90"></canvas>
            </div>

            {{-- Month summary --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px;">
                <div style="background:#eff6ff;border-radius:9px;padding:12px 14px;">
                    <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Month Revenue</div>
                    <div style="font-size:20px;font-weight:800;color:#1d4ed8;margin-top:3px;">${{ number_format($revenueMonth, 0) }}</div>
                </div>
                <div style="background:#f0fdf4;border-radius:9px;padding:12px 14px;">
                    <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Month Bookings</div>
                    <div style="font-size:20px;font-weight:800;color:#15803d;margin-top:3px;">{{ $bookingsMonth }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Recent Bookings table ── --}}
<div class="card">
    <div class="card-hdr">
        <span class="card-hdr-title">
            <i class="fas fa-list-alt me-2" style="color:#1a56db;"></i>
            Recent Bookings
        </span>
        <a href="{{ route('bookings.index') }}" class="card-hdr-link">View all bookings →</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Nights</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBookings as $b)
                        <tr>
                            <td>
                                <span style="font-size:13px;font-weight:700;color:#111827;">{{ $b->booking_number }}</span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:9px;">
                                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#818cf8,#1a56db);display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($b->customer->name ?? 'G', 0, 1)) }}
                                    </div>
                                    <span style="font-size:13.5px;font-weight:500;">{{ $b->customer->name ?? '–' }}</span>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight:600;font-size:13.5px;">{{ $b->room->room_number ?? '–' }}</span>
                            </td>
                            <td style="color:#374151;font-size:13px;">{{ $b->check_in_date?->format('M d, Y') ?? '–' }}</td>
                            <td style="color:#374151;font-size:13px;">{{ $b->check_out_date?->format('M d, Y') ?? '–' }}</td>
                            <td style="font-weight:600;text-align:center;">{{ $b->nights }}</td>
                            <td style="font-weight:700;color:#111827;">${{ number_format($b->room_total, 0) }}</td>
                            <td>
                                <span class="status-pill status-{{ $b->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('bookings.show', $b) }}" class="action-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4" style="color:#9ca3af;font-size:13.5px;">
                                <i class="fas fa-calendar-times d-block mb-2" style="font-size:24px;opacity:.3;"></i>
                                No bookings yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const ctx = document.getElementById('miniChart');
    if (!ctx) return;

    // Real hourly activity data from the server (4-hour buckets)
    const labels    = ['12AM','4AM','8AM','12PM','4PM','8PM'];
    const checkIns  = {!! json_encode($chartCheckIns) !!};
    const checkOuts = {!! json_encode($chartCheckOuts) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Check-Ins',
                    data: checkIns,
                    borderColor: '#1a56db',
                    backgroundColor: 'rgba(26,86,219,.08)',
                    fill: true,
                    tension: .45,
                    pointRadius: 3,
                    pointBackgroundColor: '#1a56db',
                    borderWidth: 2,
                },
                {
                    label: 'Check-Outs',
                    data: checkOuts,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.06)',
                    fill: true,
                    tension: .45,
                    pointRadius: 3,
                    pointBackgroundColor: '#ef4444',
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                        padding: 14, usePointStyle: true, pointStyleWidth: 8,
                    }
                },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, color: '#9ca3af' },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, color: '#9ca3af', stepSize: 2 },
                },
            },
        },
    });
})();
</script>
@endpush
