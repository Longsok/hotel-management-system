@extends('layouts.app')

@section('title', 'Check-Ins')
@section('page-title', 'Check-Ins')

@section('breadcrumb')
    <li class="breadcrumb-item active">Check-Ins</li>
@endsection

@section('header-actions')
    <a href="{{ route('check-ins.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Check-In
    </a>
@endsection

@push('styles')
<style>
    .filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .filter-input {
        height:36px; padding:0 12px; border:1.5px solid #e5e7eb; border-radius:8px;
        font-family:inherit; font-size:13px; color:#374151; background:#fff;
        outline:none; transition:border-color .2s;
    }
    .filter-input:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .btn-filter { height:36px; padding:0 16px; border-radius:8px; border:none; background:#1a56db; color:#fff; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; }
    .btn-filter:hover { background:#1447b5; }
    .btn-reset { height:36px; padding:0 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#6b7280; font-family:inherit; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px; }
    .btn-reset:hover { border-color:#d1d5db; color:#374151; }

    .action-btn {
        width:29px; height:29px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center;
        border:1px solid #e5e7eb; color:#6b7280; background:#fff; font-size:12px; text-decoration:none; transition:all .15s;
    }
    .action-btn:hover { background:#eff6ff; border-color:#93c5fd; color:#1d4ed8; }

    .empty-state { text-align:center; padding:48px 20px; color:#9ca3af; }
    .empty-state i { font-size:40px; margin-bottom:12px; display:block; color:#d1d5db; }
    .empty-state p { font-size:14px; margin:0; }

    .pagination { gap:4px; }
    .page-link { border-radius:7px !important; font-size:13px !important; font-weight:600; border:1.5px solid #e5e7eb !important; color:#374151 !important; padding:5px 11px !important; }
    .page-item.active .page-link { background:#1a56db !important; border-color:#1a56db !important; color:#fff !important; }
    .page-link:hover { background:#eff6ff !important; border-color:#93c5fd !important; color:#1d4ed8 !important; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px; font-size:14px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter --}}
<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('check-ins.index') }}">
            <div class="filter-bar">
                <div>
                    <label style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px; display:block;">Filter by Date</label>
                    <input type="date" name="date" class="filter-input" value="{{ request('date') }}">
                </div>
                <div style="align-self:flex-end;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                @if(request('date'))
                <div style="align-self:flex-end;">
                    <a href="{{ route('check-ins.index') }}" class="btn-reset">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card" style="border-radius:12px;">
    <div class="card-body p-0">

        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
            <span style="font-size:13px; color:#6b7280;">
                Showing <strong style="color:#111827;">{{ $checkIns->firstItem() ?? 0 }}–{{ $checkIns->lastItem() ?? 0 }}</strong>
                of <strong style="color:#111827;">{{ $checkIns->total() }}</strong> check-ins
            </span>
        </div>

        @if($checkIns->isEmpty())
            <div class="empty-state">
                <i class="fas fa-sign-in-alt"></i>
                <p>No check-ins found{{ request('date') ? ' for this date' : '' }}.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:13.5px;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="padding:11px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Customer</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Room</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Check-in Time</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Deposit</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Staff</th>
                        <th style="padding:11px 16px; border-bottom:1px solid #f3f4f6;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkIns as $checkIn)
                    <tr>
                        <td style="padding:12px 20px; vertical-align:middle;">
                            <div style="font-weight:600; color:#111827;">{{ $checkIn->booking->customer->name }}</div>
                            <div style="font-size:12px; color:#9ca3af;">{{ $checkIn->booking->customer->phone ?? '—' }}</div>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <span style="font-weight:600; color:#111827;">Room {{ $checkIn->booking->room->room_number }}</span>
                            <div style="font-size:12px; color:#9ca3af;">Floor {{ $checkIn->booking->room->floor }}</div>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; color:#374151;">
                            {{ $checkIn->check_in_time->format('d M Y') }}
                            <div style="font-size:12px; color:#9ca3af;">{{ $checkIn->check_in_time->format('h:i A') }}</div>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; font-weight:700; color:#111827;">
                            ${{ number_format($checkIn->deposit_amount, 2) }}
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; color:#6b7280; font-size:13px;">
                            {{ $checkIn->checkedInBy->name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <a href="{{ route('check-ins.show', $checkIn) }}" class="action-btn" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($checkIns->hasPages())
            <div style="padding:14px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end;">
                {{ $checkIns->withQueryString()->links() }}
            </div>
        @endif
        @endif

    </div>
</div>

@endsection