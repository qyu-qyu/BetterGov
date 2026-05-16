<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Citizen Portal') — BetterGov</title>
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

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar-width); min-height: 100vh;
            background: var(--sidebar-bg); position: fixed;
            top: 0; left: 0; display: flex; flex-direction: column;
            z-index: 1040; transition: transform .3s ease;
        }
        #sidebar .brand {
            padding: 1.5rem 1.25rem 1rem; color: #fff;
            font-size: 1.2rem; font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex; align-items: center; gap: .6rem;
        }
        #sidebar .brand i { color: var(--sidebar-active); font-size: 1.5rem; }
        #sidebar nav { flex: 1; padding: .75rem 0; }
        #sidebar nav a {
            display: flex; align-items: center; gap: .75rem;
            padding: .65rem 1.25rem; color: var(--sidebar-text);
            text-decoration: none; font-size: .9rem;
            border-left: 3px solid transparent; transition: all .15s;
        }
        #sidebar nav a:hover { color: #fff; background: rgba(255,255,255,.06); }
        #sidebar nav a.active {
            color: #fff; background: rgba(26,86,219,.15);
            border-left-color: var(--sidebar-active);
        }
        #sidebar nav a i { font-size: 1.1rem; width: 1.25rem; text-align: center; }
        #sidebar .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.08);
            font-size: .82rem; color: #64748b;
        }
        #sidebar .user-badge {
            display: flex; align-items: center; gap: .6rem;
            color: #e2e8f0; font-size: .88rem; margin-bottom: .75rem;
        }
        #sidebar .user-badge .avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--sidebar-active);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem; color: #fff; flex-shrink: 0;
        }

        /* ── Main layout ── */
        #main { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 500;
        }
        .topbar .page-title { font-weight: 600; font-size: 1.05rem; color: #1e293b; margin: 0; }
        .content-area { padding: 1.75rem 1.5rem; }

        /* ── Cards ── */
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.07); border-radius: 10px; }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; font-weight: 600; }

        /* ── Status badges ── */
        .badge-pending           { background: #fef3c7; color: #92400e; }
        .badge-processing        { background: #dbeafe; color: #1e40af; }
        .badge-approved          { background: #d1fae5; color: #065f46; }
        .badge-rejected          { background: #fee2e2; color: #991b1b; }
        .badge-completed         { background: #ede9fe; color: #4c1d95; }
        .badge-missing_documents { background: #ffedd5; color: #9a3412; }

        /* ── Alerts ── */
        #alert-container { position: fixed; top: 1rem; right: 1rem; z-index: 9999; width: 320px; }

        /* ── Spinner ── */
        .spinner-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(255,255,255,.6);
            z-index: 9000; align-items: center; justify-content: center;
        }
        .spinner-overlay.active { display: flex; }

        /* ── Notification bell button ── */
        .notif-bell-btn {
            position: relative; background: none; border: none;
            width: 36px; height: 36px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #64748b; cursor: pointer; transition: background .15s;
            padding: 0; flex-shrink: 0;
        }
        .notif-bell-btn:hover { background: #f1f5f9; color: #1e293b; }
        .notif-bell-btn .bi { font-size: 1.2rem; }

        /* ── Unread count badge on bell ── */
        .notif-badge {
            position: absolute; top: 3px; right: 3px;
            background: #ef4444; color: #fff;
            font-size: .6rem; font-weight: 700; line-height: 1;
            min-width: 16px; height: 16px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 3px; pointer-events: none;
        }
        .notif-badge.d-none { display: none !important; }

        /* ── Notification dropdown panel ── */
        #notif-panel {
            display: none;
            position: fixed;
            top: 56px;
            right: 1rem;
            width: 360px;
            max-height: 480px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,.14);
            z-index: 1055;
            flex-direction: column;
            overflow: hidden;
        }
        #notif-panel.open { display: flex; }

        .notif-panel-header {
            padding: .75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .notif-panel-header h6 { margin: 0; font-weight: 600; font-size: .88rem; display: flex; align-items: center; gap: .4rem; }

        .notif-list { overflow-y: auto; flex: 1; }

        .notif-item {
            display: flex; gap: .75rem; align-items: flex-start;
            padding: .75rem 1rem; border-bottom: 1px solid #f8fafc;
            cursor: pointer; transition: background .12s;
            text-decoration: none; color: inherit;
        }
        .notif-item:hover { background: #f8fafc; }
        .notif-item:last-child { border-bottom: none; }
        .notif-item.unread { background: #eff6ff; }
        .notif-item.unread:hover { background: #dbeafe; }

        .notif-icon {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem; flex-shrink: 0; margin-top: 1px;
        }
        .notif-icon.status   { background: #dbeafe; color: #1d4ed8; }
        .notif-icon.message  { background: #d1fae5; color: #065f46; }
        .notif-icon.document { background: #ede9fe; color: #4c1d95; }
        .notif-icon.request  { background: #fef3c7; color: #92400e; }
        .notif-icon.default  { background: #f1f5f9; color: #64748b; }

        .notif-msg  { font-size: .82rem; color: #1e293b; line-height: 1.45; margin: 0 0 2px; }
        .notif-time { font-size: .72rem; color: #94a3b8; }
        .notif-dot-live {
            width: 8px; height: 8px; border-radius: 50%;
            background: #3b82f6; flex-shrink: 0; margin-top: 5px;
        }

        .notif-panel-footer {
            padding: .6rem 1rem; border-top: 1px solid #f1f5f9;
            text-align: center; flex-shrink: 0;
        }

        .notif-empty {
            padding: 2.5rem 1rem; text-align: center;
            color: #94a3b8; font-size: .85rem;
        }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #main { margin-left: 0; }
            #notif-panel { width: calc(100vw - 2rem); right: 1rem; }
        }
    </style>
    @stack('head')
</head>
<body>

<!-- Sidebar -->
<div id="sidebar">
    <div class="brand">
        <i class="bi bi-building-fill-gear"></i>
        BetterGov
    </div>
    <nav>
        <a href="{{ route('citizen.services.index') }}"
            class="{{ request()->is('citizen/services*') && !request()->is('citizen/services/*/show') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap-fill"></i> Browse Services
        </a>
        <a href="{{ route('citizen.requests.index') }}"
            class="{{ request()->is('citizen/requests*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text-fill"></i> My Requests
        </a>
        <a href="{{ route('citizen.offices.index') }}"
            class="{{ request()->is('citizen/offices*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill"></i> Find Offices
        </a>
        <a href="{{ route('citizen.appointments.index') }}"
            class="{{ request()->is('citizen/appointments*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check-fill"></i> Appointments
        </a>
        <a href="{{ route('citizen.feedback.create') }}"
            class="{{ request()->is('citizen/feedback*') ? 'active' : '' }}">
            <i class="bi bi-star-fill"></i> Feedback
        </a>
        <a href="{{ route('citizen.history.index') }}"
            class="{{ request()->is('citizen/history*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> History
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-badge">
            <div class="avatar" id="user-avatar">?</div>
            <div>
                <div id="user-name" style="font-weight:600">Loading…</div>
                <div id="user-email" style="color:#64748b;font-size:.78rem"></div>
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

<!-- Notification panel (outside #main so z-index works correctly) -->
<div id="notif-panel">
    <div class="notif-panel-header">
        <h6>
            Notifications
        </h6>
        <button class="btn btn-sm btn-link text-muted p-0" style="font-size:.8rem"
            onclick="markAllRead()">Mark all read</button>
    </div>
    <div class="notif-list" id="notif-list">
        <div class="notif-empty">
            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
            No notifications yet
        </div>
    </div>
    <div class="notif-panel-footer">
        <a href="{{ route('citizen.requests.index') }}"
            class="text-primary text-decoration-none" style="font-size:.82rem">
            View all requests
        </a>
    </div>
</div>

<!-- Main -->
<div id="main">
    <div class="topbar">
        <button class="btn btn-sm btn-light d-md-none me-2"
            onclick="document.getElementById('sidebar').classList.toggle('open')">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="page-title">@yield('page-title', 'Citizen Portal')</span>
        <div class="d-flex align-items-center gap-2">
            <!-- Notification bell -->
            <button class="notif-bell-btn" id="notif-bell-btn"
                onclick="toggleNotifPanel()" aria-label="Notifications">
                <i class="bi bi-bell-fill"></i>
                <span class="notif-badge d-none" id="notif-count"></span>
            </button>
            <a href="{{ route('citizen.requests.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Request
            </a>
        </div>
    </div>
    <div class="content-area">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API_BASE = '/api';

function getToken() { return localStorage.getItem('citizen_token'); }

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
        window.location.href = '/login';
        return res;
    }
    return res;
}

function showAlert(msg, type = 'success') {
    const id = 'alert-' + Date.now();
    document.getElementById('alert-container').insertAdjacentHTML('beforeend',
        `<div id="${id}" class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
            ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`);
    setTimeout(() => document.getElementById(id)?.remove(), 5000);
}

function spinner(on) {
    document.getElementById('spinner').classList.toggle('active', on);
}

function statusBadge(status) {
    const map = {
        pending:           ['badge-pending',           'Pending'],
        processing:        ['badge-processing',        'In Review'],
        approved:          ['badge-approved',          'Approved'],
        rejected:          ['badge-rejected',          'Rejected'],
        completed:         ['badge-completed',         'Completed'],
        missing_documents: ['badge-missing_documents', 'Missing Docs'],
    };
    const [cls, label] = map[status] ?? ['bg-secondary text-white', status];
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
function fmtTimeAgo(d) {
    if (!d) return '';
    const diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}

async function logout() {
    await api('POST', '/logout');
    localStorage.removeItem('citizen_token');
    window.location.href = '/login';
}

// ── User ──────────────────────────────────────────────────────────────────────
async function loadUser() {
    const token = getToken();
    if (!token) return;
    const res = await api('GET', '/me');
    if (!res?.ok) return;
    const data = await res.json();
    const user = data.user ?? data;
    if (user) {
        document.getElementById('user-name').textContent   = user.name  ?? '';
        document.getElementById('user-email').textContent  = user.email ?? '';
        document.getElementById('user-avatar').textContent = (user.name ?? '?')[0].toUpperCase();
    }
}

// ── Notifications ─────────────────────────────────────────────────────────────
let notifOpen = false;

function notifTypeIcon(type) {
    const map = {
        status_change:     { cls: 'status',   icon: 'bi-arrow-repeat' },
        new_message:       { cls: 'message',  icon: 'bi-chat-dots-fill' },
        document_uploaded: { cls: 'document', icon: 'bi-file-earmark-arrow-down-fill' },
        new_request:       { cls: 'request',  icon: 'bi-file-earmark-plus-fill' },
    };
    return map[type] ?? { cls: 'default', icon: 'bi-bell-fill' };
}

async function loadNotifications() {
    const res = await api('GET', '/notifications');
    if (!res?.ok) return;
    const json = await res.json();
    renderNotifBadge(json.unread ?? 0);
    renderNotifList(json.data ?? [], '/citizen/requests/');
}

function renderNotifBadge(unread) {
    const badge = document.getElementById('notif-count');
    if (!badge) return;
    if (unread > 0) {
        badge.textContent = unread > 99 ? '99+' : unread;
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function renderNotifList(notifs, baseUrl) {
    const list = document.getElementById('notif-list');
    if (!list) return;
    if (!notifs.length) {
        list.innerHTML = `<div class="notif-empty">
            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>No notifications yet</div>`;
        return;
    }
    list.innerHTML = notifs.map(n => {
        const { cls, icon } = notifTypeIcon(n.type);
        const href = n.request_id ? baseUrl + n.request_id : '#';
        return `<a class="notif-item${n.is_read ? '' : ' unread'}"
                href="${href}" onclick="markRead(event,${n.id},'${href}')">
            <div class="notif-icon ${cls}"><i class="bi ${icon}"></i></div>
            <div style="flex:1;min-width:0">
                <p class="notif-msg">${n.message}</p>
                <div class="notif-time">${fmtTimeAgo(n.created_at)}</div>
            </div>
            ${!n.is_read ? '<div class="notif-dot-live"></div>' : ''}
        </a>`;
    }).join('');
}

async function markRead(e, id, href) {
    e.preventDefault();
    await api('POST', `/notifications/${id}/read`);
    await loadNotifications();
    if (href && href !== '#') window.location.href = href;
}

async function markAllRead() {
    await api('POST', '/notifications/read-all');
    await loadNotifications();
}

function toggleNotifPanel() {
    notifOpen = !notifOpen;
    const panel = document.getElementById('notif-panel');
    panel.classList.toggle('open', notifOpen);
    if (notifOpen) loadNotifications();
}

// Close panel on outside click
document.addEventListener('click', function (e) {
    if (!notifOpen) return;
    const panel = document.getElementById('notif-panel');
    const btn   = document.getElementById('notif-bell-btn');
    if (panel && btn && !panel.contains(e.target) && !btn.contains(e.target)) {
        notifOpen = false;
        panel.classList.remove('open');
    }
});

// ── Notification polling (replaces SSE for single-threaded dev servers) ──────
// SSE blocks php artisan serve (single-threaded). Use polling instead.
// On production with Nginx/Apache/Octane, you can switch back to SSE.

let _lastUnread = 0;

async function pollNotifications() {
    const res = await api('GET', '/notifications');
    if (!res?.ok) return;
    const json = await res.json();
    const unread = json.unread ?? 0;
    renderNotifBadge(unread);

    // If unread count went up, show a toast for the newest notification
    if (unread > _lastUnread && json.data?.length) {
        const latest = json.data[0];
        if (latest && !latest.is_read) {
            const { icon } = notifTypeIcon(latest.type);
            if (!notifOpen) {
                showAlert('<i class="bi ' + icon + ' me-2"></i>' + latest.message, 'info');
            }
        }
    }
    _lastUnread = unread;

    if (notifOpen) renderNotifList(json.data ?? [], '/citizen/requests/');
}

// Boot
loadUser();
pollNotifications();
setInterval(pollNotifications, 8000); // poll every 8 seconds — no lag
</script>
@stack('scripts')
</body>
</html>