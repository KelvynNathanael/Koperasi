<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Koperasi') – Sistem Manajemen</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-active: #2563eb;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            overflow-y: auto;
            z-index: 1000;
            transition: transform .25s ease;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .sidebar-brand h5 {
            color: #fff;
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .sidebar-brand small { color: var(--sidebar-text); font-size: .7rem; }

        .sidebar-section {
            padding: .75rem 1.5rem .25rem;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .55rem 1.5rem;
            color: var(--sidebar-text);
            font-size: .875rem;
            border-left: 3px solid transparent;
            transition: all .15s;
            text-decoration: none;
        }

        .sidebar-nav .nav-link:hover {
            color: #e2e8f0;
            background: rgba(255,255,255,.05);
        }

        .sidebar-nav .nav-link.active {
            color: #fff;
            background: rgba(37,99,235,.15);
            border-left-color: var(--sidebar-active);
        }

        .sidebar-nav .nav-link i { font-size: 1rem; width: 1.1rem; text-align: center; }

        /* ── Main ────────────────────────────────────────────── */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-content { padding: 1.75rem; }

        /* ── Cards ───────────────────────────────────────────── */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            border-radius: .75rem .75rem 0 0 !important;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        /* ── Stats cards ─────────────────────────────────────── */
        .stat-card {
            border-radius: .75rem;
            padding: 1.25rem;
            color: #fff;
            border: 0;
        }

        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: .5rem;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        .stat-card .stat-value { font-size: 1.5rem; font-weight: 700; }
        .stat-card .stat-label { font-size: .8rem; opacity: .85; }

        /* ── Badges ──────────────────────────────────────────── */
        .badge-status-active    { background: #dcfce7; color: #166534; }
        .badge-status-nonactive { background: #fee2e2; color: #991b1b; }
        .badge-status-paid      { background: #dcfce7; color: #166534; }
        .badge-status-unpaid    { background: #fef9c3; color: #854d0e; }
        .badge-status-partial   { background: #dbeafe; color: #1e40af; }
        .badge-status-late      { background: #fee2e2; color: #991b1b; }
        .badge-status-overdue   { background: #fee2e2; color: #991b1b; }
        .badge-status-cancelled { background: #f1f5f9; color: #64748b; }

        .badge-flow-in  { background: #dcfce7; color: #166534; }
        .badge-flow-out { background: #fee2e2; color: #991b1b; }

        /* ── Tables ──────────────────────────────────────────── */
        .table th {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .table td { vertical-align: middle; font-size: .875rem; }

        .table-hover tbody tr:hover { background: #f8fafc; }

        /* ── Forms ───────────────────────────────────────────── */
        .form-label { font-size: .825rem; font-weight: 600; color: #374151; }

        .form-control, .form-select {
            border-color: #d1d5db;
            font-size: .875rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }

        /* ── Responsive ──────────────────────────────────────── */
        @media (max-width: 991px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- ══ Sidebar ════════════════════════════════════════════════ -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-bank text-white" style="font-size:1.1rem;"></i>
            </div>
            <div>
                <h5 class="mb-0">KoperasiApp</h5>
                <small>Sistem Manajemen</small>
            </div>
        </div>
    </div>

    <ul class="sidebar-nav nav flex-column mt-2 pb-4">
        <li class="sidebar-section">Utama</li>

        <li>
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
        </li>

        <li class="sidebar-section mt-2">Anggota & Pinjaman</li>

        <li>
            <a href="{{ route('members.index') }}"
               class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Anggota
            </a>
        </li>

        <li>
            <a href="{{ route('loans.index') }}"
               class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Pinjaman
            </a>
        </li>

        <li>
            <a href="{{ route('repayments.index') }}"
               class="nav-link {{ request()->routeIs('repayments.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left"></i> Pembayaran
            </a>
        </li>

        <li class="sidebar-section mt-2">Keuangan</li>

        <li>
            <a href="{{ route('cash-flows.index') }}"
               class="nav-link {{ request()->routeIs('cash-flows.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Kas & Arus Dana
            </a>
        </li>
    </ul>
    <div class="p-3 mt-auto border-top" style="border-color: rgba(255,255,255,.07) !important;">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width:32px;height:32px;border-radius:50%;background:#334155;
                display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-person-fill text-white" style="font-size:.9rem;"></i>
            </div>
            <div>
                <div class="text-white small fw-semibold">{{ Auth::user()->name }}</div>
                <div class="text-secondary" style="font-size:.7rem;">{{ Auth::user()->email }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm w-100 text-secondary"
                    style="background:rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);">
                <i class="bi bi-box-arrow-left me-1"></i> Keluar
            </button>
        </form>
    </div>
</nav>

<!-- ══ Main Content ═══════════════════════════════════════════ -->
<div id="main-content">

    <!-- Topbar -->
    <div class="topbar d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle">
            <i class="bi bi-list fs-5"></i>
        </button>

        <nav aria-label="breadcrumb" class="flex-grow-1">
            <ol class="breadcrumb mb-0 small">
                @yield('breadcrumb')
            </ol>
        </nav>

        <span class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i>
            {{ now()->translatedFormat('d F Y') }}
        </span>
    </div>

    <!-- Alerts -->
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('error') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Terdapat kesalahan pada input:</strong>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <div class="page-content">
        @yield('content')
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle (mobile)
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>

@stack('scripts')
</body>
</html>
