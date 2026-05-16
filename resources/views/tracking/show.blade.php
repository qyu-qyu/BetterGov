<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Request — BetterGov</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; min-height: 100vh; }
        .topbar {
            background: #0f172a; padding: 1rem 1.5rem;
            display: flex; align-items: center; gap: .75rem;
        }
        .topbar .brand { color: #fff; font-weight: 700; font-size: 1.1rem; }
        .topbar .brand small { color: #64748b; font-size: .75rem; display: block; font-weight: 400; }

        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.07); border-radius: 12px; }

        /* Status badge */
        .status-pill {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .5rem 1.25rem; border-radius: 30px;
            font-weight: 600; font-size: 1rem;
        }
        .s-pending           { background: #fef3c7; color: #92400e; }
        .s-processing        { background: #dbeafe; color: #1e40af; }
        .s-approved          { background: #d1fae5; color: #065f46; }
        .s-rejected          { background: #fee2e2; color: #991b1b; }
        .s-completed         { background: #ede9fe; color: #4c1d95; }
        .s-missing_documents { background: #ffedd5; color: #9a3412; }

        /* Progress bar */
        .progress-steps {
            display: flex; align-items: center; justify-content: center;
            gap: 0; margin: 1.5rem 0;
        }
        .ps-step {
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            flex: 1; position: relative;
        }
        .ps-step:not(:last-child)::after {
            content: ''; position: absolute;
            top: 14px; left: 50%; width: 100%;
            height: 2px; background: #e2e8f0; z-index: 0;
        }
        .ps-step.done:not(:last-child)::after { background: #1a56db; }
        .ps-dot {
            width: 28px; height: 28px; border-radius: 50%;
            background: #e2e8f0; display: flex; align-items: center;
            justify-content: center; font-size: .75rem; z-index: 1;
            position: relative; flex-shrink: 0;
        }
        .ps-step.done .ps-dot   { background: #1a56db; color: #fff; }
        .ps-step.active .ps-dot { background: #1a56db; color: #fff; box-shadow: 0 0 0 4px rgba(26,86,219,.2); }
        .ps-label { font-size: .65rem; color: #94a3b8; text-align: center; }
        .ps-step.done .ps-label, .ps-step.active .ps-label { color: #1e293b; font-weight: 600; }

        /* Timeline */
        .timeline-item { display: flex; gap: .75rem; padding: .6rem 0; }
        .timeline-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #1a56db; flex-shrink: 0; margin-top: 5px;
        }

        /* Not found */
        #not-found { display: none; }

        /* Login prompt */
        .login-prompt {
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 10px; padding: 1rem 1.25rem;
            font-size: .85rem; color: #1e40af;
        }
    </style>
</head>
<body>

<div class="topbar">
    <i class="bi bi-building-fill-gear text-primary fs-5"></i>
    <div class="brand">
        BetterGov
        <small>Government Services Portal</small>
    </div>
</div>

<div class="container" style="max-width:560px; padding: 2rem 1rem;">

    {{-- Loading --}}
    <div id="loading" class="text-center py-5">
        <div class="spinner-border text-primary mb-3"></div>
        <p class="text-muted small">Looking up your request…</p>
    </div>

    {{-- Not found --}}
    <div id="not-found" class="text-center py-5">
        <i class="bi bi-question-circle display-4 text-muted d-block mb-3"></i>
        <h5>Request not found</h5>
        <p class="text-muted small">This QR code may be invalid or expired.</p>
        <a href="/" class="btn btn-outline-primary btn-sm mt-2">Go to BetterGov</a>
    </div>

    {{-- Request card --}}
    <div id="request-card" class="d-none">

        {{-- Header --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <p class="text-muted small mb-1">Request</p>
                        <h5 class="fw-bold mb-0" id="req-id">—</h5>
                    </div>
                    <span id="status-pill" class="status-pill s-pending">—</span>
                </div>
                <hr class="my-2">
                <div class="row g-2 small">
                    <div class="col-6">
                        <span class="text-muted d-block">Service</span>
                        <span class="fw-semibold" id="req-service">—</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Office</span>
                        <span class="fw-semibold" id="req-office">—</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Submitted</span>
                        <span id="req-submitted">—</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Last updated</span>
                        <span id="req-updated">—</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="card mb-3">
            <div class="card-body">
                <p class="small fw-semibold text-muted mb-3">PROGRESS</p>
                <div class="progress-steps" id="progress-steps"></div>
            </div>
        </div>

        {{-- Status history --}}
        <div class="card mb-3" id="history-card">
            <div class="card-body">
                <p class="small fw-semibold text-muted mb-2">STATUS HISTORY</p>
                <div id="history-list"></div>
            </div>
        </div>

        {{-- Office contact --}}
        <div class="card mb-3" id="office-card">
            <div class="card-body">
                <p class="small fw-semibold text-muted mb-2">OFFICE CONTACT</p>
                <p class="small mb-1" id="office-address"></p>
                <p class="small mb-0" id="office-phone"></p>
            </div>
        </div>

        {{-- Login prompt --}}
        <div class="login-prompt mb-3">
            <i class="bi bi-lock-fill me-2"></i>
            To upload documents or message the office, please
            <a href="/login" class="fw-semibold">log in to BetterGov</a>.
        </div>

        {{-- Refresh button --}}
        <div class="text-center">
            <button class="btn btn-outline-secondary btn-sm" onclick="loadStatus()">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh status
            </button>
            <p class="text-muted mt-2" style="font-size:.72rem" id="last-checked"></p>
        </div>

    </div>
</div>

<script>
const token = '{{ $token }}';
const API   = '/api/track/' + token + '/status';

const statusOrder = ['pending', 'processing', 'missing_documents', 'approved', 'completed'];
const statusLabels = {
    pending:           'Pending',
    processing:        'In Review',
    approved:          'Approved',
    rejected:          'Rejected',
    completed:         'Completed',
    missing_documents: 'Missing Docs',
};
const statusIcons = {
    pending:           'bi-hourglass',
    processing:        'bi-search',
    approved:          'bi-check-circle',
    rejected:          'bi-x-circle',
    completed:         'bi-patch-check-fill',
    missing_documents: 'bi-exclamation-triangle',
};
const statusPillClass = {
    pending:           's-pending',
    processing:        's-processing',
    approved:          's-approved',
    rejected:          's-rejected',
    completed:         's-completed',
    missing_documents: 's-missing_documents',
};

function fmtDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}
function fmtDateTime(d) {
    if (!d) return '—';
    return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function loadStatus() {
    try {
        const res  = await fetch(API);
        const json = await res.json();

        document.getElementById('loading').classList.add('d-none');

        if (!res.ok || !json.success) {
            document.getElementById('not-found').style.display = 'block';
            return;
        }

        const d = json.data;
        document.getElementById('request-card').classList.remove('d-none');

        // Header
        document.getElementById('req-id').textContent        = 'Request #' + d.id;
        document.getElementById('req-service').textContent   = d.service_name ?? '—';
        document.getElementById('req-office').textContent    = d.office_name  ?? '—';
        document.getElementById('req-submitted').textContent = fmtDate(d.submitted_at);
        document.getElementById('req-updated').textContent   = fmtDate(d.updated_at);

        // Status pill
        const pill = document.getElementById('status-pill');
        pill.textContent = '';
        const icon = document.createElement('i');
        icon.className = 'bi ' + (statusIcons[d.status] ?? 'bi-bell');
        pill.appendChild(icon);
        pill.appendChild(document.createTextNode(' ' + (statusLabels[d.status] ?? d.status)));
        pill.className = 'status-pill ' + (statusPillClass[d.status] ?? '');

        // Progress steps
        const stepsEl = document.getElementById('progress-steps');
        stepsEl.innerHTML = '';
        const steps = d.status === 'rejected'
            ? ['pending', 'processing', 'rejected']
            : statusOrder;

        const currentIdx = steps.indexOf(d.status);

        steps.forEach((s, i) => {
            const step = document.createElement('div');
            const isDone   = i < currentIdx;
            const isActive = i === currentIdx;
            step.className = 'ps-step' + (isDone ? ' done' : '') + (isActive ? ' active' : '');

            const dot = document.createElement('div');
            dot.className = 'ps-dot';
            dot.innerHTML = isDone
                ? '<i class="bi bi-check"></i>'
                : (isActive ? '<i class="bi bi-circle-fill" style="font-size:.4rem"></i>' : '');

            const label = document.createElement('div');
            label.className = 'ps-label';
            label.textContent = statusLabels[s] ?? s;

            step.appendChild(dot);
            step.appendChild(label);
            stepsEl.appendChild(step);
        });

        // History
        const historyEl = document.getElementById('history-list');
        if (d.history?.length) {
            historyEl.innerHTML = d.history.map(h => `
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div>
                        <div class="fw-semibold small">${h.status}</div>
                        ${h.comment ? `<div class="text-muted small">${h.comment}</div>` : ''}
                        <div class="text-muted" style="font-size:.72rem">${fmtDateTime(h.created_at)}</div>
                    </div>
                </div>`).join('');
        } else {
            document.getElementById('history-card').classList.add('d-none');
        }

        // Office contact
        const addrEl = document.getElementById('office-address');
        const phoneEl = document.getElementById('office-phone');
        if (d.office_address) {
            addrEl.innerHTML = '<i class="bi bi-geo-alt me-1 text-muted"></i>' + d.office_address;
        }
        if (d.office_phone) {
            phoneEl.innerHTML = '<i class="bi bi-telephone me-1 text-muted"></i>' + d.office_phone;
        }
        if (!d.office_address && !d.office_phone) {
            document.getElementById('office-card').classList.add('d-none');
        }

        // Last checked
        document.getElementById('last-checked').textContent =
            'Last checked: ' + new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

    } catch (e) {
        document.getElementById('loading').classList.add('d-none');
        document.getElementById('not-found').style.display = 'block';
    }
}

loadStatus();
// Auto-refresh every 60s — useful if someone keeps the page open
setInterval(loadStatus, 60000);
</script>
</body>
</html>