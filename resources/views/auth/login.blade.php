<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Setting::hotelName() }} – Sign In</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand:    #1a56db;
            --brand-dk: #1447b5;
            --accent:   #f59e0b;
            --bg:       #eef2ff;
            --text:     #111827;
            --muted:    #6b7280;
            --border:   #e5e7eb;
            --danger:   #ef4444;
            --radius:   14px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(26,86,219,.18) 0%, transparent 70%),
                linear-gradient(165deg, #eef2ff 0%, #e0e7ff 100%);
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            width: 600px; height: 600px;
            top: -200px; right: -200px;
            background: radial-gradient(circle, rgba(26,86,219,.07) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            bottom: -150px; left: -150px;
            background: radial-gradient(circle, rgba(245,158,11,.07) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .wrap {
            position: relative; z-index: 1;
            width: 100%; max-width: 440px;
            padding: 20px;
            animation: fadeUp .5s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        /* ── brand ── */
        .brand { text-align: center; margin-bottom: 28px; }
        .brand-logo {
            display: inline-flex; align-items: center; gap: 10px;
            margin-bottom: 6px;
        }
        .brand-icon {
            width: 46px; height: 46px;
            background: var(--brand);
            border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 20px;
            box-shadow: 0 8px 24px rgba(26,86,219,.35);
        }
        .brand-name { font-size: 24px; font-weight: 700; color: var(--text); letter-spacing: -.5px; }
        .brand-name em { font-style: normal; color: var(--brand); }
        .brand-tag {
            font-size: 11.5px; font-weight: 600; letter-spacing: 1.2px;
            text-transform: uppercase; color: var(--muted);
        }
        /* ── card ── */
        .card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: 0 24px 64px rgba(26,86,219,.13), 0 1px 3px rgba(0,0,0,.05);
            border: 1px solid rgba(255,255,255,.9);
            overflow: hidden;
        }
        .card-strip {
            height: 3px;
            background: linear-gradient(90deg, var(--brand) 0%, #818cf8 60%, var(--accent) 100%);
        }
        .card-body { padding: 36px 40px 40px; }
        .card-title { font-size: 19px; font-weight: 700; color: var(--text); margin-bottom: 3px; }
        .card-sub   { font-size: 13.5px; color: var(--muted); margin-bottom: 26px; }
        /* ── alert ── */
        .alert {
            display: flex; align-items: center; gap: 9px;
            padding: 11px 14px;
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 9px; margin-bottom: 20px;
            font-size: 13.5px; color: #dc2626;
        }
        /* ── form ── */
        .fg { margin-bottom: 16px; }
        label {
            display: block;
            font-size: 13px; font-weight: 600; color: #374151;
            margin-bottom: 6px;
        }
        .input-wrap { position: relative; }
        .inp-icon {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; font-size: 13.5px;
            pointer-events: none;
        }
        input[type=email], input[type=password], input[type=text] {
            width: 100%;
            padding: 11px 38px 11px 38px;
            background: #f9fafb;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-family: inherit; font-size: 14px; color: var(--text);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        input:focus {
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(26,86,219,.1);
        }
        input.err { border-color: var(--danger); background: #fff5f5; }
        .err-msg { font-size: 12px; color: var(--danger); margin-top: 4px; display: block; }
        .pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #9ca3af; font-size: 13.5px; padding: 4px;
            transition: color .2s;
        }
        .pw-toggle:hover { color: var(--brand); }
        /* ── remember row ── */
        .row-rem {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 22px;
        }
        .row-rem input[type=checkbox] {
            width: 15px; height: 15px;
            accent-color: var(--brand);
            padding: 0; border-radius: 4px;
        }
        .row-rem label { margin: 0; font-size: 13px; font-weight: 500; color: var(--muted); cursor: pointer; }
        /* ── button ── */
        .btn-submit {
            width: 100%; padding: 13px;
            background: var(--brand); color: #fff;
            font-family: inherit; font-size: 15px; font-weight: 600;
            border: none; border-radius: 9px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 18px rgba(26,86,219,.3);
            transition: background .2s, transform .15s, box-shadow .2s;
            letter-spacing: .1px;
        }
        .btn-submit:hover {
            background: var(--brand-dk);
            box-shadow: 0 6px 24px rgba(26,86,219,.4);
            transform: translateY(-1px);
        }
        .btn-submit:active { transform: translateY(0); }
        /* ── footer ── */
        .foot { text-align: center; margin-top: 22px; font-size: 12px; color: #9ca3af; }

        @media (max-width: 480px) { .card-body { padding: 28px 24px 32px; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <div class="brand-logo">
            <div class="brand-icon"><i class="fas fa-hotel"></i></div>
            <span class="brand-name">{{ \App\Models\Setting::hotelName() }}</span>
        </div>
        <p class="brand-tag">{{ \App\Models\Setting::hotelTagline() }}</p>
    </div>

    <div class="card">
        <div class="card-strip"></div>
        <div class="card-body">
            <h1 class="card-title">Welcome back</h1>
            <p class="card-sub">Sign in to your staff account to continue</p>

            @if ($errors->any())
                <div class="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" novalidate>
                @csrf

                <div class="fg">
                    <label for="email">Email address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope inp-icon"></i>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="staff@hotel.com"
                               class="{{ $errors->has('email') ? 'err' : '' }}"
                               autocomplete="email" required>
                    </div>
                    @error('email') <span class="err-msg">{{ $message }}</span> @enderror
                </div>

                <div class="fg">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock inp-icon"></i>
                        <input type="password" id="password" name="password"
                               placeholder="••••••••"
                               class="{{ $errors->has('password') ? 'err' : '' }}"
                               autocomplete="current-password" required>
                        <button type="button" class="pw-toggle" onclick="togglePw()">
                            <i class="fas fa-eye" id="pw-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="row-rem">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Keep me signed in</label>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign in to Dashboard
                </button>
            </form>
        </div>
    </div>
    <p class="foot">&copy; {{ date('Y') }} {{ \App\Models\Setting::hotelName() }}. All rights reserved.</p>
</div>

<script>
function togglePw() {
    const i = document.getElementById('password');
    const ic = document.getElementById('pw-icon');
    i.type = i.type === 'password' ? 'text' : 'password';
    ic.classList.toggle('fa-eye');
    ic.classList.toggle('fa-eye-slash');
}
</script>
</body>
</html>