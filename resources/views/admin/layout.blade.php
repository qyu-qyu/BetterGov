<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page-title', 'Dashboard') — BetterGov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #1a56db;
            --sidebar-bg: #0f172a;
            --sidebar-text: #cbd5e1;
            --sidebar-active: #1a56db;
        }
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }

        /* Sidebar */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            display: flex; flex-direction: column;
            z-index: 1040;
            transition: transform .3s ease;
        }
        #sidebar .brand {
            padding: 1.5rem 1.25rem 1rem;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex; align-items: center; gap: .6rem;
        }
        #sidebar .brand i { color: var(--sidebar-active); font-size: 1.5rem; }
        #sidebar .brand small { display: block; font-size: .72rem; font-weight: 400; color: #64748b; margin-top: 1px; }
        #sidebar nav { flex: 1; padding: .75rem 0; overflow-y: auto; }
        #sidebar .nav-section {
            padding: .6rem 1.25rem .2rem;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
        }
        #sidebar nav a {
            display: flex; align-items: center; gap: .75rem;
            padding: .65rem 1.25rem;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: .9rem;
            border-left: 3px solid transparent;
            transition: all .15s;
        }
        #sidebar nav a:hover { color: #fff; background: rgba(255,255,255,.06); }
        #sidebar nav a.active {
            color: #fff;
            background: rgba(26,86,219,.15);
            border-left-color: var(--sidebar-active);
        }
        #sidebar nav a i { font-size: 1.1rem; width: 1.25rem; text-align: center; }
        #sidebar .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.08);
            font-size: .82rem;
            color: #64748b;
        }
        #sidebar .user-badge {
            display: flex; align-items: center; gap: .6rem;
            color: #e2e8f0; font-size: .88rem; margin-bottom: .75rem;
        }
        #sidebar .user-badge .avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--sidebar-active);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem; color: #fff;
            flex-shrink: 0;
        }

        /* Main content */
        #main { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .topbar .page-title { font-weight: 600; font-size: 1.05rem; color: #1e293b; margin: 0; }
        .content-area { padding: 1.75rem 1.5rem; }

        /* Cards */
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.07); border-radius: 10px; }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; font-weight: 600; }

        /* Status badges */
        .badge-pending    { background: #fef3c7; color: #92400e; }
        .badge-processing { background: #dbeafe; color: #1e40af; }
        .badge-approved   { background: #d1fae5; color: #065f46; }
        .badge-rejected   { background: #fee2e2; color: #991b1b; }
        .badge-completed  { background: #ede9fe; color: #4c1d95; }
        .badge-active     { background: #d1fae5; color: #065f46; }
        .badge-inactive   { background: #f1f5f9;  color: #64748b; }

        /* Alerts */
        #alert-container { position: fixed; top: 1rem; right: 1rem; z-index: 9999; width: 320px; }

        /* Spinner overlay */
        .spinner-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(255,255,255,.6);
            z-index: 9000; align-items: center; justify-content: center;
        }
        .spinner-overlay.active { display: flex; }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #main { margin-left: 0; }
        }
    </style>
    @stack('head')
</head>
<body>

<!-- Sidebar -->
<div id="sidebar">
    <div class="brand">
        <div>
            <div><i class="bi bi-building-fill-gear"></i> BetterGov</div>
            <small>Admin Panel</small>
        </div>
    </div>
    <nav>
        <div class="nav-section">Overview</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <div class="nav-section">Management</div>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->is('admin/users*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Users
        </a>
        <a href="{{ route('admin.municipalities.index') }}" class="{{ request()->is('admin/municipalities*') ? 'active' : '' }}">
            <i class="bi bi-map-fill"></i> Municipalities
        </a>
        <a href="{{ route('admin.offices.index') }}" class="{{ request()->is('admin/offices*') ? 'active' : '' }}">
            <i class="bi bi-building-fill"></i> Offices
        </a>
        <a href="{{ route('admin.services.index') }}" class="{{ request()->is('admin/services*') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap-fill"></i> Services
        </a>
        <div class="nav-section">Operations</div>
        <a href="{{ route('admin.requests.index') }}" class="{{ request()->is('admin/requests*') ? 'active' : '' }}">
            <i class="bi bi-inbox-fill"></i> Requests
        </a>
        <a href="{{ route('admin.reports.index') }}" class="{{ request()->is('admin/reports*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line-fill"></i> Reports
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-badge">
            <div class="avatar" id="user-avatar">A</div>
            <div>
                <div id="user-name" style="font-weight:600">Admin</div>
                <div style="color:#64748b;font-size:.78rem">Administrator</div>
            </div>
        </div>
        <a href="#" class="text-danger text-decoration-none" style="font-size:.83rem" onclick="logout()">
            <i class="bi bi-box-arrow-left me-1"></i>Logout
        </a>
    </div>
</div>

<!-- Spinner overlay -->
<div class="spinner-overlay" id="spinner">
    <div class="spinner-border text-primary" style="width:3rem;height:3rem"></div>
</div>

<!-- Alert container -->
<div id="alert-container"></div>

<!-- Main -->
<div id="main">
    <div class="topbar">
        <button class="btn btn-sm btn-light d-md-none me-2" onclick="document.getElementById('sidebar').classList.toggle('open')">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="page-title">@yield('page-title', 'Dashboard')</span>
        <div class="d-flex align-items-center gap-2">
            @yield('topbar_actions')
        </div>
    </div>
    <div class="content-area">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const API_BASE = '/api';

    function getToken() {
        return localStorage.getItem('citizen_token');
    }

    async function api(method, path, body = null, isForm = false) {
        const token = getToken();
        const headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        if (!isForm && body) headers['Content-Type'] = 'application/json';

        const opts = { method, headers };
        if (body) opts.body = isForm ? body : JSON.stringify(body);

        const res = await fetch(API_BASE + path, opts);
        if (res.status === 401) {
            localStorage.removeItem('citizen_token');
            location.href = '/login';
        }
        return res;
    }

    function showAlert(msg, type = 'success') {
        const id = 'alert-' + Date.now();
        const html = `<div id="${id}" class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
            ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        document.getElementById('alert-container').insertAdjacentHTML('beforeend', html);
        setTimeout(() => { document.getElementById(id)?.remove(); }, 4000);
    }

    function spinner(on) {
        document.getElementById('spinner').classList.toggle('active', on);
    }

    function statusBadge(status) {
        const map = {
            pending:    ['badge-pending',    'Pending'],
            processing: ['badge-processing', 'In Review'],
            approved:   ['badge-approved',   'Approved'],
            rejected:   ['badge-rejected',   'Rejected'],
            completed:  ['badge-completed',  'Completed'],
        };
        const [cls, label] = map[status] ?? ['bg-secondary bg-opacity-10 text-secondary', status];
        return `<span class="badge ${cls} px-2 py-1">${label}</span>`;
    }

    function fmtDate(d) {
        if (!d) return '—';
        return new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
    }

    function fmtDateTime(d) {
        if (!d) return '—';
        return new Date(d).toLocaleString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
    }

    async function logout() {
        await api('POST', '/logout');
        localStorage.removeItem('citizen_token');
        location.href = '/login';
    }

    async function loadUser() {
        const token = getToken();
        if (!token) { location.href = '/login'; return; }
        const res = await api('GET', '/me');
        if (!res || !res.ok) return;
        const data = await res.json();
        const user = data.user ?? data;
        if (user) {
            document.getElementById('user-name').textContent = user.name ?? 'Admin';
            document.getElementById('user-avatar').textContent = (user.name ?? 'A')[0].toUpperCase();
        }
    }

    loadUser();
</script>
@stack('scripts')
</body>
</html>
