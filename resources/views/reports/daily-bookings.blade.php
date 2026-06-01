@extends('layouts.app')

@section('title', 'Daily Bookings Report')
@section('page-title', 'Daily Bookings Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.bookings.monthly') }}">Reports</a></li>
    <li class="breadcrumb-item active">Daily</li>
@endsection

@section('header-actions')
    <div style="display:flex;gap:8px;align-items:center;">
        <a href="{{ route('reports.bookings.monthly', ['year' => date('Y', strtotime($date)), 'month' => date('n', strtotime($date))]) }}"
           class="btn btn-sm" style="border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;height:34px;display:flex;align-items:center;gap:6px;padding:0 14px;text-decoration:none;">
            <i class="fas fa-calendar-alt"></i> Monthly View
        </a>
        <a href="{{ route('reports.income', ['year' => date('Y', strtotime($date))]) }}"
           class="btn btn-sm" style="border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;height:34px;display:flex;align-items:center;gap:6px;padding:0 14px;text-decoration:none;">
            <i class="fas fa-chart-line"></i> Income Report
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* ── Report nav tabs ──────────────────────────────────────────────── */
    .report-tabs {
        display: flex; gap: 6px; margin-bottom: 20px;
    }
    .report-tab {
        height: 36px; padding: 0 18px;
        border-radius: 9px; border: 1.5px solid #e5e7eb;
        background: #fff; color: #6b7280;
        font-family: inherit; font-size: 13px; font-weight: 600;
        cursor: pointer; display: flex; align-items: center; gap: 7px;
        text-decoration: none; transition: all .15s;
    }
    .report-tab:hover { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }
    .report-tab.active { background: #1a56db; border-color: #1a56db; color: #fff; }

    /* ── Date picker bar ──────────────────────────────────────────────── */
    .date-bar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .date-input {
        height: 36px; padding: 0 12px; border: 1.5px solid #e5e7eb;
        border-radius: 8px; font-family: inherit; font-size: 13px;
        color: #374151; background: #fff; outline: none;
        transition: border-color .2s;
    }
    .date-input:focus { border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.08); }
    .btn-go {
        height: 36px; padding: 0 18px; border-radius: 8px;
        border: none; background: #1a56db; color: #fff;
        font-family: inherit; font-size: 13px; font-weight: 600;
        cursor: pointer; display: flex; align-items: center; gap: 6px;
        transition: background .15s;
    }
    .btn-go:hover { background: #1447b5; }
    .nav-arrow {
        width: 36px; height: 36px; border-radius: 8px;
        border: 1.5px solid #e5e7eb; background: #fff;
        color: #6b7280; display: flex; align-items: center; justify-content: center;
        text-decoration: none; font-size: 13px; transition: all .15s;
    }
    .nav-arrow:hover { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }

    /* ── Summary stat cards ───────────────────────────────────────────── */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    .sum-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 18px;
        display: flex; align-items: center; gap: 14px;
    }
    .sum-icon {
        width: 42px; height: 42px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; flex-shrink: 0;
    }
    .sum-icon.blue   { background: #eff6ff; color: #1a56db; }
    .sum-icon.green  { background: #f0fdf4; color: #16a34a; }
    .sum-icon.amber  { background: #fffbeb; color: #d97706; }
    .sum-icon.red    { background: #fef2f2; color: #dc2626; }
    .sum-icon.purple { background: #f5f3ff; color: #7c3aed; }
    .sum-val  { font-size: 22px; font-weight: 800; color: #111827; line-height: 1; }
    .sum-lbl  { font-size: 12px; color: #6b7280; font-weight: 500; margin-top: 3px; }

    /* ── Status badge ─────────────────────────────────────────────────── */
    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 20px;
        font-size: 11.5px; font-weight: 600; white-space: nowrap;
    }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .s-confirmed  { background: #eff6ff; color: #1d4ed8; }
    .s-confirmed .dot  { background: #3b82f6; }
    .s-checked_in { background: #f0fdf4; color: #15803d; }
    .s-checked_in .dot { background: #22c55e; }
    .s-checked_out { background: #f5f3ff; color: #6d28d9; }
    .s-checked_out .dot { background: #a78bfa; }
    .s-cancelled  { background: #fef2f2; color: #dc2626; }
    .s-cancelled .dot  { background: #ef4444; }
    .s-pending    { background: #fffbeb; color: #d97706; }
    .s-pending .dot    { background: #f59e0b; }

    /* ── Table ────────────────────────────────────────────────────────── */
    .rpt-table { width: 100%; border-collapse: collapse; }
    .rpt-table thead th {
        padding: 11px 16px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .5px;
        color: #9ca3af; border-bottom: 1px solid #f3f4f6;
        background: #fafafa; white-space: nowrap;
    }
    .rpt-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .12s; }
    .rpt-table tbody tr:hover { background: #fafbff; }
    .rpt-table tbody td { padding: 12px 16px; font-size: 13.5px; color: #374151; vertical-align: middle; }
    .rpt-table tbody tr:last-child { border-bottom: none; }

    /* ── By-status pills row ──────────────────────────────────────────── */
    .status-dist {
        display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px;
    }
    .status-pill {
        display: flex; align-items: center; gap: 7px;
        background: #f9fafb; border: 1px solid #e5e7eb;
        border-radius: 20px; padding: 5px 12px;
        font-size: 12.5px; font-weight: 600; color: #374151;
    }
    .status-pill .dot { width: 8px; height: 8px; border-radius: 50%; }

    /* ── Card helpers ─────────────────────────────────────────────────── */
    .card-hdr {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 20px; border-bottom: 1px solid #e5e7eb;
    }
    .card-hdr-title { font-size: 14px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; }
    .empty-state { text-align: center; padding: 52px 20px; color: #9ca3af; }
    .empty-state i { font-size: 42px; margin-bottom: 12px; display: block; color: #d1d5db; }
    .empty-state p { font-size: 14px; margin: 0; }

    @media (max-width: 1100px) { .summary-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 700px)  { .summary-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@section('content')

{{-- ── Report Type Tabs ── --}}
<div class="report-tabs">
    <a href="{{ route('reports.bookings.daily', ['date' => $date]) }}" class="report-tab active">
        <i class="fas fa-calendar-day"></i> Daily
    </a>
    <a href="{{ route('reports.bookings.monthly', ['year' => date('Y', strtotime($date)), 'month' => date('n', strtotime($date))]) }}" class="report-tab">
        <i class="fas fa-calendar-alt"></i> Monthly
    </a>
    <a href="{{ route('reports.income', ['year' => date('Y', strtotime($date))]) }}" class="report-tab">
        <i class="fas fa-chart-line"></i> Income
    </a>
</div>

{{-- ── Date Picker ── --}}
<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.bookings.daily') }}">
            <div class="date-bar">
                @php
                    $prev = \Carbon\Carbon::parse($date)->subDay()->toDateString();
                    $next = \Carbon\Carbon::parse($date)->addDay()->toDateString();
                @endphp
                <a href="{{ route('reports.bookings.daily', ['date' => $prev]) }}" class="nav-arrow" title="Previous day">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <input type="date" name="date" class="date-input" value="{{ $date }}">
                <a href="{{ route('reports.bookings.daily', ['date' => $next]) }}" class="nav-arrow" title="Next day">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <button type="submit" class="btn-go">
                    <i class="fas fa-search"></i> View
                </button>
                @if($date !== today()->toDateString())
                    <a href="{{ route('reports.bookings.daily') }}" style="font-size:13px;color:#1a56db;font-weight:600;text-decoration:none;margin-left:4px;">
                        <i class="fas fa-dot-circle me-1" style="font-size:10px;"></i>Today
                    </a>
                @endif
                <span style="margin-left:auto;font-size:14px;font-weight:700;color:#111827;">
                    {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
                </span>
            </div>
        </form>
    </div>
</div>

{{-- ── Summary Cards ── --}}
<div class="summary-grid">
    <div class="sum-card">
        <div class="sum-icon blue"><i class="fas fa-clipboard-list"></i></div>
        <div>
            <div class="sum-val">{{ $summary['total_bookings'] }}</div>
            <div class="sum-lbl">Total Bookings</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-icon green"><i class="fas fa-sign-in-alt"></i></div>
        <div>
            <div class="sum-val">{{ $summary['check_ins'] }}</div>
            <div class="sum-lbl">Check-Ins</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-icon purple"><i class="fas fa-sign-out-alt"></i></div>
        <div>
            <div class="sum-val">{{ $summary['check_outs'] }}</div>
            <div class="sum-lbl">Check-Outs</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-icon red"><i class="fas fa-times-circle"></i></div>
        <div>
            <div class="sum-val">{{ $summary['by_status']['cancelled'] ?? 0 }}</div>
            <div class="sum-lbl">Cancellations</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-icon amber"><i class="fas fa-dollar-sign"></i></div>
        <div>
            <div class="sum-val">${{ number_format($summary['revenue'], 0) }}</div>
            <div class="sum-lbl">Revenue</div>
        </div>
    </div>
</div>

{{-- ── Bookings Table ── --}}
<div class="card" style="border-radius:12px;">
    <div class="card-hdr">
        <span class="card-hdr-title">
            <i class="fas fa-list" style="color:#1a56db;"></i>
            Bookings for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
            <span style="font-size:12px;font-weight:500;color:#9ca3af;margin-left:4px;">({{ $bookings->count() }} record{{ $bookings->count() !== 1 ? 's' : '' }})</span>
        </span>
        {{-- Status distribution --}}
        @if($bookings->count())
        <div class="status-dist">
            @foreach($summary['by_status'] as $status => $count)
            <div class="status-pill">
                <span class="dot" style="background:{{ match($status) {
                    'confirmed'   => '#3b82f6',
                    'checked_in'  => '#22c55e',
                    'checked_out' => '#a78bfa',
                    'cancelled'   => '#ef4444',
                    default       => '#f59e0b',
                } }};"></span>
                {{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $count }}
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @if($bookings->isEmpty())
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>No bookings found for {{ \Carbon\Carbon::parse($date)->format('d F Y') }}.</p>
        </div>
    @else
    <div style="overflow-x:auto;">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Nights</th>
                    <th>Status</th>
                    <th>Invoice</th>
                    <th>Total</th>
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
                    <td>
                        <div style="font-weight:600;color:#111827;">{{ $b->room->room_number ?? '—' }}</div>
                        <div style="font-size:12px;color:#9ca3af;">{{ $b->room->roomType->name ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-size:13.5px;">{{ \Carbon\Carbon::parse($b->check_in_date)->format('d M Y') }}</div>
                        @if($b->checkIn)
                            <div style="font-size:11.5px;color:#22c55e;font-weight:600;">
                                <i class="fas fa-check" style="font-size:10px;"></i>
                                {{ \Carbon\Carbon::parse($b->checkIn->check_in_time)->format('H:i') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:13.5px;">{{ \Carbon\Carbon::parse($b->check_out_date)->format('d M Y') }}</div>
                        @if($b->checkOut)
                            <div style="font-size:11.5px;color:#a78bfa;font-weight:600;">
                                <i class="fas fa-check" style="font-size:10px;"></i>
                                {{ \Carbon\Carbon::parse($b->checkOut->check_out_time)->format('H:i') }}
                            </div>
                        @endif
                    </td>
                    <td style="text-align:center;font-weight:600;">{{ $b->nights }}</td>
                    <td>
                        <span class="status-badge s-{{ $b->status }}">
                            <span class="dot"></span>
                            {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                        </span>
                    </td>
                    <td>
                        @if($b->invoice)
                            <span class="status-badge {{ $b->invoice->status === 'paid' ? 's-checked_in' : ($b->invoice->status === 'void' ? 's-cancelled' : 's-pending') }}">
                                <span class="dot"></span>
                                {{ ucfirst($b->invoice->status) }}
                            </span>
                        @else
                            <span style="color:#d1d5db;font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="font-weight:700;color:#111827;">
                        ${{ number_format($b->invoice->grand_total ?? $b->room_total ?? 0, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb;">
                    <td colspan="8" style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151;text-align:right;">
                        Day Revenue (Paid)
                    </td>
                    <td style="padding:12px 16px;font-size:15px;font-weight:800;color:#1a56db;">
                        ${{ number_format($summary['revenue'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

@endsection