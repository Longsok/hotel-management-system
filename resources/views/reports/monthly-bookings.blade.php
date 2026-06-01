@extends('layouts.app')

@section('title', 'Monthly Bookings Report')
@section('page-title', 'Monthly Bookings Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.bookings.monthly') }}">Reports</a></li>
    <li class="breadcrumb-item active">Monthly</li>
@endsection

@section('header-actions')
    <div style="display:flex;gap:8px;align-items:center;">
        <a href="{{ route('reports.bookings.daily') }}" class="btn btn-sm"
           style="border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;height:34px;display:flex;align-items:center;gap:6px;padding:0 14px;text-decoration:none;">
            <i class="fas fa-calendar-day"></i> Daily View
        </a>
        <a href="{{ route('reports.income', ['year' => $year]) }}" class="btn btn-sm"
           style="border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;height:34px;display:flex;align-items:center;gap:6px;padding:0 14px;text-decoration:none;">
            <i class="fas fa-chart-line"></i> Income Report
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* ── Report tabs ─────────────────────────────────────────────────── */
    .report-tabs { display:flex; gap:6px; margin-bottom:20px; }
    .report-tab {
        height:36px; padding:0 18px; border-radius:9px; border:1.5px solid #e5e7eb;
        background:#fff; color:#6b7280; font-family:inherit; font-size:13px; font-weight:600;
        cursor:pointer; display:flex; align-items:center; gap:7px;
        text-decoration:none; transition:all .15s;
    }
    .report-tab:hover { border-color:#93c5fd; color:#1d4ed8; background:#eff6ff; }
    .report-tab.active { background:#1a56db; border-color:#1a56db; color:#fff; }

    /* ── Month picker ────────────────────────────────────────────────── */
    .month-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .month-select {
        height:36px; padding:0 12px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:13px;
        color:#374151; background:#fff; outline:none; cursor:pointer;
        transition:border-color .2s;
    }
    .month-select:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .btn-go {
        height:36px; padding:0 18px; border-radius:8px;
        border:none; background:#1a56db; color:#fff;
        font-family:inherit; font-size:13px; font-weight:600;
        cursor:pointer; display:flex; align-items:center; gap:6px;
    }
    .btn-go:hover { background:#1447b5; }
    .nav-arrow {
        width:36px; height:36px; border-radius:8px;
        border:1.5px solid #e5e7eb; background:#fff;
        color:#6b7280; display:flex; align-items:center; justify-content:center;
        text-decoration:none; font-size:13px; transition:all .15s;
    }
    .nav-arrow:hover { border-color:#93c5fd; color:#1d4ed8; background:#eff6ff; }

    /* ── Summary grid ────────────────────────────────────────────────── */
    .summary-grid {
        display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px;
    }
    .sum-card {
        background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px;
    }
    .sum-card .sum-icon {
        width:40px; height:40px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        font-size:16px; margin-bottom:12px;
    }
    .sum-icon.blue   { background:#eff6ff; color:#1a56db; }
    .sum-icon.green  { background:#f0fdf4; color:#16a34a; }
    .sum-icon.amber  { background:#fffbeb; color:#d97706; }
    .sum-icon.purple { background:#f5f3ff; color:#7c3aed; }
    .sum-icon.red    { background:#fef2f2; color:#dc2626; }
    .sum-icon.teal   { background:#f0fdfa; color:#0d9488; }
    .sum-card .sum-val { font-size:26px; font-weight:800; color:#111827; line-height:1; }
    .sum-card .sum-lbl { font-size:12px; color:#6b7280; font-weight:500; margin-top:4px; }
    .sum-card .sum-sub { font-size:11.5px; margin-top:6px; font-weight:600; }

    /* ── Charts layout ───────────────────────────────────────────────── */
    .charts-row { display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px; }
    .chart-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; }
    .card-hdr {
        display:flex; align-items:center; justify-content:space-between;
        padding:14px 20px; border-bottom:1px solid #e5e7eb;
    }
    .card-hdr-title { font-size:14px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
    .card-body-pad { padding:20px; }

    /* ── Bottom row ──────────────────────────────────────────────────── */
    .bottom-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }

    /* ── Room type table ─────────────────────────────────────────────── */
    .rt-table { width:100%; border-collapse:collapse; }
    .rt-table th {
        padding:10px 16px; font-size:11px; font-weight:700;
        text-transform:uppercase; letter-spacing:.5px;
        color:#9ca3af; border-bottom:1px solid #f3f4f6; background:#fafafa;
    }
    .rt-table td { padding:11px 16px; font-size:13.5px; color:#374151; border-bottom:1px solid #f3f4f6; }
    .rt-table tr:last-child td { border-bottom:none; }
    .rt-table tbody tr:hover { background:#fafbff; }

    /* ── Payment type cards ──────────────────────────────────────────── */
    .pay-item {
        display:flex; align-items:center; justify-content:space-between;
        padding:11px 0; border-bottom:1px solid #f3f4f6;
    }
    .pay-item:last-child { border-bottom:none; }
    .pay-dot { width:10px; height:10px; border-radius:3px; margin-right:10px; flex-shrink:0; }

    /* ── Status badge ────────────────────────────────────────────────── */
    .status-badge {
        display:inline-flex; align-items:center; gap:5px;
        padding:3px 9px; border-radius:20px; font-size:11.5px; font-weight:600;
    }
    .status-badge .dot { width:6px; height:6px; border-radius:50%; }
    .s-confirmed   { background:#eff6ff; color:#1d4ed8; }
    .s-confirmed .dot   { background:#3b82f6; }
    .s-checked_in  { background:#f0fdf4; color:#15803d; }
    .s-checked_in .dot  { background:#22c55e; }
    .s-checked_out { background:#f5f3ff; color:#6d28d9; }
    .s-checked_out .dot { background:#a78bfa; }
    .s-cancelled   { background:#fef2f2; color:#dc2626; }
    .s-cancelled .dot   { background:#ef4444; }
    .s-pending     { background:#fffbeb; color:#d97706; }
    .s-pending .dot     { background:#f59e0b; }

    /* ── Main bookings table ─────────────────────────────────────────── */
    .rpt-table { width:100%; border-collapse:collapse; }
    .rpt-table thead th {
        padding:11px 16px; font-size:11px; font-weight:700;
        text-transform:uppercase; letter-spacing:.5px;
        color:#9ca3af; border-bottom:1px solid #f3f4f6; background:#fafafa; white-space:nowrap;
    }
    .rpt-table tbody tr { border-bottom:1px solid #f3f4f6; transition:background .12s; }
    .rpt-table tbody tr:hover { background:#fafbff; }
    .rpt-table tbody td { padding:11px 16px; font-size:13.5px; color:#374151; vertical-align:middle; }
    .rpt-table tbody tr:last-child { border-bottom:none; }

    /* ── Occupancy bar ───────────────────────────────────────────────── */
    .occ-bar-wrap { height:8px; background:#f3f4f6; border-radius:4px; overflow:hidden; margin-top:8px; }
    .occ-bar-fill { height:100%; border-radius:4px; transition:width .4s; }

    .empty-state { text-align:center; padding:52px 20px; color:#9ca3af; }
    .empty-state i { font-size:42px; margin-bottom:12px; display:block; color:#d1d5db; }

    @media (max-width:1100px) { .charts-row { grid-template-columns:1fr; } .bottom-row { grid-template-columns:1fr; } .summary-grid { grid-template-columns:repeat(2,1fr); } }
    @media (max-width:600px)  { .summary-grid { grid-template-columns:1fr 1fr; } }
</style>
@endpush

@section('content')

{{-- ── Report Tabs ── --}}
<div class="report-tabs">
    <a href="{{ route('reports.bookings.daily') }}" class="report-tab">
        <i class="fas fa-calendar-day"></i> Daily
    </a>
    <a href="{{ route('reports.bookings.monthly', ['year' => $year, 'month' => $month]) }}" class="report-tab active">
        <i class="fas fa-calendar-alt"></i> Monthly
    </a>
    <a href="{{ route('reports.income', ['year' => $year]) }}" class="report-tab">
        <i class="fas fa-chart-line"></i> Income
    </a>
</div>

{{-- ── Month Picker ── --}}
<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.bookings.monthly') }}">
            <div class="month-bar">
                @php
                    $prevDate = \Carbon\Carbon::create($year, $month, 1)->subMonth();
                    $nextDate = \Carbon\Carbon::create($year, $month, 1)->addMonth();
                @endphp
                <a href="{{ route('reports.bookings.monthly', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}" class="nav-arrow">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <select name="month" class="month-select">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                        </option>
                    @endforeach
                </select>
                <select name="year" class="month-select">
                    @foreach(range(now()->year - 3, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <a href="{{ route('reports.bookings.monthly', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}" class="nav-arrow">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <button type="submit" class="btn-go">
                    <i class="fas fa-search"></i> View
                </button>
                <span style="margin-left:auto;font-size:14px;font-weight:700;color:#111827;">
                    {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
                </span>
            </div>
        </form>
    </div>
</div>

{{-- ── Summary Cards ── --}}
<div class="summary-grid">
    <div class="sum-card">
        <div class="sum-icon blue"><i class="fas fa-clipboard-list"></i></div>
        <div class="sum-val">{{ $summary['total_bookings'] }}</div>
        <div class="sum-lbl">Total Bookings</div>
        <div class="sum-sub" style="color:#6b7280;">
            <span style="color:#22c55e;">{{ $summary['checked_out'] }}</span> completed ·
            <span style="color:#ef4444;">{{ $summary['cancelled'] }}</span> cancelled
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div class="sum-val">${{ number_format($summary['total_revenue'], 0) }}</div>
        <div class="sum-lbl">Total Revenue</div>
        <div class="sum-sub" style="color:#6b7280;">
            @foreach($summary['revenue_breakdown'] as $type => $amt)
                <span>{{ ucfirst($type) }}: ${{ number_format($amt, 0) }}</span>
                @if(!$loop->last) · @endif
            @endforeach
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-icon teal"><i class="fas fa-percent"></i></div>
        <div class="sum-val">{{ $summary['occupancy_rate'] }}%</div>
        <div class="sum-lbl">Occupancy Rate</div>
        <div class="occ-bar-wrap">
            <div class="occ-bar-fill" style="width:{{ min($summary['occupancy_rate'], 100) }}%; background:{{ $summary['occupancy_rate'] >= 70 ? '#22c55e' : ($summary['occupancy_rate'] >= 40 ? '#f59e0b' : '#ef4444') }};"></div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-icon amber"><i class="fas fa-bed"></i></div>
        <div class="sum-val">{{ $summary['checked_in'] }}</div>
        <div class="sum-lbl">Currently Checked In</div>
        <div class="sum-sub" style="color:#6b7280;">
            {{ $summary['confirmed'] }} confirmed · awaiting
        </div>
    </div>
</div>

{{-- ── Charts Row ── --}}
<div class="charts-row">

    {{-- Daily Revenue Chart --}}
    <div class="chart-card">
        <div class="card-hdr">
            <span class="card-hdr-title">
                <i class="fas fa-chart-area" style="color:#1a56db;"></i>
                Daily Revenue — {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            </span>
        </div>
        <div class="card-body-pad">
            <canvas id="dailyRevenueChart" height="90"></canvas>
        </div>
    </div>

    {{-- Booking Status Doughnut --}}
    <div class="chart-card">
        <div class="card-hdr">
            <span class="card-hdr-title">
                <i class="fas fa-chart-pie" style="color:#1a56db;"></i>
                Booking Status
            </span>
        </div>
        <div class="card-body-pad">
            <canvas id="statusChart" height="140"></canvas>
            <div style="display:flex;flex-direction:column;gap:7px;margin-top:16px;">
                @php $statusColors = ['confirmed'=>'#3b82f6','checked_in'=>'#22c55e','checked_out'=>'#a78bfa','cancelled'=>'#ef4444','pending'=>'#f59e0b']; @endphp
                @foreach($summary['by_status'] as $status => $count)
                    @if($count > 0)
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;">
                            <span style="width:10px;height:10px;border-radius:3px;background:{{ $statusColors[$status] ?? '#9ca3af' }};display:inline-block;flex-shrink:0;"></span>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </div>
                        <span style="font-size:13px;font-weight:700;color:#111827;">{{ $count }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ── Bottom Row: Room Types + Payment Breakdown ── --}}
<div class="bottom-row">

    {{-- By Room Type --}}
    <div class="chart-card">
        <div class="card-hdr">
            <span class="card-hdr-title">
                <i class="fas fa-door-open" style="color:#1a56db;"></i>
                Bookings by Room Type
            </span>
        </div>
        @if($summary['by_room_type']->isEmpty())
            <div class="empty-state"><i class="fas fa-bed"></i><p>No data for this period.</p></div>
        @else
        <table class="rt-table">
            <thead>
                <tr>
                    <th>Room Type</th>
                    <th style="text-align:center;">Bookings</th>
                    <th style="text-align:right;">Revenue</th>
                    <th style="text-align:right;">Share</th>
                </tr>
            </thead>
            <tbody>
                @php $totalRev = $summary['by_room_type']->sum('revenue') ?: 1; @endphp
                @foreach($summary['by_room_type'] as $rt)
                <tr>
                    <td style="font-weight:600;">{{ $rt->room_type }}</td>
                    <td style="text-align:center;">{{ $rt->bookings_count }}</td>
                    <td style="text-align:right;font-weight:700;color:#111827;">${{ number_format($rt->revenue, 0) }}</td>
                    <td style="text-align:right;">
                        @php $share = round(($rt->revenue / $totalRev) * 100, 1); @endphp
                        <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end;">
                            <span style="font-size:12px;color:#6b7280;font-weight:600;">{{ $share }}%</span>
                            <div style="width:50px;height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $share }}%;height:100%;background:#1a56db;border-radius:3px;"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Payment Breakdown --}}
    <div class="chart-card">
        <div class="card-hdr">
            <span class="card-hdr-title">
                <i class="fas fa-credit-card" style="color:#1a56db;"></i>
                Payment Breakdown
            </span>
        </div>
        <div class="card-body-pad">
            @if($summary['revenue_breakdown']->isEmpty())
                <div style="text-align:center;padding:30px 0;color:#9ca3af;font-size:14px;">No payments recorded.</div>
            @else
                @php
                    $payColors = ['cash'=>'#22c55e','card'=>'#3b82f6','bank_transfer'=>'#a78bfa','online'=>'#f59e0b'];
                    $totalPay  = $summary['revenue_breakdown']->sum() ?: 1;
                @endphp
                <canvas id="paymentChart" height="160" style="margin-bottom:16px;"></canvas>
                @foreach($summary['revenue_breakdown'] as $type => $amt)
                <div class="pay-item">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span class="pay-dot" style="background:{{ $payColors[$type] ?? '#9ca3af' }};"></span>
                        <span style="font-size:13.5px;font-weight:600;color:#374151;">{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:14px;font-weight:800;color:#111827;">${{ number_format($amt, 2) }}</div>
                        <div style="font-size:11.5px;color:#9ca3af;">{{ round(($amt / $totalPay) * 100, 1) }}%</div>
                    </div>
                </div>
                @endforeach
                <div style="border-top:2px solid #e5e7eb;margin-top:8px;padding-top:12px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;font-weight:700;color:#374151;">Total</span>
                    <span style="font-size:16px;font-weight:800;color:#1a56db;">${{ number_format($summary['total_revenue'], 2) }}</span>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Full Bookings Table ── --}}
<div class="card" style="border-radius:12px;">
    <div class="card-hdr">
        <span class="card-hdr-title">
            <i class="fas fa-list" style="color:#1a56db;"></i>
            All Bookings — {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            <span style="font-size:12px;font-weight:500;color:#9ca3af;margin-left:4px;">({{ $bookings->count() }} records)</span>
        </span>
    </div>
    @if($bookings->isEmpty())
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>No bookings found for {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}.</p>
        </div>
    @else
    <div style="overflow-x:auto;">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Nights</th>
                    <th>Status</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $b)
                <tr>
                    <td>
                        <a href="{{ route('bookings.show', $b) }}" style="font-weight:700;color:#1a56db;text-decoration:none;font-size:13px;">
                            #{{ $b->id }}
                        </a>
                    </td>
                    <td>
                        <div style="font-weight:600;color:#111827;font-size:13.5px;">{{ $b->customer->name ?? '—' }}</div>
                        <div style="font-size:12px;color:#9ca3af;">{{ $b->customer->phone ?? '' }}</div>
                    </td>
                    <td style="font-weight:600;">{{ $b->room->room_number ?? '—' }}</td>
                    <td style="font-size:12.5px;color:#6b7280;">{{ $b->room->roomType->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($b->check_in_date)->format('d M') }}</td>
                    <td>{{ \Carbon\Carbon::parse($b->check_out_date)->format('d M') }}</td>
                    <td style="text-align:center;font-weight:600;">{{ $b->nights }}</td>
                    <td>
                        <span class="status-badge s-{{ $b->status }}">
                            <span class="dot"></span>
                            {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                        </span>
                    </td>
                    <td style="text-align:right;font-weight:700;color:#111827;">
                        ${{ number_format($b->invoice->grand_total ?? $b->room_total ?? 0, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb;">
                    <td colspan="8" style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151;text-align:right;">Month Revenue (Paid)</td>
                    <td style="padding:12px 16px;font-size:15px;font-weight:800;color:#1a56db;text-align:right;">
                        ${{ number_format($summary['total_revenue'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    // ── Daily Revenue Line Chart ──────────────────────────────────────
    const dailyData = @json($summary['daily_revenue']);
    const daysInMonth = {{ \Carbon\Carbon::create($year, $month)->daysInMonth }};

    // Build a full array for every day in the month
    const revenueByDay = Array(daysInMonth).fill(0);
    dailyData.forEach(r => {
        const day = parseInt(r.date.split('-')[2], 10) - 1;
        revenueByDay[day] = parseFloat(r.total);
    });
    const dayLabels = Array.from({ length: daysInMonth }, (_, i) => i + 1);

    const ctxDaily = document.getElementById('dailyRevenueChart');
    if (ctxDaily) {
        new Chart(ctxDaily, {
            type: 'bar',
            data: {
                labels: dayLabels,
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenueByDay,
                    backgroundColor: 'rgba(26,86,219,.15)',
                    borderColor: '#1a56db',
                    borderWidth: 1.5,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' $' + ctx.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
                    y: {
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            font: { size: 11 }, color: '#9ca3af',
                            callback: v => '$' + v.toLocaleString()
                        }
                    }
                }
            }
        });
    }

    // ── Status Doughnut ───────────────────────────────────────────────
    const statusData = @json($summary['by_status']);
    const statusColors = {
        confirmed:   '#3b82f6',
        checked_in:  '#22c55e',
        checked_out: '#a78bfa',
        cancelled:   '#ef4444',
        pending:     '#f59e0b',
    };
    const ctxStatus = document.getElementById('statusChart');
    if (ctxStatus && Object.keys(statusData).length) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData).map(s => s.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: Object.keys(statusData).map(s => statusColors[s] || '#9ca3af'),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: { legend: { display: false } }
            }
        });
    }

    // ── Payment Doughnut ──────────────────────────────────────────────
    const payData = @json($summary['revenue_breakdown']);
    const payColors = { cash: '#22c55e', card: '#3b82f6', bank_transfer: '#a78bfa', online: '#f59e0b' };
    const ctxPay = document.getElementById('paymentChart');
    if (ctxPay && Object.keys(payData).length) {
        new Chart(ctxPay, {
            type: 'doughnut',
            data: {
                labels: Object.keys(payData).map(s => s.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())),
                datasets: [{
                    data: Object.values(payData),
                    backgroundColor: Object.keys(payData).map(s => payColors[s] || '#9ca3af'),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: { legend: { display: false } }
            }
        });
    }
})();
</script>
@endpush