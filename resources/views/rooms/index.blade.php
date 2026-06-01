@extends('layouts.app')

@section('title', 'Rooms')
@section('page-title', 'Rooms')

@section('breadcrumb')
    <li class="breadcrumb-item active">Rooms</li>
@endsection

@section('header-actions')
    @if(auth()->user()->isAdmin())
        <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Room
        </a>
    @endif
@endsection

@push('styles')
<style>
    .tab-switcher {
        display: inline-flex; background: #f3f4f6;
        border-radius: 10px; padding: 3px; gap: 2px;
    }
    .tab-btn {
        padding: 7px 18px; border-radius: 8px; border: none;
        background: transparent; font-family: inherit;
        font-size: 13px; font-weight: 600; color: #6b7280;
        cursor: pointer; transition: all .2s;
        display: flex; align-items: center; gap: 6px;
    }
    .tab-btn.active { background: #fff; color: #111827; box-shadow: 0 1px 4px rgba(0,0,0,.1); }

    .filter-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .filter-select, .filter-input {
        height: 36px; padding: 0 12px;
        border: 1.5px solid #e5e7eb; border-radius: 8px;
        font-family: inherit; font-size: 13px; color: #374151;
        background: #fff; outline: none; min-width: 130px;
        transition: border-color .2s;
    }
    .filter-select:focus, .filter-input:focus {
        border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.08);
    }
    .filter-input { min-width: 200px; padding-left: 34px; }
    .filter-input-wrap { position: relative; }
    .filter-input-icon {
        position: absolute; left: 11px; top: 50%;
        transform: translateY(-50%); color: #9ca3af; font-size: 13px; pointer-events: none;
    }
    .btn-filter {
        height: 36px; padding: 0 16px; border-radius: 8px; border: none;
        background: #1a56db; color: #fff; font-family: inherit;
        font-size: 13px; font-weight: 600; cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        transition: background .2s;
    }
    .btn-filter:hover { background: #1447b5; }
    .btn-reset {
        height: 36px; padding: 0 14px; border-radius: 8px;
        border: 1.5px solid #e5e7eb; background: #fff; color: #6b7280;
        font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        text-decoration: none; transition: all .2s;
    }
    .btn-reset:hover { border-color: #d1d5db; color: #374151; }

    /* Status summary chips */
    .status-strip { display: flex; gap: 8px; flex-wrap: wrap; }
    .status-chip {
        display: flex; align-items: center; gap: 6px;
        padding: 5px 12px; border-radius: 20px;
        font-size: 12.5px; font-weight: 600; cursor: pointer;
        border: 1.5px solid transparent; transition: all .2s; text-decoration: none;
    }
    .status-chip .dot { width: 8px; height: 8px; border-radius: 50%; }
    .status-chip.s-all  { background: #f3f4f6; color: #374151; border-color: #d1d5db; }
    .status-chip.s-available   { background: #f0fdf4; color: #15803d; border-color: #86efac; }
    .status-chip.s-occupied    { background: #fef2f2; color: #dc2626; border-color: #fca5a5; }
    .status-chip.s-reserved    { background: #fefce8; color: #a16207; border-color: #fde047; }
    .status-chip.s-cleaning    { background: #fff7ed; color: #c2410c; border-color: #fdba74; }
    .status-chip.s-maintenance { background: #f5f3ff; color: #6d28d9; border-color: #c4b5fd; }
    .status-chip:hover, .status-chip.chip-active { transform: translateY(-1px); box-shadow: 0 3px 10px rgba(0,0,0,.09); }

    /* Floor sections */
    .floor-section { margin-bottom: 26px; }
    .floor-header { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .floor-badge {
        background: #111827; color: #fff;
        font-size: 12px; font-weight: 700;
        padding: 3px 11px; border-radius: 6px;
    }
    .floor-divider { flex: 1; height: 1px; background: #e5e7eb; }
    .floor-count { font-size: 12px; color: #9ca3af; font-weight: 500; }

    /* Room grid tiles */
    .room-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
        gap: 12px;
    }
    .room-tile {
        border-radius: 12px; padding: 14px 8px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 3px; cursor: pointer; text-decoration: none;
        transition: transform .15s, box-shadow .2s;
        border: 2px solid transparent;
    }
    .room-tile:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.12); text-decoration: none; }
    .room-tile .t-icon  { font-size: 22px; }
    .room-tile .t-num   { font-size: 16px; font-weight: 800; line-height: 1; }
    .room-tile .t-type  { font-size: 9.5px; font-weight: 500; opacity: .75; }
    .room-tile .t-stat  { font-size: 9px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; opacity: .8; }
    .room-tile.available   { background: #dcfce7; border-color: #86efac; color: #15803d; }
    .room-tile.occupied    { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }
    .room-tile.reserved    { background: #fef9c3; border-color: #fde047; color: #a16207; }
    .room-tile.cleaning    { background: #ffedd5; border-color: #fdba74; color: #c2410c; }
    .room-tile.maintenance { background: #ede9fe; border-color: #c4b5fd; color: #6d28d9; }

    /* Legend */
    .legend-bar { display: flex; gap: 14px; flex-wrap: wrap; padding: 10px 0 2px; }
    .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #6b7280; font-weight: 500; }
    .legend-dot { width: 10px; height: 10px; border-radius: 3px; }

    /* List table actions */
    .action-btn {
        width: 30px; height: 30px; border-radius: 7px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #e5e7eb; color: #6b7280; background: #fff;
        transition: all .15s; cursor: pointer; font-size: 12px; text-decoration: none;
    }
    .action-btn:hover { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }
    .action-btn.danger:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }

    .list-panel { display: none; }
    .map-panel  { display: block; }
</style>
@endpush

@section('content')

{{-- ── Top card: tabs + status chips + filter ── --}}
<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body p-3">

        {{-- Row 1: tabs + chips --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div class="tab-switcher">
                <button class="tab-btn active" id="btn-map"  onclick="switchTab('map')">
                    <i class="fas fa-th-large"></i> Room Map
                </button>
                <button class="tab-btn" id="btn-list" onclick="switchTab('list')">
                    <i class="fas fa-list"></i> Room List
                </button>
            </div>

            <div class="status-strip">
                <a href="{{ route('rooms.index', request()->except('status')) }}"
                   class="status-chip s-all {{ !request('status') ? 'chip-active' : '' }}">
                    <span class="dot" style="background:#9ca3af;"></span>
                    All <strong>{{ $totalRooms }}</strong>
                </a>
                @php
                    $chipDefs = [
                        'available'   => ['#22c55e','Available'],
                        'occupied'    => ['#ef4444','Occupied'],
                        'reserved'    => ['#eab308','Reserved'],
                        'cleaning'    => ['#f97316','Cleaning'],
                        'maintenance' => ['#8b5cf6','Maintenance'],
                    ];
                @endphp
                @foreach($chipDefs as $s => [$color, $label])
                    <a href="{{ route('rooms.index', array_merge(request()->query(), ['status' => $s])) }}"
                       class="status-chip s-{{ $s }} {{ request('status') === $s ? 'chip-active' : '' }}">
                        <span class="dot" style="background:{{ $color }};"></span>
                        {{ $label }} <strong>{{ $roomCounts[$s] ?? 0 }}</strong>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Row 2: filter bar --}}
        <form method="GET" action="{{ route('rooms.index') }}">
            <div class="filter-bar">
                <div class="filter-input-wrap">
                    <i class="fas fa-search filter-input-icon"></i>
                    <input type="text" name="search" class="filter-input"
                           placeholder="Search room number…" value="{{ request('search') }}">
                </div>
                <select name="floor" class="filter-select">
                    <option value="">All Floors</option>
                    @foreach($floors as $fl)
                        <option value="{{ $fl }}" {{ request('floor') == $fl ? 'selected' : '' }}>
                            Floor {{ $fl }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                            {{ ucfirst($st) }}
                        </option>
                    @endforeach
                </select>
                <select name="type" class="filter-select">
                    <option value="">All Types</option>
                    @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}" {{ request('type') == $rt->id ? 'selected' : '' }}>
                            {{ $rt->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('rooms.index') }}" class="btn-reset"><i class="fas fa-times"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════
     MAP PANEL
══════════════════════════════════════════ --}}
<div id="map-panel" class="map-panel">
    @forelse($roomsByFloor as $floor => $floorRooms)
        <div class="floor-section">
            <div class="floor-header">
                <span class="floor-badge">Floor {{ $floor }}</span>
                <div class="floor-divider"></div>
                <span class="floor-count">{{ $floorRooms->count() }} room{{ $floorRooms->count() !== 1 ? 's' : '' }}</span>
            </div>
            <div class="room-grid">
                @foreach($floorRooms as $room)
                    <a href="{{ route('rooms.show', $room) }}" class="room-tile {{ $room->status }}">
                        <div class="t-icon">
                            @if($room->status === 'available')   🛏️
                            @elseif($room->status === 'occupied') 🔴
                            @elseif($room->status === 'reserved') 🟡
                            @elseif($room->status === 'cleaning') 🧹
                            @else 🔧
                            @endif
                        </div>
                        <div class="t-num">{{ $room->room_number }}</div>
                        <div class="t-type">{{ $room->roomType->name ?? '–' }}</div>
                        <div class="t-stat">{{ ucfirst($room->status) }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-door-open d-block mb-3" style="font-size:44px;color:#d1d5db;"></i>
                <div style="font-size:15px;font-weight:600;color:#374151;">No rooms found</div>
                <div style="font-size:13.5px;color:#9ca3af;margin-top:6px;">Try adjusting your filters or add a new room.</div>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm mt-3">
                        <i class="fas fa-plus me-1"></i> Add Room
                    </a>
                @endif
            </div>
        </div>
    @endforelse

    @if($rooms->count())
        <div class="legend-bar">
            <div class="legend-item"><div class="legend-dot" style="background:#86efac;"></div> Available</div>
            <div class="legend-item"><div class="legend-dot" style="background:#fca5a5;"></div> Occupied</div>
            <div class="legend-item"><div class="legend-dot" style="background:#fde047;"></div> Reserved</div>
            <div class="legend-item"><div class="legend-dot" style="background:#fdba74;"></div> Cleaning</div>
            <div class="legend-item"><div class="legend-dot" style="background:#c4b5fd;"></div> Maintenance</div>
        </div>
    @endif
</div>

{{-- ══════════════════════════════════════════
     LIST PANEL
══════════════════════════════════════════ --}}
<div id="list-panel" class="list-panel">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Floor</th>
                            <th>Type</th>
                            <th>Price / Night</th>
                            <th>Amenities</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                            <tr>
                                <td>
                                    <span style="font-size:14px;font-weight:700;color:#111827;">{{ $room->room_number }}</span>
                                </td>
                                <td style="font-weight:500;color:#374151;">Floor {{ $room->floor }}</td>
                                <td>
                                    <span style="background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600;padding:3px 9px;border-radius:6px;">
                                        {{ $room->roomType->name ?? '–' }}
                                    </span>
                                </td>
                                <td style="font-weight:700;color:#111827;">${{ number_format($room->roomType->base_price ?? 0, 0) }}</td>
                                <td>
                                    @foreach($room->amenities->take(3) as $am)
                                        <span style="background:#f3f4f6;color:#374151;font-size:11px;font-weight:500;padding:2px 7px;border-radius:5px;margin-right:3px;">{{ $am->name }}</span>
                                    @endforeach
                                    @if($room->amenities->count() > 3)
                                        <span style="font-size:11px;color:#9ca3af;">+{{ $room->amenities->count() - 3 }}</span>
                                    @endif
                                    @if($room->amenities->isEmpty())
                                        <span style="color:#d1d5db;font-size:12px;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $room->status }}">
                                        {{ ucfirst($room->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:5px;">
                                        <a href="{{ route('rooms.show', $room) }}" class="action-btn" title="View"><i class="fas fa-eye"></i></a>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('rooms.edit', $room) }}" class="action-btn" title="Edit"><i class="fas fa-pen"></i></a>
                                            <form action="{{ route('rooms.destroy', $room) }}" method="POST"
                                                  onsubmit="return confirm('Delete room {{ $room->room_number }}? This cannot be undone.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="action-btn danger" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5" style="color:#9ca3af;">
                                    <i class="fas fa-door-open d-block mb-2" style="font-size:28px;opacity:.25;"></i>
                                    No rooms match your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    var saved = localStorage.getItem('roomsTab') || 'map';
    switchTab(saved, false);

    function switchTab(tab, save) {
        if (save !== false) localStorage.setItem('roomsTab', tab);
        document.getElementById('map-panel').style.display  = tab === 'map'  ? 'block' : 'none';
        document.getElementById('list-panel').style.display = tab === 'list' ? 'block' : 'none';
        document.getElementById('btn-map').classList.toggle('active',  tab === 'map');
        document.getElementById('btn-list').classList.toggle('active', tab === 'list');
    }
</script>
@endpush
