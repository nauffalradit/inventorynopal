<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · Inventory</title>
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="prefetch" href="{{ route('dashboard') }}">
    <link rel="prefetch" href="{{ route('products.index') }}">
    <style>
        * { box-sizing:border-box; }
        body { margin:0; min-width:320px; font:var(--text-md)/1.45 Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color:var(--ink); background:var(--canvas); }
        .app-shell { min-height:100vh; display:flex; }
        .sidebar { width:260px; flex:0 0 260px; min-height:100vh; position:sticky; top:0; align-self:flex-start; display:flex; flex-direction:column; padding:26px 16px 18px; color:var(--sidebar-ink); background:var(--navy); }
        .brand { display:flex; align-items:center; gap:11px; padding:0 12px 30px; color:var(--surface); text-decoration:none; font-weight:750; font-size:var(--text-xl); letter-spacing:-.35px; }
        .brand-mark { width:32px; height:32px; border-radius:var(--radius-lg); display:grid; place-items:center; background:linear-gradient(145deg, var(--brand-grad-1), var(--brand-grad-2)); box-shadow:0 8px 20px rgba(91,92,226,.35); }
        .brand-mark svg { width:18px; height:18px; }
        .nav-label { padding:0 12px 9px; color:var(--nav-label); font-size:10px; font-weight:750; letter-spacing:.12em; text-transform:uppercase; }
        nav { display:grid; gap:5px; }
        nav a { display:flex; align-items:center; gap:12px; padding:11px 12px; color:var(--nav-link); border-radius:var(--radius-md); text-decoration:none; font-weight:600; transition:.18s ease; }
        nav a:hover { color:var(--surface); background:rgba(255,255,255,.06); }
        nav a.active { color:var(--surface); background:var(--accent); box-shadow:0 8px 18px rgba(54,55,177,.3); }
        .nav-icon { width:18px; height:18px; flex:0 0 18px; stroke:currentColor; stroke-width:1.8; fill:none; }
        .sidebar-footer { margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,.1); }
        .user-card { display:flex; align-items:center; gap:10px; padding:0 8px 14px; }
        .avatar { width:34px; height:34px; display:grid; place-items:center; border-radius:50%; color:var(--surface); background:var(--avatar-bg); font-weight:750; }
        .user-name { overflow:hidden; color:var(--surface); font-size:var(--text-base); font-weight:650; text-overflow:ellipsis; white-space:nowrap; }
        .user-role { color:var(--role-ink); font-size:var(--text-xs); }
        .logout { width:100%; display:flex; align-items:center; gap:10px; padding:10px 12px; border:0; border-radius:var(--radius-md); color:var(--nav-link); background:transparent; cursor:pointer; font:inherit; font-weight:600; text-align:left; }
        .logout:hover { color:var(--surface); background:rgba(255,255,255,.06); }
        .main-area { min-width:0; flex:1; }
        .topbar { height:78px; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:0 38px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.86); }
        .crumb { color:var(--muted); font-size:var(--text-base); }
        .crumb strong { color:var(--ink); font-weight:700; }
        .topbar-right { display:flex; align-items:center; gap:16px; }
        .date { color:var(--muted); font-size:var(--text-sm); }
        .bell { width:36px; height:36px; display:grid; place-items:center; border:1px solid var(--line); border-radius:var(--radius-lg); color:var(--icon-ink); background:var(--surface); }
        main { max-width:1480px; margin:0 auto; padding:34px 38px 46px; }
        h1 { margin:0 0 24px; color:var(--ink); font-size:var(--text-3xl); letter-spacing:-.65px; line-height:1.2; }
        h2 { margin:0 0 15px; color:var(--ink); font-size:var(--text-lg); letter-spacing:-.15px; }
        .grid { display:grid; gap:18px; }
        .stats { grid-template-columns:repeat(4,minmax(0,1fr)); }
        .two { grid-template-columns:minmax(0,1.3fr) minmax(300px,.7fr); }
        .card { padding:var(--space-5); border:1px solid var(--line); border-radius:var(--radius-xl); background:var(--surface); box-shadow:0 2px 4px rgba(29,42,70,.015); }
        .metric { position:relative; overflow:hidden; min-height:119px; color:var(--muted); font-size:var(--text-base); font-weight:600; }
        .metric::after { content:""; position:absolute; right:-16px; bottom:-25px; width:82px; height:82px; border-radius:50%; background:var(--accent-soft); }
        .metric strong { position:relative; z-index:1; display:block; margin-top:8px; color:var(--ink); font-size:var(--text-3xl); letter-spacing:-1px; }
        .metric:nth-child(2)::after { background:var(--metric-tint-2); }.metric:nth-child(3)::after { background:var(--metric-tint-3); }.metric:nth-child(4)::after { background:var(--metric-tint-4); }
        .actions { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin-top:18px; }
        .btn { min-height:39px; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:8px 13px; border:1px solid var(--line); border-radius:var(--radius-md); color:var(--btn-ink); background:var(--surface); cursor:pointer; text-decoration:none; font:inherit; font-size:var(--text-base); font-weight:650; transition:.18s ease; }
        .btn:hover { border-color:var(--btn-hover-border); transform:translateY(-1px); box-shadow:0 3px 10px rgba(32,43,66,.06); }        .btn.primary { border-color:var(--accent); color:var(--surface); background:var(--accent); box-shadow:0 6px 14px rgba(91,92,226,.2); }
        .btn.ghost { background:transparent; }
        .btn.ghost:hover { border-color:var(--accent); color:var(--accent); box-shadow:none; transform:none; }
        .btn.ghost-danger { background:transparent; }
        .btn.ghost-danger:hover { border-color:var(--danger); color:var(--danger); box-shadow:none; transform:none; }
        .stats-strip { display:flex; flex-wrap:wrap; gap:var(--space-5); padding:22px 26px; border-bottom:1px solid var(--line); }
        .stat-item small { display:block; color:var(--muted); font-size:var(--text-sm); }
        .stat-item b { font-size:var(--text-2xl); letter-spacing:-.5px; }
        .rows { display:grid; }
        .row-item { display:flex; align-items:center; flex-wrap:wrap; gap:16px; padding:16px 0; border-bottom:1px solid var(--line); }
        .row-item:last-child { border-bottom:0; }
        .row-grow { flex:1; min-width:0; display:grid; }
        .row-grow b { font-weight:600; overflow-wrap:anywhere; }
        .row-grow .muted { font-size:var(--text-base); }
        .row-amt { font-weight:600; white-space:nowrap; }
        .row-acts { display:flex; gap:8px; }
        .rows-empty { padding:40px 20px; text-align:center; }
        table { width:100%; border-collapse:separate; border-spacing:0; overflow:hidden; border:1px solid var(--line); border-radius:var(--radius-lg); background:var(--surface); }
        th,td { padding:13px 14px; border-bottom:1px solid var(--table-line); text-align:left; vertical-align:middle; } th { color:var(--table-head-ink); background:var(--table-head-bg); font-size:10px; font-weight:750; letter-spacing:.08em; text-transform:uppercase; } tr:last-child td { border-bottom:0; } tbody tr:hover td { background:var(--table-hover); }
        input,select,textarea { width:100%; min-height:41px; padding:9px 11px; border:1px solid var(--line-strong); border-radius:var(--radius-md); outline:0; color:var(--ink); background:var(--surface); font:inherit; } input:focus,select:focus,textarea:focus { border-color:var(--focus-ring); box-shadow:0 0 0 3px rgba(91,92,226,.1); } textarea { min-height:96px; resize:vertical; }
        label { display:grid; gap:7px; color:var(--label-ink); font-size:var(--text-sm); font-weight:650; }.form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }.inline { display:inline; }.status { margin-bottom:18px; padding:11px 13px; border:1px solid var(--success-border); border-radius:var(--radius-lg); color:var(--success-text); background:var(--success-bg); }.error { margin-top:4px; color:var(--danger); font-size:var(--text-sm); }.badge { display:inline-flex; align-items:center; padding:4px 9px; border-radius:var(--radius-pill); color:var(--badge-ink); background:var(--badge-bg); font-size:var(--text-xs); font-weight:700; text-transform:capitalize; }.danger { color:var(--danger); }.muted { color:var(--muted); }
        #nprogress{position:fixed;top:0;left:0;right:0;height:3px;z-index:9999;pointer-events:none;opacity:0;transition:opacity .2s}
        #nprogress .bar{height:100%;width:100%;background:linear-gradient(90deg,var(--accent),var(--progress-tip));transform:scaleX(0);transform-origin:left;transition:transform .35s ease}
        #nprogress.loading{opacity:1}.skeleton{animation:shimmer 1.1s infinite linear;background:linear-gradient(90deg,var(--skeleton) 25%,var(--canvas) 37%,var(--skeleton) 63%);background-size:400% 100%}
        @keyframes shimmer{0%{background-position:100% 0}100%{background-position:-100% 0}}
        @media (max-width:1050px) { .sidebar { width:218px; flex-basis:218px; }.topbar { padding:0 26px; } main { padding:28px 26px 40px; }.stats { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:760px) { .app-shell { display:block; }.sidebar { width:100%; min-height:0; position:relative; padding:16px; }.brand { padding:0 5px 14px; }.nav-label,.sidebar-footer { display:none; } nav { display:flex; overflow:auto; gap:6px; } nav a { flex:0 0 auto; padding:8px 10px; font-size:var(--text-sm); }.nav-icon { width:16px; height:16px; }.topbar { height:58px; padding:0 18px; }.date { display:none; } main { padding:24px 18px 36px; } h1 { font-size:var(--text-2xl); }.stats,.two,.form-grid { grid-template-columns:1fr; }.card { padding:var(--space-4); } table { display:block; overflow-x:auto; white-space:nowrap; } }
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
            <a href="{{ route('orders.index') }}" @class(['active' => request()->routeIs('orders.*')])><svg class="nav-icon" viewBox="0 0 24 24"><path d="M3 6h18M6 6l1 15h10l1-15M9 10v7M15 10v7"/></svg>Penjualan & Pembayaran</a>
            <a href="{{ route('reports.index') }}" @class(['active' => request()->routeIs('reports.*')])><svg class="nav-icon" viewBox="0 0 24 24"><path d="M6 2h9l3 3v17H6z"/><path d="M9 13h6M9 17h6M9 9h2"/></svg>Cetak Laporan</a>
            <a href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])><svg class="nav-icon" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>Notif & Komunikasi</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-card"><div class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'U', 0, 1)) }}</div><div><div class="user-name">{{ auth()->user()?->name ?? 'Pengguna' }}</div><div class="user-role">{{ auth()->user()?->isAdmin() ? 'Administrator' : 'Staff' }}</div></div></div>
            @if(auth()->user()?->isAdmin())
            <nav style="margin-bottom:var(--space-3)">
                <a href="{{ route('admin.users.index') }}" @class(['active' => request()->routeIs('admin.users.*')])><svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Kelola User</a>
            </nav>
            @endif
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5M15 12H3M21 3v18"/></svg>Logout</button></form>
        </div>
    </aside>
    <div class="main-area">
        <header class="topbar"><div class="crumb">Inventory / <strong>{{ request()->routeIs('dashboard') ? 'Dashboard' : 'Workspace' }}</strong></div><div class="topbar-right"><span class="date">{{ now()->translatedFormat('l, d F Y') }}</span><a class="bell" href="{{ route('notifications.index') }}" title="Notifikasi" style="position:relative;text-decoration:none"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>@if(($unreadNotifications ?? 0) > 0)<span class="badge" style="position:absolute;top:-6px;right:-6px;min-width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>@endif</a></div></header>
        <main>
            @if (session('status'))<div class="status">{{ session('status') }}</div>@endif
            @if (session('error'))<div class="status" style="background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-text)">{{ session('error') }}</div>@endif
            @if ($errors->any())<div class="status" style="background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-text)">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
<div id="nprogress"><div class="bar"></div></div>
<script>
// Instant navigation + prefetch — bikin pindah page terasa instan meski Neon 200ms
(() => {
  const bar = document.querySelector('#nprogress .bar');
  const nprogress = document.getElementById('nprogress');
  let loading = false;
  const setProgress = (p) => { bar.style.transform = `scaleX(${p})`; };
  const start = () => { if(loading) return; loading=true; nprogress.classList.add('loading'); setProgress(0.35); setTimeout(()=>setProgress(0.7),120); };
  const done = () => { setProgress(1); setTimeout(()=>{ nprogress.classList.remove('loading'); setProgress(0); loading=false; }, 220); };
  const isInternal = (a) => a.host === location.host && !a.hasAttribute('target') && !a.href.includes('#') && !a.href.includes('logout');
  const swapMain = (html, url) => {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const newMain = doc.querySelector('main');
    const curMain = document.querySelector('main');
    if (!newMain || !curMain) return false;
    curMain.innerHTML = newMain.innerHTML;
    document.title = doc.title;
    // update active nav
    document.querySelectorAll('nav a').forEach(a=>a.classList.toggle('active', a.href===url || (url!==location.href && a.getAttribute('href')===new URL(url).pathname)));
    // push crumb
    const crumbStrong = document.querySelector('.crumb strong');
    if (crumbStrong) crumbStrong.textContent = doc.querySelector('.crumb strong')?.textContent || crumbStrong.textContent;
    history.pushState({}, '', url);
    window.scrollTo(0,0);
    return true;
  };
  document.addEventListener('click', async (e) => {
    const a = e.target.closest('nav a, a.btn');
    if (!a || !isInternal(a)) return;
    // hanya untuk GET page (bukan form POST)
    if (a.tagName==='A' && a.href && a.getAttribute('href').startsWith('/')) {
      e.preventDefault();
      const url = a.href;
      start();
      try {
        const res = await fetch(url, {headers:{'X-Requested-With':'fetch','Accept':'text/html'}});
        if (!res.ok) throw 0;
        const html = await res.text();
        if (!swapMain(html, url)) location.href = url;
      } catch { location.href = url; }
      done();
    }
  });
  // prefetch on hover + idle
  const prefetch = (href) => { if(document.querySelector(`link[rel="prefetch"][href="${href}"]`)) return; const l=document.createElement('link'); l.rel='prefetch'; l.href=href; document.head.appendChild(l); fetch(href,{headers:{'X-Prefetch':'1'}}).catch(()=>{}); };
  document.querySelectorAll('nav a').forEach(a=>a.addEventListener('mouseenter',()=>prefetch(a.href),{once:true}));
  // skeleton on refresh (F5): show bar immediately
  window.addEventListener('beforeunload', start);
  window.addEventListener('popstate', () => location.reload());
})();
</script>
<script data-double-submit-guard>
/* Anti dobel-klik: kunci tombol submit form POST sekali tekan (submit event
   baru jalan setelah confirm() OK, jadi dialog hapus tetap bekerja). */
(() => {
  const lock = (form) => {
    const btns = form.querySelectorAll('button[type="submit"]');
    if ([...btns].every((b) => b.disabled)) return; // sudah terkunci
    btns.forEach((b) => {
      b.disabled = true;
      if (!b.dataset.label) b.dataset.label = b.textContent.trim();
      b.textContent = 'Memproses…';
    });
  };
  document.addEventListener('submit', (e) => {
    if (e.target.tagName === 'FORM' && (e.target.method || 'get').toLowerCase() !== 'get') lock(e.target);
  });
  // tombol back browser bisa mengembalikan state disabled — buka kuncinya
  window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[type="submit"][disabled]').forEach((b) => {
      b.disabled = false;
      if (b.dataset.label) b.textContent = b.dataset.label;
    });
  });
})();
</script>
</body>
</html>
