<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') – {{ \App\Models\Setting::hotelName() }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

    <style>
        /* ─────────────────────────────────────────────────────────────────────
           Reset AdminLTE layout so it doesn't interfere
        ───────────────────────────────────────────────────────────────────── */
        body.hold-transition .wrapper,
        .wrapper { display: block !important; }
        .main-sidebar { position: fixed !important; }
        .content-wrapper { margin-left: 0 !important; min-height: unset !important; }
        .main-footer { margin-left: 0 !important; }

        /* ─────────────────────────────────────────────────────────────────────
           Variables
        ───────────────────────────────────────────────────────────────────── */
        :root {
            --sidebar-w:  260px;
            --topbar-h:   60px;
            --brand:      #1a56db;
            --brand-dk:   #1447b5;
            --sidebar-bg: #111827;
            --text:       #111827;
            --muted:      #6b7280;
            --border:     #e5e7eb;
            --card-r:     12px;
            --bg:         #f3f4f8;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background: var(--bg) !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* ─────────────────────────────────────────────────────────────────────
           Root layout: sidebar fixed, main scrolls
        ───────────────────────────────────────────────────────────────────── */
        #app-sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1050;
            /* no transform on desktop — always visible */
        }

        #app-main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─────────────────────────────────────────────────────────────────────
           Sidebar internals
        ───────────────────────────────────────────────────────────────────── */
        .sb-brand {
            background: #0d1525;
            border-bottom: 1px solid rgba(255,255,255,.07);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .sb-brand-icon {
            width: 38px; height: 38px;
            background: var(--brand);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 17px; flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(26,86,219,.45);
        }
        .sb-brand-name {
            font-size: 15px; font-weight: 700;
            color: #fff; letter-spacing: -.2px; line-height: 1.1;
        }
        .sb-brand-sub {
            font-size: 10px; color: rgba(255,255,255,.38);
            font-weight: 500; letter-spacing: .9px;
            text-transform: uppercase; margin-top: 1px;
        }

        .sb-nav {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0 20px;
        }
        .sb-nav::-webkit-scrollbar { width: 3px; }
        .sb-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 3px; }

        .sb-section {
            font-size: 10px; font-weight: 700;
            letter-spacing: 1.1px; text-transform: uppercase;
            color: rgba(255,255,255,.28);
            padding: 16px 20px 5px;
        }

        .sb-item { margin: 1px 8px; }
        .sb-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 13px;
            border-radius: 9px;
            color: rgba(255,255,255,.62);
            font-size: 13.5px; font-weight: 500;
            text-decoration: none;
            transition: background .16s, color .16s;
            white-space: nowrap;
        }
        .sb-link i { font-size: 14px; width: 18px; flex-shrink: 0; }
        .sb-link:hover  { background: rgba(255,255,255,.08); color: #fff; }
        .sb-link.active {
            background: var(--brand);
            color: #fff;
            box-shadow: 0 4px 14px rgba(26,86,219,.38);
        }

        .sb-footer {
            border-top: 1px solid rgba(255,255,255,.07);
            background: #0d1525;
            padding: 13px 16px;
            display: flex; align-items: center; gap: 10px;
            flex-shrink: 0;
        }
        .sb-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg,var(--brand),#818cf8);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 13px; font-weight: 700; flex-shrink: 0;
        }
        .sb-user-name { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sb-user-role { font-size: 11px; color: rgba(255,255,255,.38); text-transform: capitalize; }
        .sb-logout {
            margin-left: auto; flex-shrink: 0;
            background: none; border: none; padding: 5px;
            color: rgba(255,255,255,.35); font-size: 13px; cursor: pointer;
            transition: color .2s;
        }
        .sb-logout:hover { color: #ef4444; }

        /* ─────────────────────────────────────────────────────────────────────
           Topbar
        ───────────────────────────────────────────────────────────────────── */
        #app-topbar {
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
            display: flex; align-items: center;
            padding: 0 22px; gap: 12px;
            position: sticky; top: 0; z-index: 900;
            flex-shrink: 0;
        }

        /* Hamburger — hidden on desktop, shown on mobile via media query */
        #hamburger {
            display: none;
            align-items: center; justify-content: center;
            width: 36px; height: 36px;
            background: none; border: 1.5px solid var(--border);
            border-radius: 8px; cursor: pointer; color: var(--muted);
            transition: all .15s; flex-shrink: 0;
        }
        #hamburger:hover { background: var(--bg); color: var(--text); }

        .topbar-title {
            font-size: 15px; font-weight: 700; color: var(--text);
        }

        .topbar-right { margin-left: auto; position: relative; }
        .topbar-user-btn {
            display: flex; align-items: center; gap: 9px;
            cursor: pointer; padding: 5px 8px;
            border-radius: 9px; transition: background .15s;
        }
        .topbar-user-btn:hover { background: var(--bg); }
        .topbar-ava {
            width: 33px; height: 33px; border-radius: 50%;
            background: linear-gradient(135deg,var(--brand),#818cf8);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 13px;
        }
        .topbar-name { font-size: 13px; font-weight: 600; color: var(--text); line-height: 1.1; }
        .topbar-role { font-size: 11px; color: var(--muted); text-transform: capitalize; }
        .topbar-chevron { font-size: 10px; color: #9ca3af; }

        .topbar-dropdown {
            display: none;
            position: absolute; top: calc(100% + 8px); right: 0;
            background: #fff; border: 1px solid var(--border);
            border-radius: 11px; padding: 7px;
            min-width: 175px; box-shadow: 0 10px 28px rgba(0,0,0,.11);
            z-index: 9999;
        }
        .topbar-dropdown.open { display: block; }
        .topbar-dropdown a,
        .topbar-dropdown button {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 10px; border-radius: 7px;
            font-size: 13.5px; font-family: inherit; text-decoration: none;
            color: var(--text); background: none; border: none;
            width: 100%; cursor: pointer; transition: background .14s;
        }
        .topbar-dropdown a:hover,
        .topbar-dropdown button:hover { background: var(--bg); }
        .topbar-dropdown hr { border: none; border-top: 1px solid var(--border); margin: 5px 0; }
        .topbar-dropdown .dd-danger { color: #dc2626; }
        .topbar-dropdown .dd-danger:hover { background: #fef2f2; }

        /* ─────────────────────────────────────────────────────────────────────
           Content
        ───────────────────────────────────────────────────────────────────── */
        #app-content {
            flex: 1;
            padding: 22px 24px 32px;
        }

        .page-header {
            margin-bottom: 20px;
        }
        .page-header h1 {
            font-size: 20px; font-weight: 700; color: var(--text); margin: 0 0 4px;
        }
        .breadcrumb {
            display: flex; align-items: center; gap: 6px;
            list-style: none; margin: 0; padding: 0;
            font-size: 12.5px; color: var(--muted);
        }
        .breadcrumb li + li::before { content: '/'; color: #d1d5db; }
        .breadcrumb a { color: var(--brand); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .page-header-actions { margin-top: 12px; }

        /* ─────────────────────────────────────────────────────────────────────
           Footer
        ───────────────────────────────────────────────────────────────────── */
        #app-footer {
            background: #fff;
            border-top: 1px solid var(--border);
            padding: 12px 24px;
            font-size: 12px; color: #9ca3af;
        }

        /* ─────────────────────────────────────────────────────────────────────
           Overlay (mobile only)
        ───────────────────────────────────────────────────────────────────── */
        #sb-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.48);
            z-index: 1040;
        }
        #sb-overlay.on { display: block; }

        /* ─────────────────────────────────────────────────────────────────────
           Flash
        ───────────────────────────────────────────────────────────────────── */
        #flash-zone {
            position: fixed; top: 70px; right: 20px;
            z-index: 9999; width: 320px; max-width: calc(100vw - 40px);
        }
        .flash {
            display: flex; align-items: center; gap: 10px;
            padding: 13px 16px; border-radius: 10px;
            font-size: 13.5px; font-weight: 500; margin-bottom: 8px;
            box-shadow: 0 6px 20px rgba(0,0,0,.1);
            animation: fadeSlide .3s ease both;
        }
        @keyframes fadeSlide { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }
        .flash-ok  { background:#f0fdf4; border:1px solid #86efac; color:#15803d; }
        .flash-err { background:#fef2f2; border:1px solid #fca5a5; color:#dc2626; }

        /* ─────────────────────────────────────────────────────────────────────
           Shared UI components (cards, tables, pills, stat cards, etc.)
        ───────────────────────────────────────────────────────────────────── */
        .card { border:1px solid var(--border) !important; border-radius:var(--card-r) !important; box-shadow:0 1px 5px rgba(0,0,0,.05) !important; }
        .card-header { background:#fff !important; border-bottom:1px solid var(--border) !important; border-radius:var(--card-r) var(--card-r) 0 0 !important; padding:14px 20px !important; font-weight:600 !important; font-size:14px !important; color:var(--text) !important; }

        .stat-card { background:#fff; border:1px solid var(--border); border-radius:var(--card-r); padding:20px 22px; display:flex; align-items:center; gap:16px; box-shadow:0 1px 4px rgba(0,0,0,.04); transition:transform .2s,box-shadow .2s; }
        .stat-card:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,0,0,.08); }
        .stat-icon { width:50px; height:50px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
        .stat-icon.blue   { background:#eff6ff; color:#1d4ed8; }
        .stat-icon.red    { background:#fef2f2; color:#dc2626; }
        .stat-icon.yellow { background:#fffbeb; color:#d97706; }
        .stat-icon.purple { background:#f5f3ff; color:#7c3aed; }
        .stat-icon.green  { background:#f0fdf4; color:#15803d; }
        .stat-icon.orange { background:#fff7ed; color:#ea580c; }
        .stat-value { font-size:26px; font-weight:700; color:var(--text); line-height:1; }
        .stat-label { font-size:12.5px; color:var(--muted); margin-top:3px; font-weight:500; }
        .stat-badge { font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; display:inline-flex; align-items:center; gap:3px; margin-top:5px; }
        .stat-badge.up   { background:#f0fdf4; color:#15803d; }
        .stat-badge.down { background:#fef2f2; color:#dc2626; }

        .status-pill { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .status-pill::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; opacity:.7; }
        .status-available   { background:#f0fdf4; color:#15803d; }
        .status-occupied    { background:#fef2f2; color:#dc2626; }
        .status-reserved    { background:#fefce8; color:#a16207; }
        .status-cleaning    { background:#fff7ed; color:#c2410c; }
        .status-maintenance,.status-out_of_service { background:#f5f3ff; color:#6d28d9; }
        .status-confirmed   { background:#eff6ff; color:#1d4ed8; }
        .status-pending     { background:#fffbeb; color:#d97706; }
        .status-checked_in  { background:#f0fdf4; color:#15803d; }
        .status-checked_out { background:#f1f5f9; color:#475569; }
        .status-cancelled   { background:#fef2f2; color:#dc2626; }
        .status-draft       { background:#f1f5f9; color:#475569; }
        .status-issued      { background:#eff6ff; color:#1d4ed8; }
        .status-paid        { background:#f0fdf4; color:#15803d; }
        .status-void        { background:#fef2f2; color:#dc2626; }

        .room-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(80px,1fr)); gap:10px; }
        .room-tile { aspect-ratio:1; border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; font-weight:700; font-size:15px; border:2px solid transparent; cursor:pointer; transition:all .18s; text-decoration:none; gap:2px; }
        .room-tile .room-sub { font-size:10px; font-weight:500; }
        .room-tile:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,.1); }
        .room-tile.available  { background:#f0fdf4; border-color:#86efac; color:#15803d; }
        .room-tile.occupied   { background:#fef2f2; border-color:#fca5a5; color:#dc2626; }
        .room-tile.reserved   { background:#fefce8; border-color:#fde68a; color:#a16207; }
        .room-tile.cleaning   { background:#fff7ed; border-color:#fdba74; color:#c2410c; }
        .room-tile.maintenance,.room-tile.out_of_service { background:#f5f3ff; border-color:#c4b5fd; color:#6d28d9; }

        .table th { font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; border-top:none; background:#f9fafb; padding:10px 14px; }
        .table td { font-size:13.5px; padding:12px 14px; vertical-align:middle; border-color:var(--border); }
        .table tbody tr:hover { background:#f9fafb; }

        .btn { border-radius:8px !important; font-size:13.5px !important; font-weight:600 !important; font-family:inherit !important; }
        .btn-primary { background:var(--brand) !important; border-color:var(--brand) !important; box-shadow:0 2px 8px rgba(26,86,219,.22) !important; }
        .btn-primary:hover { background:var(--brand-dk) !important; border-color:var(--brand-dk) !important; }

        /* ─────────────────────────────────────────────────────────────────────
           RESPONSIVE
           Desktop (> 768px): sidebar always visible, no hamburger
           Mobile  (≤ 768px): sidebar hidden, hamburger shown
        ───────────────────────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            /* Show hamburger */
            #hamburger { display: flex; }

            /* Sidebar: off-screen by default, slides in when .open */
            #app-sidebar {
                transform: translateX(-100%);
                transition: transform .26s cubic-bezier(.4,0,.2,1);
            }
            #app-sidebar.open {
                transform: translateX(0);
                box-shadow: 6px 0 30px rgba(0,0,0,.25);
            }

            /* Content: full width */
            #app-main { margin-left: 0; }

            #app-content { padding: 14px 14px 24px; }
        }

        @media (max-width: 480px) {
            .topbar-name, .topbar-role { display: none; }
            #app-content { padding: 10px 10px 20px; }
            #flash-zone { right: 10px; width: calc(100vw - 20px); }
        }
    </style>

    @stack('styles')
</head>
<body class="hold-transition">

    {{-- Mobile overlay --}}
    <div id="sb-overlay"></div>

    {{-- ══════════ SIDEBAR ══════════ --}}
    <aside id="app-sidebar">

        <a href="{{ route('dashboard') }}" class="sb-brand">
            <div class="sb-brand-icon"><i class="fas fa-hotel"></i></div>
            <div>
                <div class="sb-brand-name">{{ \App\Models\Setting::hotelName() }}</div>
                <div class="sb-brand-sub">{{ \App\Models\Setting::hotelTagline() }}</div>
            </div>
        </a>

        <nav class="sb-nav">

            <div class="sb-section">Main Menu</div>

            <div class="sb-item">
                <a href="{{ route('dashboard') }}"
                   class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
            <div class="sb-item">
                <a href="{{ route('rooms.index') }}"
                   class="sb-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                    <i class="fas fa-door-open"></i> Rooms
                </a>
            </div>
            <div class="sb-item">
                <a href="{{ route('bookings.index') }}"
                   class="sb-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i> Bookings
                </a>
            </div>
            <div class="sb-item">
                <a href="{{ route('check-ins.index') }}"
                   class="sb-link {{ request()->routeIs('check-ins.*') ? 'active' : '' }}">
                    <i class="fas fa-sign-in-alt"></i> Check-In
                </a>
            </div>
            <div class="sb-item">
                <a href="{{ route('check-outs.index') }}"
                   class="sb-link {{ request()->routeIs('check-outs.*') ? 'active' : '' }}">
                    <i class="fas fa-sign-out-alt"></i> Check-Out
                </a>
            </div>
            <div class="sb-item">
                <a href="{{ route('payments.index') }}"
                   class="sb-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
            </div>
            <div class="sb-item">
                <a href="{{ route('invoices.index') }}"
                   class="sb-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i> Invoices
                </a>
            </div>
            <div class="sb-item">
                <a href="{{ route('customers.index') }}"
                   class="sb-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Customers
                </a>
            </div>
            <div class="sb-item">
                <a href="{{ route('extra-services.index') }}"
                   class="sb-link {{ request()->routeIs('extra-services.*') ? 'active' : '' }}">
                    <i class="fas fa-concierge-bell"></i> Extra Services
                </a>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="sb-section" style="margin-top:6px;">Analytics</div>
                <div class="sb-item">
                    <a href="{{ route('reports.bookings.monthly') }}"
                       class="sb-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Reports &amp; Analytics
                    </a>
                </div>

                <div class="sb-section" style="margin-top:6px;">Admin</div>
                <div class="sb-item">
                    <a href="{{ route('users.index') }}"
                       class="sb-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-user-cog"></i> Staff Management
                    </a>
                </div>
                <div class="sb-item">
                    <a href="{{ route('settings.index') }}"
                       class="sb-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            @endif

        </nav>

        <div class="sb-footer">
            <div class="sb-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="sb-user-name">{{ auth()->user()->name }}</div>
                <div class="sb-user-role">{{ auth()->user()->role }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sb-logout" title="Sign out">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>

    </aside>

    {{-- ══════════ MAIN ══════════ --}}
    <div id="app-main">

        {{-- Topbar --}}
        <header id="app-topbar">
            {{-- Hamburger: CSS hides it on desktop --}}
            <button id="hamburger" aria-label="Open menu">
                <i class="fas fa-bars"></i>
            </button>

            <span class="topbar-title">@yield('page-title', 'Dashboard')</span>

            <div class="topbar-right">
                <div class="topbar-user-btn" id="topbar-btn">
                    <div class="topbar-ava">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="d-none d-sm-block">
                        <div class="topbar-name">{{ auth()->user()->name }}</div>
                        <div class="topbar-role">{{ auth()->user()->role }}</div>
                    </div>
                    <i class="fas fa-chevron-down topbar-chevron"></i>
                </div>

                <div class="topbar-dropdown" id="topbar-dd">
                    <a href="{{ route('profile') }}">
                        <i class="fas fa-user-circle" style="color:#9ca3af;width:15px;"></i> My Profile
                    </a>
                    <hr>
                    <form action="{{ route('logout') }}" method="POST" style="margin:0">
                        @csrf
                        <button type="submit" class="dd-danger">
                            <i class="fas fa-sign-out-alt" style="width:15px;"></i> Sign out
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Flash --}}
        <div id="flash-zone">
            @if(session('success'))
                <div class="flash flash-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
        </div>

        {{-- Page content --}}
        <div id="app-content">

            {{-- Page header --}}
            <div class="page-header">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div>
                        <h1>@yield('page-title', 'Dashboard')</h1>
                        <ol class="breadcrumb">
                            <li><a href="{{ route('dashboard') }}">Home</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                    <div>@yield('header-actions')</div>
                </div>
            </div>

            @yield('content')

        </div>

        <footer id="app-footer">
            &copy; {{ date('Y') }} {{ \App\Models\Setting::hotelName() }}. All rights reserved.
        </footer>

    </div>{{-- /#app-main --}}

<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script>
(function () {

    // Flash auto-dismiss
    document.querySelectorAll('.flash').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 420);
        }, 4000);
    });

    window.csrfToken = '{{ csrf_token() }}';

    // ── Mobile sidebar ──────────────────────────────────────────────────────
    var sidebar   = document.getElementById('app-sidebar');
    var overlay   = document.getElementById('sb-overlay');
    var hamburger = document.getElementById('hamburger');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('on');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('on');
        document.body.style.overflow = '';
    }

    hamburger.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);

    // Close on nav link tap (mobile)
    document.querySelectorAll('.sb-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) closeSidebar();
    });

    // ── Topbar dropdown ─────────────────────────────────────────────────────
    var btn = document.getElementById('topbar-btn');
    var dd  = document.getElementById('topbar-dd');

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        dd.classList.toggle('open');
    });
    document.addEventListener('click', function () {
        dd.classList.remove('open');
    });

}());
</script>

@stack('scripts')
</body>
</html>