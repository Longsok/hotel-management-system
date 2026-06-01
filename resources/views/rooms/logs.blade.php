@extends('layouts.app')

@section('title', 'Room ' . $room->room_number . ' – Status Log')
@section('page-title', 'Room ' . $room->room_number . ' Status Log')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rooms.show', $room) }}">{{ $room->room_number }}</a></li>
    <li class="breadcrumb-item active">Status Log</li>
@endsection

@section('header-actions')
    <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm"
       style="border:1.5px solid #e5e7eb;background:#fff;color:#374151;border-radius:8px;font-weight:600;font-size:13px;padding:6px 14px;display:flex;align-items:center;gap:6px;text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Back to Room
    </a>
@endsection

@section('content')

<div style="max-width:760px;">
    <div class="card">
        <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-history" style="color:#1a56db;"></i>
            <span style="font-size:14px;font-weight:700;color:#111827;">Full Status History – Room {{ $room->room_number }}</span>
            <span style="margin-left:auto;font-size:12px;color:#9ca3af;">{{ $logs->total() }} records</span>
        </div>

        @if($logs->isNotEmpty())
            <div class="card-body" style="padding:0 20px;">
                <ul style="list-style:none;padding:0;margin:0;">
                    @foreach($logs as $log)
                        <li style="display:flex;gap:14px;padding:16px 0;border-bottom:1px solid #f3f4f6;">
                            <div style="width:36px;height:36px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:13px;margin-top:2px;
                                @if($log->new_status==='available')   background:#f0fdf4;color:#15803d;
                                @elseif($log->new_status==='occupied') background:#fef2f2;color:#dc2626;
                                @elseif($log->new_status==='reserved') background:#fefce8;color:#a16207;
                                @elseif($log->new_status==='cleaning') background:#fff7ed;color:#c2410c;
                                @else background:#f5f3ff;color:#6d28d9;
                                @endif">
                                <i class="fas
                                    @if($log->new_status==='available')   fa-check
                                    @elseif($log->new_status==='occupied') fa-user
                                    @elseif($log->new_status==='reserved') fa-clock
                                    @elseif($log->new_status==='cleaning') fa-broom
                                    @else fa-wrench
                                    @endif
                                "></i>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                                    <span class="status-pill status-{{ $log->old_status }}" style="font-size:11px;padding:2px 8px;">
                                        {{ ucfirst($log->old_status) }}
                                    </span>
                                    <i class="fas fa-arrow-right" style="font-size:10px;color:#9ca3af;"></i>
                                    <span class="status-pill status-{{ $log->new_status }}" style="font-size:11px;padding:2px 8px;">
                                        {{ ucfirst($log->new_status) }}
                                    </span>
                                </div>
                                <div style="font-size:12px;color:#9ca3af;margin-top:3px;">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <strong style="color:#374151;">{{ $log->changedBy->name ?? 'System' }}</strong>
                                    &nbsp;·&nbsp;
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ $log->changed_at->format('M d, Y') }}
                                    &nbsp;·&nbsp;
                                    <i class="far fa-clock me-1"></i>
                                    {{ $log->changed_at->format('H:i') }}
                                    &nbsp;·&nbsp;
                                    {{ $log->changed_at->diffForHumans() }}
                                </div>
                                @if($log->notes)
                                    <div style="margin-top:7px;padding:8px 12px;background:#f9fafb;border-radius:7px;font-size:12.5px;color:#6b7280;font-style:italic;border-left:3px solid #e5e7eb;">
                                        "{{ $log->notes }}"
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div style="padding:14px 20px;border-top:1px solid #e5e7eb;">
                    {{ $logs->links() }}
                </div>
            @endif
        @else
            <div class="card-body text-center py-5" style="color:#9ca3af;">
                <i class="fas fa-history d-block mb-3" style="font-size:40px;opacity:.2;"></i>
                <div style="font-size:15px;font-weight:600;color:#374151;">No status history yet</div>
                <div style="font-size:13.5px;margin-top:6px;">Status changes will appear here once recorded.</div>
            </div>
        @endif
    </div>
</div>

@endsection
