<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · Inventory</title>
    <style>
        :root { --navy:#17213a; --navy-2:#222e4b; --accent:#5b5ce2; --accent-soft:#eeefff; --ink:#19233d; --muted:#7c879d; --line:#e8ebf2; --surface:#fff; --canvas:#f7f8fc; --danger:#d6485d; --success:#1a9b73; }
        * { box-sizing:border-box; }
        body { margin:0; min-width:320px; font:14px/1.45 Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color:var(--ink); background:var(--canvas); }
        .app-shell { min-height:100vh; display:flex; }
        .sidebar { width:260px; flex:0 0 260px; min-height:100vh; position:sticky; top:0; align-self:flex-start; display:flex; flex-direction:column; padding:26px 16px 18px; color:#dce4f4; background:var(--navy); }
        .brand { display:flex; align-items:center; gap:11px; padding:0 12px 30px; color:#fff; text-decoration:none; font-weight:750; font-size:18px; letter-spacing:-.35px; }
        .brand-mark { width:32px; height:32px; border-radius:10px; display:grid; place-items:center; background:linear-gradient(145deg, #8584ff, #5859e7); box-shadow:0 8px 20px rgba(91,92,226,.35); }
        .brand-mark svg { width:18px; height:18px; }
        .nav-label { padding:0 12px 9px; color:#8290ad; font-size:10px; font-weight:750; letter-spacing:.12em; text-transform:uppercase; }
        nav { display:grid; gap:5px; }
        nav a { display:flex; align-items:center; gap:12px; padding:11px 12px; color:#aeb9cf; border-radius:9px; text-decoration:none; font-weight:600; transition:.18s ease; }
        nav a:hover { color:#fff; background:rgba(255,255,255,.06); }
        nav a.active { color:#fff; background:var(--accent); box-shadow:0 8px 18px rgba(54,55,177,.3); }
        .nav-icon { width:18px; height:18px; flex:0 0 18px; stroke:currentColor; stroke-width:1.8; fill:none; }
        .sidebar-footer { margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,.1); }
        .user-card { display:flex; align-items:center; gap:10px; padding:0 8px 14px; }
        .avatar { width:34px; height:34px; display:grid; place-items:center; border-radius:50%; color:#fff; background:#354361; font-weight:750; }
        .user-name { overflow:hidden; color:#fff; font-size:13px; font-weight:650; text-overflow:ellipsis; white-space:nowrap; }
        .user-role { color:#8997b3; font-size:11px; }
        .logout { width:100%; display:flex; align-items:center; gap:10px; padding:10px 12px; border:0; border-radius:8px; color:#aeb9cf; background:transparent; cursor:pointer; font:inherit; font-weight:600; text-align:left; }
        .logout:hover { color:#fff; background:rgba(255,255,255,.06); }
        .main-area { min-width:0; flex:1; }
        .topbar { height:78px; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:0 38px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.86); }
        .crumb { color:var(--muted); font-size:13px; }
        .crumb strong { color:var(--ink); font-weight:700; }
        .topbar-right { display:flex; align-items:center; gap:16px; }
        .date { color:var(--muted); font-size:12px; }
        .bell { width:36px; height:36px; display:grid; place-items:center; border:1px solid var(--line); border-radius:10px; color:#63708a; background:#fff; }
        main { max-width:1480px; margin:0 auto; padding:34px 38px 46px; }
        h1 { margin:0 0 24px; color:var(--ink); font-size:27px; letter-spacing:-.65px; line-height:1.2; }
        h2 { margin:0 0 15px; color:var(--ink); font-size:15px; letter-spacing:-.15px; }
        .grid { display:grid; gap:18px; }
        .stats { grid-template-columns:repeat(4,minmax(0,1fr)); }
        .two { grid-template-columns:minmax(0,1.3fr) minmax(300px,.7fr); }
        .card { padding:20px; border:1px solid var(--line); border-radius:14px; background:var(--surface); box-shadow:0 2px 4px rgba(29,42,70,.015); }
        .metric { position:relative; overflow:hidden; min-height:119px; color:var(--muted); font-size:13px; font-weight:600; }
        .metric::after { content:""; position:absolute; right:-16px; bottom:-25px; width:82px; height:82px; border-radius:50%; background:var(--accent-soft); }
        .metric strong { position:relative; z-index:1; display:block; margin-top:8px; color:var(--ink); font-size:29px; letter-spacing:-1px; }
        .metric:nth-child(2)::after { background:#e9f8f3; }.metric:nth-child(3)::after { background:#fff4e6; }.metric:nth-child(4)::after { background:#f8edff; }
        .actions { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin-top:18px; }
        .btn { min-height:39px; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:8px 13px; border:1px solid var(--line); border-radius:8px; color:#536078; background:#fff; cursor:pointer; text-decoration:none; font:inherit; font-size:13px; font-weight:650; transition:.18s ease; }
        .btn:hover { border-color:#cdd3e1; transform:translateY(-1px); box-shadow:0 3px 10px rgba(32,43,66,.06); }.btn.primary { border-color:var(--accent); color:#fff; background:var(--accent); box-shadow:0 6px 14px rgba(91,92,226,.2); }
        table { width:100%; border-collapse:separate; border-spacing:0; overflow:hidden; border:1px solid var(--line); border-radius:10px; background:#fff; }
        th,td { padding:13px 14px; border-bottom:1px solid #edf0f5; text-align:left; vertical-align:middle; } th { color:#8a94a8; background:#fafbfe; font-size:10px; font-weight:750; letter-spacing:.08em; text-transform:uppercase; } tr:last-child td { border-bottom:0; } tbody tr:hover td { background:#fcfcff; }
        input,select,textarea { width:100%; min-height:41px; padding:9px 11px; border:1px solid #dfe4ed; border-radius:8px; outline:0; color:var(--ink); background:#fff; font:inherit; } input:focus,select:focus,textarea:focus { border-color:#9292f1; box-shadow:0 0 0 3px rgba(91,92,226,.1); } textarea { min-height:96px; resize:vertical; }
        label { display:grid; gap:7px; color:#65728a; font-size:12px; font-weight:650; }.form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }.inline { display:inline; }.status { margin-bottom:18px; padding:11px 13px; border:1px solid #a9e6cc; border-radius:10px; color:#137354; background:#ecfdf6; }.error { margin-top:4px; color:var(--danger); font-size:12px; }.badge { display:inline-flex; align-items:center; padding:4px 9px; border-radius:999px; color:#5656bd; background:#f0f0ff; font-size:11px; font-weight:700; text-transform:capitalize; }.danger { color:var(--danger); }.muted { color:var(--muted); }
        @media (max-width:1050px) { .sidebar { width:218px; flex-basis:218px; }.topbar { padding:0 26px; } main { padding:28px 26px 40px; }.stats { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:760px) { .app-shell { display:block; }.sidebar { width:100%; min-height:0; position:relative; padding:16px; }.brand { padding:0 5px 14px; }.nav-label,.sidebar-footer { display:none; } nav { display:flex; overflow:auto; gap:6px; } nav a { flex:0 0 auto; padding:8px 10px; font-size:12px; }.nav-icon { width:16px; height:16px; }.topbar { height:58px; padding:0 18px; }.date { display:none; } main { padding:24px 18px 36px; } h1 { font-size:23px; }.stats,.two,.form-grid { grid-template-columns:1fr; }.card { padding:16px; } table { display:block; overflow-x:auto; white-space:nowrap; } }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark"><svg viewBox="0 0 24 24"><path d="M5 19V9m7 10V5m7 14v-7" stroke="white" stroke-linecap="round" stroke-width="2"/></svg></span>Nopal A1</a>
        <div class="nav-label">Menu utama</div>
        <nav>
            <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])><svg class="nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a>
            <a href="{{ route('products.index') }}" @class(['active' => request()->routeIs('products.*')])><svg class="nav-icon" viewBox="0 0 24 24"><path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="m3 12 9 4 9-4M3 17l9 4 9-4"/></svg>Pencatatan</a>
            <a href="{{ route('reports.index') }}" @class(['active' => request()->routeIs('reports.*')])><svg class="nav-icon" viewBox="0 0 24 24"><path d="M6 2h9l3 3v17H6z"/><path d="M9 13h6M9 17h6M9 9h2"/></svg>Cetak Laporan</a>
            <a href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])><svg class="nav-icon" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>Notif & Komunikasi</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-card"><div class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'U', 0, 1)) }}</div><div><div class="user-name">{{ auth()->user()?->name ?? 'Pengguna' }}</div><div class="user-role">Administrator</div></div></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5M15 12H3M21 3v18"/></svg>Logout</button></form>
        </div>
    </aside>
    <div class="main-area">
        <header class="topbar"><div class="crumb">Inventory / <strong>{{ request()->routeIs('dashboard') ? 'Dashboard' : 'Workspace' }}</strong></div><div class="topbar-right"><span class="date">{{ now()->translatedFormat('l, d F Y') }}</span><span class="bell"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></span></div></header>
        <main>
            @if (session('status'))<div class="status">{{ session('status') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
