<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SI-GYM')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
    <style>
        :root { --blue: #2563eb; --background: #f4f7fb; --border: #e2e8f0; }
        * { box-sizing: border-box; }
        body { background: var(--background); color: #1e293b; font-family: "Inter", sans-serif; font-size: 14px; }
        body.authenticated-page { overflow-x: hidden; }
        .sidebar { width: 248px; min-height: 100vh; background: linear-gradient(180deg,#111827,#172c5b); border-right: 0; position: fixed; inset: 0 auto 0 0; z-index: 1030; overflow-y: auto; box-shadow: 6px 0 24px rgba(15,23,42,.08); }
        .brand { color: #fff; font-size: 20px; font-weight: 800; padding: 10px 0 24px; letter-spacing: -.8px; }
        .brand-mark { width: 38px; height: 38px; display: inline-grid; place-items: center; border-radius: 11px; background: #2563eb; color: white; margin-right: 9px; font-style: italic; }
        .menu-label { color: #7f91ad; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; padding: 16px 14px 7px; text-transform: uppercase; }
        .sidebar-menu a { color: #cbd5e1; text-decoration: none; padding: 11px 14px; margin-bottom: 3px; display: flex; align-items: center; gap: 12px; border-radius: 8px; font-weight: 500; }
        .sidebar-menu a svg { width: 18px; height: 18px; }
        .sidebar-menu a:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sidebar-menu a.active { background: #2563eb; color: #fff; box-shadow: 0 6px 16px rgba(37,99,235,.28); }
        .sidebar-menu a.active { box-shadow: inset 3px 0 #2563eb; }
        .sidebar-menu .member-sidebar-logout { margin-top: 16px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,.12); }
        .sidebar-menu .member-sidebar-logout button { width: 100%; padding: 11px 14px; border: 0; border-radius: 8px; background: transparent; color: #cbd5e1; display: flex; align-items: center; gap: 12px; font: inherit; text-align: left; }
        .sidebar-menu .member-sidebar-logout button:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sidebar-menu .member-sidebar-logout svg { width: 18px; height: 18px; }
        .sidebar-footer { border-top: 1px solid var(--border); margin-top: 18px; padding: 18px 10px 10px; }
        .main { margin-left: 248px; min-height: 100vh; }
        .topbar { height: 68px; background: white; border-bottom: 1px solid #e9eef5; display: flex; align-items: center; justify-content: space-between; padding: 0 28px; }
        .page-content { padding: 28px clamp(16px, 2.5vw, 34px) 40px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        .page-header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .page-title { font-size: 24px; font-weight: 700; letter-spacing: -.3px; }
        .stat-card { border: 0; border-radius: 14px; box-shadow: 0 3px 14px rgba(15,23,42,.055); height: 100%; }
        .app-card { border: 0; border-radius: 14px; box-shadow: 0 3px 14px rgba(15,23,42,.055); }
        .admin-table-card { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 3px 14px rgba(15,23,42,.04); }
        .admin-table-card .table { margin-bottom: 0; }
        .admin-table-card thead th { background: #f8fafc; color: #667085; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .45px; border-bottom: 1px solid var(--border); padding-top: 14px; padding-bottom: 14px; white-space: nowrap; }
        .admin-table-card tbody td { vertical-align: middle; border-color: #eef1f5; padding-top: 14px; padding-bottom: 14px; }
        .btn-primary { border-color: #2563eb; background: #2563eb; }
        .btn-outline-secondary { border-color: #d0d5dd; color: #344054; }
        .owner-theme { background: #f4f7fb; }
        .owner-theme .sidebar { background: #172033; border-right: 0; box-shadow: 8px 0 24px rgba(15,23,42,.08); }
        .owner-theme .brand { color: #fff; }
        .owner-theme .brand-mark { background: #d95d39; }
        .owner-theme .menu-label { color: #7f8da7; }
        .owner-theme .sidebar-menu a { color: #cbd5e1; }
        .owner-theme .sidebar-menu a:hover, .owner-theme .sidebar-menu a.active { background: rgba(255,255,255,.08); color: #fff; }
        .owner-theme .sidebar-menu a.active { box-shadow: inset 3px 0 #f97316; }
        .owner-theme .sidebar-footer { border-color: rgba(255,255,255,.08); }
        .owner-theme .topbar { border-bottom: 0; box-shadow: 0 1px 12px rgba(15,23,42,.04); }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .role-badge { display:inline-flex;align-items:center;gap:13px;min-height:58px;padding:7px 24px 7px 13px;border:1px solid rgba(255,255,255,.22);border-radius:14px;background:linear-gradient(135deg,#1e3a72,#2f5c9d);box-shadow:inset 0 0 0 1px rgba(255,255,255,.08),0 3px 12px rgba(15,23,42,.18);color:white;font-size:17px;font-weight:700;white-space:nowrap; }
        .role-badge-icon { width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:#fff;color:#204c87; }
        .role-badge-icon svg { width:24px;height:24px; }
        .sidebar-account { border-radius:5px;background:rgba(255,255,255,.08);padding:10px;display:flex;align-items:center;gap:10px;color:white;margin-bottom:8px; }
        .sidebar-account-avatar { width:34px;height:34px;border-radius:50%;background:white;color:#172033;display:grid;place-items:center;font-weight:700; }
        .sidebar-account small { color:#cbd5e1;display:block;max-width:150px;overflow:hidden;text-overflow:ellipsis; }
        .topbar-actions { display:flex;align-items:center;gap:16px; }
        .notification-button { position:relative;border:0;background:transparent;color:#475569;padding:6px;display:grid;place-items:center; }
        .notification-button svg { width:20px;height:20px; }
        .notification-dot { position:absolute;right:-1px;top:-3px;width:17px;height:17px;border-radius:50%;background:#ef4444;border:2px solid white;color:white;font-size:9px;font-weight:700;display:grid;place-items:center; }
        .topbar-logout { border:0;background:transparent;color:#475569;font-size:13px;text-decoration:none;display:flex;gap:5px;align-items:center; }
        .crud-toolbar { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 10px rgba(15,23,42,.035); }
        .crud-toolbar .form-control { min-width: 220px; border-radius: 8px; }
        .crud-action { width: 34px; height: 34px; display: inline-grid; place-items: center; border-radius: 50%; padding: 0; }
        .crud-action svg { width: 16px; height: 16px; }
        .admin-theme .sidebar { background: #111827; border-right: 0; }
        .admin-theme .brand, .admin-theme .brand-mark { color: #fff; }
        .admin-theme .brand-mark { background: #2563eb; }
        .admin-theme .menu-label { color: #64748b; }
        .admin-theme .sidebar-menu a { color: #cbd5e1; }
        .admin-theme .sidebar-menu a:hover, .admin-theme .sidebar-menu a.active { background: rgba(96,165,250,.14); color: #fff; }
        .admin-theme .sidebar-menu a.active { box-shadow: inset 3px 0 #60a5fa; }
        .admin-theme .sidebar-footer { border-color: rgba(255,255,255,.08); }
        .admin-theme .sidebar-footer .btn { border-color: rgba(255,255,255,.16); color: #dbeafe; }
        .owner-theme .sidebar { background: linear-gradient(180deg,#172033,#213052); }
        .member-theme .sidebar, .registrant-theme .sidebar { background: linear-gradient(180deg,#111827,#172d5c); }
        .page-header { padding-bottom: 18px; border-bottom: 1px solid #e8edf4; }
        .page-header .page-title { color: #172033; }
        .crud-toolbar .form-select, .crud-toolbar .form-control { border-color: #dfe5ec; }
        .crud-toolbar .form-control:focus, .crud-toolbar .form-select:focus { border-color: #93b4f4; box-shadow: 0 0 0 .2rem rgba(37,99,235,.08); }
        form.card, .card.app-card, .card-body .row + .btn-primary { border-radius: 12px; }
        .page-content > form.card, .page-content > .card:not(.admin-table-card):not(.crud-toolbar) { box-shadow: 0 5px 18px rgba(15,23,42,.045); }
        .form-label { font-weight: 600; color: #344054; }
        .form-control, .form-select { border-color: #dfe5ec; min-height: 42px; }
        .topbar-menu { display: none; border: 1px solid var(--border); background: white; width: 38px; height: 38px; border-radius: 9px; align-items: center; justify-content: center; }
        .sidebar-backdrop { display: none; }
        .card, .btn, .form-control, .form-select { transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .table-responsive { scrollbar-width: thin; }
        @media (max-width: 992px) { .sidebar { width: 78px; } .sidebar .brand > span:last-child, .menu-label, .sidebar-menu a span, .sidebar-footer span { display: none; } .brand-mark { margin: 0; } .sidebar-menu a { justify-content: center; } .main { margin-left: 78px; } }
        @media (max-width: 768px) {
            .sidebar { width: min(280px, 86vw); transform: translateX(-100%); transition: transform .22s ease; position: fixed; z-index: 1050; }
            .sidebar.sidebar-open { transform: translateX(0); box-shadow: 16px 0 40px rgba(15,23,42,.22); }
            .sidebar .brand > span:last-child, .sidebar-menu a span, .sidebar-footer span, .menu-label { display: block; }
            .sidebar-menu a { justify-content: flex-start; }
            .sidebar-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.42); z-index: 1040; }
            .sidebar-backdrop.visible { display: block; }
            .main { margin-left: 0; }
            .topbar { height: 64px; padding: 0 16px; }
            .topbar-menu { display: inline-flex; }
            .role-badge { min-height:38px;gap:7px;padding:4px 8px;font-size:10px;border-radius:10px; }
            .role-badge-icon { width:28px;height:28px; }
            .role-badge-icon svg { width:18px;height:18px; }
            .topbar-actions { gap:8px; }
            .topbar-logout { font-size:11px; }
            .page-content { padding: 20px 16px 32px; }
            .page-header { align-items: flex-start; flex-direction: column; }
            .page-header-actions { width: 100%; justify-content: flex-start; }
            .page-header-actions > .btn { flex: 1 1 150px; }
            .page-title { font-size: 22px; }
        }
    </style>
</head>
<body class="{{ auth()->check() ? 'authenticated-page '.match(auth()->user()->role) { 'owner' => 'owner-theme', 'admin' => 'admin-theme', 'member' => 'member-theme', default => 'registrant-theme' } : '' }}">
@auth
<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
<aside class="sidebar p-3" id="sidebar">
    <div class="brand">
        <span class="brand-mark">
            <i data-lucide="dumbbell"></i>
        </span>
        <span>SI-GYM</span>
    </div>
    @if(auth()->user()->role === 'admin')
        <nav class="sidebar-menu">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
            <a class="{{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i data-lucide="users"></i><span>Pengguna</span></a>
            <a class="{{ request()->routeIs('registrations.*') ? 'active' : '' }}" href="{{ route('registrations.index') }}"><i data-lucide="clipboard-list"></i><span>Pendaftaran</span></a>
            <a class="{{ request()->routeIs('members.*') ? 'active' : '' }}" href="{{ route('members.index') }}"><i data-lucide="user-round-check"></i><span>Data Member</span></a>
            <a class="{{ request()->routeIs('packages.*') ? 'active' : '' }}" href="{{ route('packages.index') }}"><i data-lucide="package"></i><span>Paket Membership</span></a>
            <a class="{{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}"><i data-lucide="credit-card"></i><span>Pembayaran</span></a>
            <a class="{{ request()->routeIs('memberships.*') ? 'active' : '' }}" href="{{ route('memberships.index') }}"><i data-lucide="badge-check"></i><span>Membership</span></a>
            <div class="menu-label">Administrasi</div>
            <a href="{{ route('reports.index') }}"><i data-lucide="chart-no-axes-combined"></i><span>Laporan</span></a>
            <a href="{{ route('settings.index') }}"><i data-lucide="settings"></i><span>Pengaturan</span></a>
        </nav>
    @elseif(auth()->user()->role === 'owner')
        <div class="menu-label">Pemantauan</div>
        <nav class="sidebar-menu">
            <a class="{{ request()->routeIs('owner.dashboard') ? 'active' : '' }}" href="{{ route('owner.dashboard') }}"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
            <a class="{{ request()->routeIs('owner.reports') ? 'active' : '' }}" href="{{ route('owner.reports') }}"><i data-lucide="chart-no-axes-combined"></i><span>Laporan & Statistik</span></a>
            <a class="{{ request()->routeIs('owner.members*') ? 'active' : '' }}" href="{{ route('owner.members') }}"><i data-lucide="users"></i><span>Data Member</span></a>
            <a class="{{ request()->routeIs('owner.registrations*') ? 'active' : '' }}" href="{{ route('owner.registrations') }}"><i data-lucide="clipboard-list"></i><span>Pendaftar</span></a>
            <a class="{{ request()->routeIs('owner.packages*') ? 'active' : '' }}" href="{{ route('owner.packages') }}"><i data-lucide="package"></i><span>Paket Membership</span></a>
            <a class="{{ request()->routeIs('owner.payments*') ? 'active' : '' }}" href="{{ route('owner.payments') }}"><i data-lucide="credit-card"></i><span>Pembayaran</span></a>
            <a class="{{ request()->routeIs('owner.memberships*') ? 'active' : '' }}" href="{{ route('owner.memberships') }}"><i data-lucide="badge-check"></i><span>Membership</span></a>
            <a class="{{ request()->routeIs('owner.profile*') || request()->routeIs('owner.password*') ? 'active' : '' }}" href="{{ route('owner.profile') }}"><i data-lucide="circle-user-round"></i><span>Profil Saya</span></a>
            <a class="{{ request()->routeIs('owner.settings') ? 'active' : '' }}" href="{{ route('owner.settings') }}"><i data-lucide="settings"></i><span>Pengaturan</span></a>
        </nav>
    @elseif(auth()->user()->role === 'member')
        <div class="menu-label">Area Member</div>
        <nav class="sidebar-menu">
            <a class="{{ request()->routeIs('member.dashboard') ? 'active' : '' }}" href="{{ route('member.dashboard') }}"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
            <a class="{{ request()->routeIs('member.profile') ? 'active' : '' }}" href="{{ route('member.profile') }}"><i data-lucide="circle-user-round"></i><span>Profil Saya</span></a>
            <a class="{{ request()->routeIs('member.memberships*') ? 'active' : '' }}" href="{{ route('member.memberships') }}"><i data-lucide="badge-check"></i><span>Membership</span></a>
            <a class="{{ request()->routeIs('member.payments*') ? 'active' : '' }}" href="{{ route('member.payments') }}"><i data-lucide="credit-card"></i><span>Riwayat Pembayaran</span></a>
            <a href="{{ route('member.profile') }}#ubah-password"><i data-lucide="key-round"></i><span>Ubah Password</span></a>
            <form method="POST" action="{{ route('logout') }}" class="member-sidebar-logout">@csrf<button type="submit"><i data-lucide="log-out"></i><span>Logout</span></button></form>
        </nav>
    @else
        <div class="menu-label">Area Pendaftar</div>
        <nav class="sidebar-menu">
            <a class="{{ request()->routeIs('registrant.dashboard') ? 'active' : '' }}" href="{{ route('registrant.dashboard') }}"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
            <a href="{{ route('registrant.dashboard') }}#informasi-gym"><i data-lucide="dumbbell"></i><span>Informasi Gym</span></a>
            <a class="{{ request()->routeIs('registrant.packages.show') ? 'active' : '' }}" href="{{ route('registrant.dashboard') }}#paket-membership"><i data-lucide="package"></i><span>Paket Membership</span></a>
            <a class="{{ request()->routeIs('registrations.status.me', 'registrations.show.me', 'registrations.edit.me', 'registrations.create') ? 'active' : '' }}" href="{{ route('registrations.status.me') }}"><i data-lucide="list-checks"></i><span>Pendaftaran Saya</span></a>
            <a class="{{ request()->routeIs('registrant.profile') ? 'active' : '' }}" href="{{ route('registrant.profile') }}"><i data-lucide="circle-user-round"></i><span>Profil Saya</span></a>
            <a href="{{ route('registrant.profile') }}#ubah-password"><i data-lucide="key-round"></i><span>Ubah Password</span></a>
            <a class="{{ request()->routeIs('registrant.payments*') ? 'active' : '' }}" href="{{ route('registrant.payments') }}"><i data-lucide="credit-card"></i><span>Pembayaran</span></a>
        </nav>
    @endif
    <div class="sidebar-footer">
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'owner')
        <div class="sidebar-account">
            <div class="sidebar-account-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <strong style="font-size:12px;display:block">{{ auth()->user()->role === 'admin' ? 'Admin Gym' : 'Pemilik Gym' }}</strong>
                <small>{{ auth()->user()->email }}</small>
            </div>
        </div>
        @endif
        @if(auth()->user()->role === 'member')
        <div class="sidebar-account">
            <div class="sidebar-account-avatar">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div><strong style="font-size:12px;display:block">Member</strong><small>{{ auth()->user()->email }}</small></div>
        </div>
        @endif
        @if(auth()->user()->role === 'pendaftar')
        <div class="sidebar-account">
            <div class="sidebar-account-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div><strong style="font-size:12px;display:block">Pendaftar</strong><small>{{ auth()->user()->email ?: 'pendaftar@gym.id' }}</small></div>
        </div>
        @endif
        @if(auth()->user()->role !== 'member')
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm w-100"><i data-lucide="log-out" class="me-1"></i><span>Keluar</span></button></form>
        @endif
    </div>
</aside>
@endauth
<main class="{{ auth()->check() ? 'main' : '' }}">
@auth
    <header class="topbar">
        <button class="topbar-menu" id="sidebarToggle" type="button" aria-label="Buka menu navigasi" aria-controls="sidebar"><i data-lucide="menu"></i></button>
        @php
            [$roleLabel, $roleIcon] = match (auth()->user()->role) {
                'admin' => ['Admin / Pengelola Gym', 'user-cog'],
                'owner' => ['Pemilik Gym', 'crown'],
                'member' => ['Member', 'user-round'],
                default => ['Pendaftar / Calon Member', 'user-round-plus'],
            };
        @endphp
        <div class="role-badge"><span class="role-badge-icon"><i data-lucide="{{ $roleIcon }}"></i></span><span>{{ $roleLabel }}</span></div>
        <div class="topbar-actions">
            <button class="notification-button" type="button" aria-label="Notifikasi"><i data-lucide="bell"></i><span class="notification-dot">9</span></button>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="topbar-logout" type="submit">+ <span>Logout</span></button></form>
        </div>
    </header>
    <div class="page-content">
@endauth
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @yield('content')
@auth
    </div>
@endauth
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@if(auth()->check() || request()->routeIs('login', 'register'))
<script>window.lucide?.createIcons();</script>
@endif
@auth
<script>
    const sidebar = document.querySelector('.sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const sidebarToggle = document.getElementById('sidebarToggle');

    function setSidebar(open) {
        sidebar?.classList.toggle('sidebar-open', open);
        sidebarBackdrop?.classList.toggle('visible', open);
        sidebarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    sidebarToggle?.addEventListener('click', () => setSidebar(!sidebar.classList.contains('sidebar-open')));
    sidebarBackdrop?.addEventListener('click', () => setSidebar(false));
    sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
        if (window.innerWidth <= 768) setSidebar(false);
    }));
</script>
@endauth
@stack('scripts')
</body>
</html>
