<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; --ink:#1f2933; --muted:#65758b; --line:#d9e2ec; --panel:#ffffff; --bg:#f6f8fb; --accent:#0f766e; --accent-2:#b45309; --danger:#b91c1c; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Arial, Helvetica, sans-serif; background:var(--bg); color:var(--ink); font-size:14px; }
        header { background:#ffffff; border-bottom:1px solid var(--line); position:sticky; top:0; z-index:5; }
        .bar { max-width:1180px; margin:0 auto; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .brand { font-weight:700; font-size:18px; color:#0f172a; }
        nav { display:flex; flex-wrap:wrap; gap:8px; }
        nav a, .btn { border:1px solid var(--line); background:#fff; color:var(--ink); padding:9px 12px; border-radius:6px; text-decoration:none; cursor:pointer; display:inline-flex; align-items:center; min-height:38px; }
        nav a.active, .btn.primary { background:var(--accent); border-color:var(--accent); color:#fff; }
        main { max-width:1180px; margin:0 auto; padding:22px 20px 40px; }
        h1 { font-size:24px; margin:0 0 18px; }
        h2 { font-size:17px; margin:0 0 12px; }
        .grid { display:grid; gap:14px; }
        .stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .two { grid-template-columns: 1.2fr .8fr; }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:16px; }
        .metric { color:var(--muted); font-size:13px; }
        .metric strong { display:block; color:var(--ink); font-size:28px; margin-top:6px; }
        table { width:100%; border-collapse:collapse; background:#fff; border:1px solid var(--line); border-radius:8px; overflow:hidden; }
        th, td { text-align:left; padding:11px 12px; border-bottom:1px solid var(--line); vertical-align:top; }
        th { font-size:12px; color:var(--muted); text-transform:uppercase; background:#f9fafb; }
        tr:last-child td { border-bottom:0; }
        input, select, textarea { width:100%; border:1px solid var(--line); border-radius:6px; min-height:40px; padding:9px 10px; font:inherit; background:#fff; }
        textarea { min-height:96px; resize:vertical; }
        label { display:grid; gap:6px; color:var(--muted); font-size:13px; }
        form.inline { display:inline; }
        .form-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px; }
        .actions { display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-top:14px; }
        .status { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:10px 12px; border-radius:6px; margin-bottom:14px; }
        .error { color:var(--danger); font-size:12px; margin-top:4px; }
        .badge { display:inline-flex; border-radius:999px; padding:4px 8px; background:#eef2ff; color:#3730a3; font-size:12px; }
        .danger { color:var(--danger); }
        .muted { color:var(--muted); }
        @media (max-width: 760px) {
            .bar { align-items:flex-start; flex-direction:column; }
            .stats, .two, .form-grid { grid-template-columns:1fr; }
            table { display:block; overflow-x:auto; white-space:nowrap; }
        }
    </style>
</head>
<body>
<header>
    <div class="bar">
        <div class="brand">Nopal A1 Inventory</div>
        <nav>
            <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Dashboard</a>
            <a href="{{ route('products.index') }}" @class(['active' => request()->routeIs('products.*')])>Pencatatan</a>
            <a href="{{ route('reports.index') }}" @class(['active' => request()->routeIs('reports.*')])>Cetak Laporan</a>
            <a href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])>Notif & Komunikasi</a>
        </nav>
    </div>
</header>
<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
