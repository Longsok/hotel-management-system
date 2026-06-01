@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('breadcrumb')
    <li class="breadcrumb-item active">Payments</li>
@endsection

@push('styles')
<style>
    .method-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 6px;
        font-size: 11.5px; font-weight: 600;
    }
    .method-cash   { background: #f0fdf4; color: #15803d; }
    .method-stripe { background: #f5f3ff; color: #6d28d9; }
    .type-deposit    { background: #fff7ed; color: #c2410c; }
    .type-settlement { background: #eff6ff; color: #1d4ed8; }
</style>
@endpush

@section('content')

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('payments.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;display:block;margin-bottom:4px;">Booking ID</label>
                <input type="text" name="booking_id" value="{{ request('booking_id') }}"
                    placeholder="e.g. 42"
                    style="height:36px;border-radius:8px;border:1.5px solid #e5e7eb;padding:0 10px;font-size:13px;font-family:inherit;width:120px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;display:block;margin-bottom:4px;">Status</label>
                <select name="status"
                    style="height:36px;border-radius:8px;border:1.5px solid #e5e7eb;padding:0 10px;font-size:13px;font-family:inherit;">
                    <option value="">All</option>
                    @foreach(['pending','paid','failed','refunded'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;display:block;margin-bottom:4px;">Type</label>
                <select name="payment_type"
                    style="height:36px;border-radius:8px;border:1.5px solid #e5e7eb;padding:0 10px;font-size:13px;font-family:inherit;">
                    <option value="">All</option>
                    <option value="deposit"    @selected(request('payment_type') === 'deposit')>Deposit</option>
                    <option value="settlement" @selected(request('payment_type') === 'settlement')>Settlement</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height:36px;padding:0 16px;">
                <i class="fas fa-search me-1"></i> Filter
            </button>
            @if(request()->hasAny(['booking_id','status','payment_type']))
                <a href="{{ route('payments.index') }}" class="btn"
                   style="height:36px;padding:0 14px;border:1.5px solid #e5e7eb;color:#374151;background:#fff;">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:14px;font-weight:700;color:#111827;">
            <i class="fas fa-credit-card me-2" style="color:#1a56db;"></i>
            All Payments
            <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">({{ $payments->total() }})</span>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Type</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td style="color:#9ca3af;font-size:12.5px;">#{{ $payment->id }}</td>
                        <td>
                            <a href="{{ route('bookings.show', $payment->booking_id) }}"
                               style="color:#1a56db;font-weight:600;font-size:13px;text-decoration:none;">
                                #{{ $payment->booking_id }}
                            </a>
                        </td>
                        <td style="font-size:13.5px;">{{ $payment->booking->customer->name ?? '—' }}</td>
                        <td>
                            <span class="method-badge type-{{ $payment->payment_type }}">
                                {{ ucfirst($payment->payment_type) }}
                            </span>
                        </td>
                        <td>
                            <span class="method-badge method-{{ $payment->method }}">
                                @if($payment->method === 'stripe')
                                    <i class="fab fa-stripe-s" style="font-size:12px;"></i>
                                @elseif($payment->method === 'cash')
                                    <i class="fas fa-money-bill-wave" style="font-size:11px;"></i>
                                @endif
                                {{ ucfirst($payment->method) }}
                            </span>
                        </td>
                        <td style="font-weight:700;color:#111827;">${{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <span class="status-pill status-{{ $payment->status }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td style="font-size:12.5px;color:#6b7280;">
                            {{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : ($payment->created_at->format('M d, Y') . ' (pending)') }}
                        </td>
                        <td style="font-size:12.5px;color:#6b7280;">{{ $payment->createdBy->name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('payments.show', $payment) }}"
                               style="font-size:12.5px;color:#1a56db;font-weight:600;text-decoration:none;">
                                View <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:48px 0;color:#9ca3af;">
                            <i class="fas fa-receipt d-block mb-2" style="font-size:28px;opacity:.25;"></i>
                            No payments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #e5e7eb;">
            {{ $payments->links() }}
        </div>
    @endif
</div>

@endsection