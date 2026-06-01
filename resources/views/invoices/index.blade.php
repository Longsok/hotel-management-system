@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item active">Invoices</li>
@endsection

@push('styles')
<style>
    .filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .filter-input, .filter-select {
        height:36px; padding:0 12px; border:1.5px solid #e5e7eb; border-radius:8px;
        font-family:inherit; font-size:13px; color:#374151; background:#fff;
        outline:none; transition:border-color .2s;
    }
    .filter-input:focus, .filter-select:focus { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.08); }
    .btn-filter { height:36px; padding:0 16px; border-radius:8px; border:none; background:#1a56db; color:#fff; font-family:inherit; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; }
    .btn-filter:hover { background:#1447b5; }
    .btn-reset  { height:36px; padding:0 14px; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#6b7280; font-family:inherit; font-size:13px; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px; }
    .btn-reset:hover { border-color:#d1d5db; color:#374151; }

    .status-pill { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; display:inline-block; }
    .status-draft  { background:#f1f5f9; color:#475569; }
    .status-issued { background:#eff6ff; color:#1d4ed8; }
    .status-paid   { background:#f0fdf4; color:#15803d; }
    .status-void   { background:#fef2f2; color:#dc2626; }

    .action-btn { width:29px; height:29px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #e5e7eb; color:#6b7280; background:#fff; font-size:12px; text-decoration:none; transition:all .15s; }
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
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="filter-bar">
                <div>
                    <label style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px; display:block;">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="draft"  {{ request('status') === 'draft'  ? 'selected' : '' }}>Draft</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="paid"   {{ request('status') === 'paid'   ? 'selected' : '' }}>Paid</option>
                        <option value="void"   {{ request('status') === 'void'   ? 'selected' : '' }}>Void</option>
                    </select>
                </div>
                <div style="align-self:flex-end;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                @if(request('status'))
                <div style="align-self:flex-end;">
                    <a href="{{ route('invoices.index') }}" class="btn-reset">
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
                Showing <strong style="color:#111827;">{{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }}</strong>
                of <strong style="color:#111827;">{{ $invoices->total() }}</strong> invoices
            </span>
        </div>

        @if($invoices->isEmpty())
            <div class="empty-state">
                <i class="fas fa-file-invoice-dollar"></i>
                <p>No invoices found{{ request('status') ? ' with this status' : '' }}.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:13.5px;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="padding:11px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Invoice #</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Customer</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Grand Total</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Status</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">Created</th>
                        <th style="padding:11px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; border-bottom:1px solid #f3f4f6;">By</th>
                        <th style="padding:11px 16px; border-bottom:1px solid #f3f4f6;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td style="padding:12px 20px; vertical-align:middle;">
                            <a href="{{ route('invoices.show', $invoice) }}" style="font-weight:700; color:#1a56db; font-family:monospace; text-decoration:none;">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <div style="font-weight:600; color:#111827;">{{ $invoice->booking->customer->name }}</div>
                            <div style="font-size:12px; color:#9ca3af;">{{ $invoice->booking->booking_number }}</div>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; font-weight:700; color:#111827;">
                            ${{ number_format($invoice->grand_total, 2) }}
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <span class="status-pill status-{{ $invoice->status }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; color:#6b7280; font-size:13px;">
                            {{ $invoice->created_at->format('d M Y') }}
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle; color:#6b7280; font-size:13px;">
                            {{ $invoice->createdBy->name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px; vertical-align:middle;">
                            <a href="{{ route('invoices.show', $invoice) }}" class="action-btn" title="View Invoice">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('invoices.pdf', $invoice) }}" class="action-btn" title="Download PDF" style="color:#dc2626; border-color:#fca5a5;">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div style="padding:14px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end;">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
        @endif

    </div>
</div>

@endsection
