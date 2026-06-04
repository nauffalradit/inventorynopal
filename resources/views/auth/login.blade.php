<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; --ink:#1f2933; --muted:#65758b; --line:#d9e2ec; --panel:#ffffff; --bg:#f6f8fb; --accent:#0f766e; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; font-family:Arial, Helvetica, sans-serif; background:var(--bg); color:var(--ink); font-size:14px; padding:20px; }
        .login { width:min(100%, 420px); background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:24px; }
        h1 { font-size:24px; margin:0 0 8px; }
        p { color:var(--muted); line-height:1.5; margin:0 0 18px; }
        a { width:100%; min-height:42px; display:inline-flex; align-items:center; justify-content:center; gap:10px; border:1px solid var(--accent); background:var(--accent); color:#fff; border-radius:6px; text-decoration:none; font-weight:700; }
        .status { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:10px 12px; border-radius:6px; margin-bottom:14px; }
    </style>
</head>
<body>
    <section class="login">
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <h1>Nopal A1 Inventory</h1>
        <p>Masuk untuk mengakses dashboard inventory.</p>
        <a href="{{ route('auth.google.redirect') }}">Masuk dengan Google</a>
    </section>
</body>
</html>
