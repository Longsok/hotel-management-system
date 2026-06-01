@extends('layouts.app')

@section('title', 'Income Report')
@section('page-title', 'Income Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.bookings.monthly') }}">Reports</a></li>
    <li class="breadcrumb-item active">Income</li>
@endsection

@section('header-actions')
    <div style="display:flex;gap:8px;align-items:center;">
        <a href="{{ route('reports.bookings.daily') }}" class="btn btn-sm"
           style="border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;height:34px;display:flex;align-items:center;gap:6px;padding:0 14px;text-decoration:none;">
            <i class="fas fa-calendar-day"></i> Daily
        </a>
        <a href="{{ route('reports.bookings.monthly', ['year' => $year]) }}" class="btn btn-sm"
           style="border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;height:34px;display:flex;align-items:center;gap:6px;padding:0 14px;text-decoration:none;">
            <i class="fas fa-calendar-alt"></i> Monthly
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* ── Tabs ──────────────────────────────────────────────────────── */
    .report-tabs { display:flex; gap:6px; margin-bottom:20px; }
    .report-tab {
        height:36px; padding:0 18px; border-radius:9px; border:1.5px solid #e5e7eb;
        background:#fff; color:#6b7280; font-family:inherit; font-size:13px; font-weight:600;
        cursor:pointer; display:flex; align-items:center; gap:7px;
        text-decoration:none; transition:all .15s;
    }
    .report-tab:hover { border-color:#93c5fd; color:#1d4ed8; background:#eff6ff; }
    .report-tab.active { background:#1a56db; border-color:#1a56db; color:#fff; }

    /* ── Filter bar ─────────────────────────────────────────────────── */
    .filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .filter-select {
        height:36px; padding:0 12px; border:1.5px solid #e5e7eb;
        border-radius:8px; font-family:inherit; font-size:13px;
        color:#374151; background:#fff; outline:none; cursor:pointer; transition:border-color .2s;
    }
    .filter-select:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .btn-go {
        height:36px; padding:0 18px; border-radius:8px; border:none;
        background:#1a56db; color:#fff; font-family:inherit; font-size:13px; font-weight:600;
        cursor:pointer; display:flex; align-items:center; gap:6px;
    }
    .btn-go:hover { background:#1447b5; }
    .btn-reset {
        height:36px; padding:0 14px; border-radius:8px; border:1.5px solid #e5e7eb;
        background:#fff; color:#6b7280; font-family:inherit; font-size:13px; font-weight:600;
        cursor:pointer; display:flex; align-items:center; gap:6px; text-decoration:none;
    }
    .btn-reset:hover { border-color:#d1d5db; color:#374151; }

    /* ── Hero income card ───────────────────────────────────────────── */
    .income-hero {
        background: linear-gradient(135deg, #1a56db 0%, #1447b5 100%);
        border-radius: 14px;
        padding: 28px 32px;
        color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(26,86,219,.25);
    }
    .hero-label { font-size: 13px; font-weight: 600; opacity: .75; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 8px; }
    .hero-value { font-size: 42px; font-weight: 900; line-height: 1; letter-spacing: -1px; }
    .hero-sub   { font-size: 13px; opacity: .7; margin-top: 6px; }
    .hero-right { text-align: right; }
    .hero-period { font-size: 16px; font-weight: 700; opacity: .9; }
    .hero-icon-wrap {
        width: 64px; height: 64px; border-radius: 16px;
        background: rgba(255,255,255,.15); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; margin-bottom: 8px; margin-left: auto;
    }

    /* ── Stats row ──────────────────────────────────────────────────── */
    .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
    .stat-card {
        background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 20px;
        display:flex; align-items:center; gap:16px;
    }
    .stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .stat-icon.green  { background:#f0fdf4; color:#16a34a; }
    .stat-icon.blue   { background:#eff6ff; color:#1a56db; }
    .stat-icon.amber  { background:#fffbeb; color:#d97706; }
    .stat-val { font-size:24px; font-weight:800; color:#111827; line-height:1; }
    .stat-lbl { font-size:12px; color:#6b7280; font-weight:500; margin-top:3px; }

    /* ── Charts layout ──────────────────────────────────────────────── */
    .chart-row { display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px; }
    .chart-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; }
    .card-hdr {
        display:flex; align-items:center; justify-content:space-between;
        padding:14px 20px; border-bottom:1px solid #e5e7eb;
    }
    .card-hdr-title { font-size:14px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
    .card-body-pad { padding:20px; }

    /* ── Monthly breakdown table ────────────────────────────────────── */
    .month-table { width:100%; border-collapse:collapse; }
    .month-table thead th {
        padding:11px 16px; font-size:11px; font-weight:700;
        text-transform:uppercase; letter-spacing:.5px;
        color:#9ca3af; border-bottom:1px solid #f3f4f6; background:#fafafa; white-space:nowrap;
    }
    .month-table tbody tr { border-bottom:1px solid #f3f4f6; transition:background .12s; }
    .month-table tbody tr:hover { background:#fafbff; }
    .month-table tbody td { padding:12px 16px; font-size:13.5px; color:#374151; }
    .month-table tbody tr:last-child { border-bottom:none; }
    .month-table tr.current-month { background:#fafbff; }
    .month-table tr.current-month td:first-child { border-left:3px solid #1a56db; }

    /* ── Payment type table ─────────────────────────────────────────── */
    .pay-row {
        display:flex; align-items:center; justify-content:space-between;
        padding:12px 0; border-bottom:1px solid #f3f4f6;
    }
    .pay-row:last-child { border-bottom:none; }
    .pay-color-dot { width:10px; height:10px; border-radius:3px; margin-right:10px; flex-shrink:0; }

    /* ── Sparkline bars ─────────────────────────────────────────────── */
    .spark-bar { display:flex; align-items:flex-end; gap:2px; height:30px; }
    .spark-bar-item { flex:1; border-radius:2px 2px 0 0; background:#eff6ff; min-height:3px; transition:height .3s; }
    .spark-bar-item.active { background:#1a56db; }

    /* ── Month link ─────────────────────────────────────────────────── */
    .month-link { color:#1a56db; font-weight:600; text-decoration:none; font-size:13px; }
    .month-link:hover { text-decoration:underline; }

    .empty-state { text-align:center; padding:40px 20px; color:#9ca3af; }
    .empty-state i { font-size:38px; margin-bottom:10px; display:block; color:#d1d5db; }

    @media (max-width:1100px) { .chart-row { grid-template-columns:1fr; } .stats-row { grid-template-columns:1fr 1fr; } }
    @media (max-width:600px)  { .stats-row { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

{{-- ── Report Tabs ── --}}
<div class="report-tabs">
    <a href="{{ route('reports.bookings.daily') }}" class="report-tab">
        <i class="fas fa-calendar-day"></i> Daily
    </a>
    <a href="{{ route('reports.bookings.monthly', ['year' => $year, 'month' => $month ?? now()->month]) }}" class="report-tab">
        <i class="fas fa-calendar-alt"></i> Monthly
    </a>
    <a href="{{ route('reports.income', ['year' => $year]) }}" class="report-tab active">
        <i class="fas fa-chart-line"></i> Income
    </a>
</div>

{{-- ── Filter Bar ── --}}
<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.income') }}">
            <div class="filter-bar">
                <select name="year" class="filter-select">
                    @foreach(range(now()->year - 3, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <select name="month" class="filter-select">
                    <option value="">All Months</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ isset($month) && $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-go">
                    <i class="fas fa-search"></i> View
                </button>
                @if(isset($month))
                    <a href="{{ route('reports.income', ['year' => $year]) }}" class="btn-reset">
                        <i class="fas fa-times"></i> Clear Month
                    </a>
                @endif
                <span style="margin-left:auto;font-size:14px;font-weight:700;color:#111827;">
                    @if(isset($month))
                        {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
                    @else
                        Full Year {{ $year }}
                    @endif
                </span>
            </div>
        </form>
    </div>
</div>

{{-- ── Hero Card ── --}}
<div class="income-hero">
    <div>
        <div class="hero-label">
            @if(isset($month))
                Revenue for {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            @else
                Total Revenue {{ $year }}
            @endif
        </div>
        <div class="hero-value">${{ number_format($totalIncome, 2) }}</div>
        <div class="hero-sub">
            @php $monthsWithRevenue = $byMonth->where('total', '>', 0)->count(); @endphp
            Across {{ $monthsWithRevenue }} month{{ $monthsWithRevenue !== 1 ? 's' : '' }} with recorded payments
        </div>
    </div>
    <div class="hero-right">
        <div class="hero-icon-wrap"><i class="fas fa-dollar-sign"></i></div>
        <div class="hero-period">
            @if(isset($month))
                {{ \Carbon\Carbon::create($year, $month)->format('M Y') }}
            @else
                FY {{ $year }}
            @endif
        </div>
        @if($byMonth->count() >= 2)
            @php
                $lastTwo  = $byMonth->sortBy('month')->values();
                $last     = $lastTwo->last()->total ?? 0;
                $secondL  = $lastTwo->count() >= 2 ? $lastTwo[$lastTwo->count()-2]->total : 0;
                $change   = $secondL > 0 ? round((($last - $secondL) / $secondL) * 100, 1) : 0;
            @endphp
            <div style="margin-top:6px;font-size:13px;font-weight:600;opacity:.85;">
                @if($change >= 0)
                    <i class="fas fa-arrow-up" style="font-size:11px;"></i> +{{ $change }}% vs prev
                @else
                    <i class="fas fa-arrow-down" style="font-size:11px;"></i> {{ $change }}% vs prev
                @endif
            </div>
        @endif
    </div>
</div>

{{-- ── Stats Row ── --}}
@php
    $monthlyAvg = $monthsWithRevenue > 0 ? round($totalIncome / $monthsWithRevenue, 2) : 0;
    $peakMonth  = $byMonth->sortByDesc('total')->first();
@endphp
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-chart-line"></i></div>
        <div>
            <div class="stat-val">${{ number_format($monthlyAvg, 0) }}</div>
            <div class="stat-lbl">Monthly Average</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-trophy"></i></div>
        <div>
            <div class="stat-val">${{ $peakMonth ? number_format($peakMonth->total, 0) : '0' }}</div>
            <div class="stat-lbl">Peak Month{{ $peakMonth ? ' (' . \Carbon\Carbon::create(null, $peakMonth->month)->format('M') . ')' : '' }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-credit-card"></i></div>
        <div>
            <div class="stat-val">{{ $byType->count() }}</div>
            <div class="stat-lbl">Payment Channels</div>
        </div>
    </div>
</div>

{{-- ── Chart + Payment Type ── --}}
<div class="chart-row">

    {{-- Annual Revenue Bar Chart --}}
    <div class="chart-card">
        <div class="card-hdr">
            <span class="card-hdr-title">
                <i class="fas fa-chart-bar" style="color:#1a56db;"></i>
                Monthly Revenue — {{ $year }}
            </span>
            @if(isset($month))
                <a href="{{ route('reports.income', ['year' => $year]) }}" style="font-size:12.5px;color:#1a56db;font-weight:600;text-decoration:none;">
                    View Full Year →
                </a>
            @endif
        </div>
        <div class="card-body-pad">
            <canvas id="annualChart" height="100"></canvas>
        </div>
    </div>

    {{-- Payment Type Breakdown --}}
    <div class="chart-card">
        <div class="card-hdr">
            <span class="card-hdr-title">
                <i class="fas fa-wallet" style="color:#1a56db;"></i>
                By Payment Type
            </span>
        </div>
        <div class="card-body-pad">
            @if($byType->isEmpty())
                <div class="empty-state"><i class="fas fa-receipt"></i><p>No data.</p></div>
            @else
                @php
                    $payColors = ['cash'=>'#22c55e','card'=>'#3b82f6','bank_transfer'=>'#a78bfa','online'=>'#f59e0b'];
                    $totalPay  = $byType->sum('total') ?: 1;
                @endphp
                <canvas id="typeChart" height="160" style="margin-bottom:20px;"></canvas>
                @foreach($byType as $pt)
                <div class="pay-row">
                    <div style="display:flex;align-items:center;">
                        <span class="pay-color-dot" style="background:{{ $payColors[$pt->payment_type] ?? '#9ca3af' }};"></span>
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:#374151;">{{ ucwords(str_replace('_', ' ', $pt->payment_type)) }}</div>
                            <div style="font-size:11.5px;color:#9ca3af;">{{ round(($pt->total / $totalPay) * 100, 1) }}% of total</div>
                        </div>
                    </div>
                    <div style="font-size:15px;font-weight:800;color:#111827;">${{ number_format($pt->total, 2) }}</div>
                </div>
                @endforeach
                <div style="border-top:2px solid #e5e7eb;margin-top:8px;padding-top:12px;display:flex;justify-content:space-between;">
                    <span style="font-size:13px;font-weight:700;color:#374151;">Total</span>
                    <span style="font-size:16px;font-weight:800;color:#1a56db;">${{ number_format($totalIncome, 2) }}</span>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Monthly Breakdown Table ── --}}
<div class="chart-card" style="margin-bottom:24px;">
    <div class="card-hdr">
        <span class="card-hdr-title">
            <i class="fas fa-table" style="color:#1a56db;"></i>
            Month-by-Month Breakdown — {{ $year }}
        </span>
    </div>
    @if($byMonth->isEmpty())
        <div class="empty-state">
            <i class="fas fa-chart-bar"></i>
            <p>No income data for {{ $year }}.</p>
        </div>
    @else
    @php
        $monthlyMap = $byMonth->keyBy('month');
        $grandTotal = $byMonth->sum('total') ?: 1;
        $maxMonth   = $byMonth->max('total') ?: 1;
    @endphp
    <div style="overflow-x:auto;">
        <table class="month-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th style="text-align:right;">Revenue</th>
                    <th style="text-align:right;">Share</th>
                    <th style="width:200px;">Bar</th>
                    <th style="text-align:right;">MoM Change</th>
                    <th style="text-align:center;">Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach(range(1,12) as $m)
                    @php
                        $rec   = $monthlyMap[$m] ?? null;
                        $total = $rec ? $rec->total : 0;
                        $prevRec   = $monthlyMap[$m - 1] ?? null;
                        $prevTotal = $prevRec ? $prevRec->total : 0;
                        $change    = $prevTotal > 0 ? round((($total - $prevTotal) / $prevTotal) * 100, 1) : null;
                        $share     = round(($total / $grandTotal) * 100, 1);
                        $barWidth  = $maxMonth > 0 ? round(($total / $maxMonth) * 100, 1) : 0;
                        $isCurrent = isset($month) && $m == $month;
                        $isThisMonth = !isset($month) && $m == now()->month && $year == now()->year;
                    @endphp
                    <tr class="{{ $isCurrent || $isThisMonth ? 'current-month' : '' }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if($isCurrent || $isThisMonth)
                                    <span style="width:8px;height:8px;border-radius:50%;background:#1a56db;display:inline-block;flex-shrink:0;"></span>
                                @else
                                    <span style="width:8px;height:8px;border-radius:50%;background:#e5e7eb;display:inline-block;flex-shrink:0;"></span>
                                @endif
                                <span style="font-weight:{{ $isCurrent || $isThisMonth ? '700' : '500' }};color:#111827;">
                                    {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                                </span>
                            </div>
                        </td>
                        <td style="text-align:right;font-weight:{{ $total > 0 ? '700' : '400' }};color:{{ $total > 0 ? '#111827' : '#d1d5db' }};">
                            {{ $total > 0 ? '$' . number_format($total, 2) : '—' }}
                        </td>
                        <td style="text-align:right;font-size:12.5px;color:#6b7280;font-weight:500;">
                            {{ $total > 0 ? $share . '%' : '—' }}
                        </td>
                        <td>
                            <div style="height:8px;background:#f3f4f6;border-radius:4px;overflow:hidden;">
                                <div style="width:{{ $barWidth }}%;height:100%;background:{{ $isCurrent || $isThisMonth ? '#1a56db' : '#93c5fd' }};border-radius:4px;transition:width .3s;"></div>
                            </div>
                        </td>
                        <td style="text-align:right;">
                            @if($change !== null && $total > 0)
                                <span style="font-size:12.5px;font-weight:700;color:{{ $change >= 0 ? '#16a34a' : '#dc2626' }};">
                                    <i class="fas fa-arrow-{{ $change >= 0 ? 'up' : 'down' }}" style="font-size:10px;"></i>
                                    {{ $change >= 0 ? '+' : '' }}{{ $change }}%
                                </span>
                            @else
                                <span style="color:#d1d5db;font-size:12px;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($total > 0)
                                <a href="{{ route('reports.bookings.monthly', ['year' => $year, 'month' => $m]) }}"
                                   style="font-size:12px;color:#1a56db;font-weight:600;text-decoration:none;">
                                    View →
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb;">
                    <td style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151;">Total {{ $year }}</td>
                    <td style="padding:12px 16px;font-size:15px;font-weight:800;color:#1a56db;text-align:right;">
                        ${{ number_format($byMonth->sum('total'), 2) }}
                    </td>
                    <td colspan="4"></td>
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
    const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const byMonthData = @json($byMonth);
    const currentMonth = {{ $month ?? 'null' }};

    // Build full 12-month array
    const revenues = Array(12).fill(0);
    byMonthData.forEach(r => { revenues[r.month - 1] = parseFloat(r.total); });

    const bgColors = revenues.map((_, i) =>
        currentMonth && (i + 1) === currentMonth
            ? '#1a56db'
            : revenues[i] > 0 ? 'rgba(26,86,219,.15)' : 'rgba(229,231,235,.5)'
    );
    const borderColors = revenues.map((_, i) =>
        currentMonth && (i + 1) === currentMonth
            ? '#1447b5'
            : revenues[i] > 0 ? '#1a56db' : '#e5e7eb'
    );

    // ── Annual Bar Chart ──────────────────────────────────────────────
    const ctxAnnual = document.getElementById('annualChart');
    if (ctxAnnual) {
        new Chart(ctxAnnual, {
            type: 'bar',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenues,
                    backgroundColor: bgColors,
                    borderColor: borderColors,
                    borderWidth: 1.5,
                    borderRadius: 5,
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

    // ── Payment Type Doughnut ─────────────────────────────────────────
    const typeData = @json($byType);
    const payColors = { cash: '#22c55e', card: '#3b82f6', bank_transfer: '#a78bfa', online: '#f59e0b' };
    const ctxType = document.getElementById('typeChart');
    if (ctxType && typeData.length) {
        new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: typeData.map(t => t.payment_type.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())),
                datasets: [{
                    data: typeData.map(t => parseFloat(t.total)),
                    backgroundColor: typeData.map(t => payColors[t.payment_type] || '#9ca3af'),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                cutout: '62%',
                plugins: { legend: { display: false } }
            }
        });
    }
})();
</script>
@endpush