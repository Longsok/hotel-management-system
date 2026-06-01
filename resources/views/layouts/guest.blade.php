<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Book Your Stay') — {{ \App\Models\Setting::hotelName() }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand:    #1a56db;
            --brand-dk: #1447b5;
            --gold:     #d97706;
            --text:     #111827;
            --muted:    #6b7280;
            --border:   #e5e7eb;
            --bg:       #f9fafb;
            --card-r:   14px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        a { color: inherit; text-decoration: none; }

        /* Nav */
        .g-nav {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 8px rgba(0,0,0,.06);
        }
        .g-nav-brand { display: flex; align-items: center; gap: 10px; }
        .g-nav-icon {
            width: 38px; height: 38px;
            background: var(--brand); border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 17px;
            box-shadow: 0 3px 10px rgba(26,86,219,.35);
        }
        .g-nav-name  { font-size: 17px; font-weight: 700; color: var(--text); }
        .g-nav-sub   { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; }
        .g-nav-link  {
            padding: 9px 20px; border-radius: 8px;
            background: var(--brand); color: #fff;
            font-size: 13.5px; font-weight: 600;
            box-shadow: 0 2px 8px rgba(26,86,219,.3);
            transition: background .15s;
        }
        .g-nav-link:hover { background: var(--brand-dk); }

        /* Content */
        .g-content { min-height: calc(100vh - 64px - 60px); }

        /* Footer */
        .g-footer {
            background: #111827;
            padding: 24px 32px;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
        }
        .g-footer-left { font-size: 13px; color: rgba(255,255,255,.45); }
        .g-footer-right { display: flex; gap: 20px; }
        .g-footer-right a { font-size: 12.5px; color: rgba(255,255,255,.4); transition: color .15s; }
        .g-footer-right a:hover { color: #fff; }

        @media (max-width: 640px) {
            .g-nav { padding: 0 16px; }
            .g-nav-sub { display: none; }
            .g-footer { padding: 20px 16px; flex-direction: column; align-items: flex-start; }
        }
    </style>

    @stack('styles')
</head>
<body>

<nav class="g-nav">
    <div class="g-nav-brand">
        <div class="g-nav-icon"><i class="fas fa-hotel"></i></div>
        <div>
            <div class="g-nav-name">{{ \App\Models\Setting::hotelName() }}</div>
            <div class="g-nav-sub">{{ \App\Models\Setting::hotelTagline() }}</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        @auth('customer')
            <a href="{{ route('guest.history') }}"
               style="padding:8px 16px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-calendar-check"></i> My Bookings
            </a>
            <form action="{{ route('guest.logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit"
                        style="padding:8px 16px;border-radius:8px;border:none;background:#f3f4f6;color:#374151;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </button>
            </form>
        @else
            <a href="{{ route('guest.login') }}"
               style="padding:8px 16px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:600;">
                Sign In
            </a>
            <a href="{{ route('guest.register') }}" class="g-nav-link">
                Create Account
            </a>
        @endauth
    </div>
</nav>

<div class="g-content">
    @yield('content')
</div>

<footer class="g-footer">
    <div class="g-footer-left">
        &copy; {{ date('Y') }} {{ \App\Models\Setting::hotelName() }}. All rights reserved.
    </div>
    <div class="g-footer-right">
        @if(\App\Models\Setting::hotelEmail())
            <a href="mailto:{{ \App\Models\Setting::hotelEmail() }}">
                <i class="fas fa-envelope" style="margin-right:5px;"></i>{{ \App\Models\Setting::hotelEmail() }}
            </a>
        @endif
        @if(\App\Models\Setting::hotelAddress())
            <a href="#">
                <i class="fas fa-map-marker-alt" style="margin-right:5px;"></i>{{ \App\Models\Setting::hotelAddress() }}
            </a>
        @endif
    </div>
</footer>

@stack('scripts')
</body>
</html>
