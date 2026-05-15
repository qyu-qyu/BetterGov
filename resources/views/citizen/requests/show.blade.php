@extends('citizen.layout')

@section('title', 'Request Details')
@section('page-title', 'Request Details')

@section('content')
<div id="req-loading" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
</div>

<div id="req-content" class="d-none">

    <!-- Header bar -->
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <a href="{{ route('citizen.requests.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <h5 class="mb-0 fw-bold">Request <span id="req-id"></span></h5>
        <span id="req-status-badge"></span>
        <span class="text-muted small ms-auto" id="req-date"></span>
    </div>

    <!-- Completion banner (visible only when status = completed) -->
    <div id="completion-banner" class="d-none mb-4">
        <div class="d-flex align-items-center gap-3 p-4 rounded-3 border border-success"
             style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%)">
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                 style="width:48px;height:48px;flex-shrink:0">
                <i class="bi bi-check-lg fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-success fs-6">Request Completed</div>
                <div class="text-success-emphasis small mt-1">
                    Your documents are ready. Download your certificates and files from the panel below.
                </div>
            </div>
            <button class="btn btn-success btn-sm px-3"
                    onclick="document.getElementById('office-docs-card').scrollIntoView({behavior:'smooth'})">
                <i class="bi bi-download me-1"></i>Get Documents
            </button>
        </div>
    </div>

    <div class="row g-4">

        <!-- Left: Info + Timeline + Documents -->
        <div class="col-lg-5">

            <!-- Request info -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Request Info
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Service</dt>
                        <dd class="col-7 fw-semibold" id="info-service">—</dd>
                        <dt class="col-5 text-muted">Office</dt>
                        <dd class="col-7" id="info-office">—</dd>
                        <dt class="col-5 text-muted">Fee</dt>
                        <dd class="col-7" id="info-fee">—</dd>
                        <dt class="col-5 text-muted">Est. Time</dt>
                        <dd class="col-7" id="info-time">—</dd>
                        <dt class="col-5 text-muted">Notes</dt>
                        <dd class="col-7 text-muted" id="info-notes">—</dd>
                    </dl>
                </div>
            </div>

            <!-- Status timeline -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Status Timeline
                </div>
                <div class="card-body">
                    <div id="timeline"></div>
                </div>
            </div>

            <!-- Upload documents -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <i class="bi bi-cloud-upload me-2 text-primary"></i>Upload Documents
                </div>
                <div class="card-body">
                    <div id="required-docs-hint" class="mb-3 d-none">
                        <div class="small fw-semibold text-muted mb-2">Required by this service:</div>
                        <ul class="list-unstyled mb-0 small" id="required-docs-list"></ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Document Type <span class="text-muted fw-normal">(optional)</span></label>
                        <select id="upload-doc-type" class="form-select form-select-sm">
                            <option value="">Select type…</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">File <span class="text-danger">*</span></label>
                        <input type="file" id="upload-doc-file" class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">PDF, JPG or PNG · max 5 MB</div>
                    </div>
                    <button class="btn btn-primary btn-sm w-100" onclick="uploadDocument()">
                        <i class="bi bi-cloud-upload me-1"></i>Upload
                    </button>
                </div>
            </div>

            <!-- My uploaded documents (what the citizen submitted) -->
            <div class="card mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-folder2-open me-2 text-primary"></i>My Uploaded Documents</span>
                    <span id="submitted-docs-count" class="badge bg-secondary bg-opacity-10 text-secondary"></span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="submitted-docs">
                        <li class="list-group-item text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Documents from office: certificates, receipts, responses -->
            <div class="card" id="office-docs-card">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <span>
                        <i class="bi bi-file-earmark-arrow-down me-2 text-primary"></i>Documents from Office
                    </span>
                    <button id="download-all-btn" class="btn btn-sm btn-outline-primary d-none"
                            onclick="downloadAll()">
                        <i class="bi bi-download me-1"></i>Download All
                    </button>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="response-docs">
                        <li class="list-group-item text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- Right: Chat -->
        <div class="col-lg-7">
            <div class="card h-100 d-flex flex-column" style="min-height:520px">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-chat-dots me-2 text-primary"></i>Messages</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadMessages()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div id="chat-box" class="flex-grow-1 p-3 overflow-auto"
                     style="max-height:400px;background:#f8fafc">
                    <div class="text-center text-muted small py-4">Loading messages…</div>
                </div>
                <div class="card-footer bg-white border-top">
                    <div class="input-group">
                        <input type="text" id="msg-input" class="form-control"
                            placeholder="Type a message…"
                            onkeydown="if(event.key==='Enter')sendMessage()">
                        <button class="btn btn-primary" onclick="sendMessage()">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
const requestId = {{ request()->segment(3) }};
let currentUserId = null;

// ── File-type helpers ──────────────────────────────────────────────────────────
function fileIcon(name) {
    const ext = (name ?? '').split('.').pop().toLowerCase();
    if (ext === 'pdf')                                    return 'bi-file-earmark-pdf text-danger';
    if (['jpg','jpeg','png','gif','webp'].includes(ext))  return 'bi-file-earmark-image text-info';
    if (['doc','docx'].includes(ext))                     return 'bi-file-earmark-word text-primary';
    return 'bi-file-earmark text-secondary';
}
function fileBg(name) {
    const ext = (name ?? '').split('.').pop().toLowerCase();
    if (ext === 'pdf')                                    return 'bg-danger';
    if (['jpg','jpeg','png','gif','webp'].includes(ext))  return 'bg-info';
    if (['doc','docx'].includes(ext))                     return 'bg-primary';
    return 'bg-secondary';
}

// ── Shared list-item template ──────────────────────────────────────────────────
function docRow(d, mode) {
    const name  = d.file_name ?? d.file_path?.split('/').pop() ?? 'document';
    const label = mode === 'response' ? (d.title ?? name) : name;
    const sub   = mode === 'response'
        ? fmtDate(d.created_at)
        : (d.document_type?.name ?? null);
    return `
    <li class="list-group-item d-flex align-items-center gap-3 py-3">
        <div class="rounded-2 d-flex align-items-center justify-content-center ${fileBg(name)} bg-opacity-10"
             style="width:40px;height:40px;flex-shrink:0">
            <i class="bi ${fileIcon(name)} fs-5"></i>
        </div>
        <div class="flex-grow-1 overflow-hidden">
            <div class="fw-semibold small text-truncate">${label}</div>
            ${sub ? `<div class="text-muted" style="font-size:.75rem">${sub}</div>` : ''}
        </div>
        <button type="button"
           class="btn btn-sm btn-outline-secondary download-btn flex-shrink-0" title="Download"
           onclick="downloadDocument(${d.id}, '${mode}', '${name.replace(/'/g, "\\'")}')">
            <i class="bi bi-download"></i>
        </button>
    </li>`;
}

async function downloadDocument(id, mode, filename) {
    const token = localStorage.getItem('citizen_token');
    const endpoint = mode === 'response'
        ? `/api/response-documents/${id}/download`
        : `/api/request-documents/${id}/download`;

    try {
        const res = await fetch(endpoint, {
            headers: token ? { 'Authorization': 'Bearer ' + token, 'Accept': 'application/octet-stream' } : { 'Accept': 'application/octet-stream' },
        });

        if (!res.ok) {
            showAlert('Failed to download document.', 'danger');
            return;
        }

        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename || 'document';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    } catch (error) {
        showAlert('Failed to download document.', 'danger');
    }
}

// ── Main load ──────────────────────────────────────────────────────────────────
async function loadAll() {
    const meRes = await api('GET', '/me');
    if (meRes && meRes.ok) {
        const me = await meRes.json();
        currentUserId = (me.user ?? me).id;
    }

    const res = await api('GET', `/requests/${requestId}`);
    if (!res || !res.ok) {
        document.getElementById('req-loading').innerHTML = '<p class="text-danger">Request not found.</p>';
        return;
    }
    const data = await res.json();
    const req  = data.data ?? data;

    document.getElementById('req-id').textContent          = '#' + req.id;
    document.getElementById('req-status-badge').innerHTML   = statusBadge(req.status);
    document.getElementById('req-date').textContent         = 'Submitted ' + fmtDate(req.created_at);

    if (req.status === 'completed') {
        document.getElementById('completion-banner').classList.remove('d-none');
    }

    document.getElementById('info-service').textContent = req.service?.name           ?? '—';
    document.getElementById('info-office').textContent   = req.office?.name            ?? '—';
    document.getElementById('info-fee').textContent      = req.service?.fee
        ? '$' + parseFloat(req.service.fee).toFixed(2) : '—';
    document.getElementById('info-time').textContent     = req.service?.estimated_time ?? '—';
    document.getElementById('info-notes').textContent    = req.notes ?? 'None';

    renderTimeline(req.status_histories ?? []);

    loadRequiredDocs(req.service?.id);
    loadResponseDocs();
    loadSubmittedDocs();
    loadMessages();

    document.getElementById('req-loading').classList.add('d-none');
    document.getElementById('req-content').classList.remove('d-none');

    // Auto-scroll when arriving from the index download shortcut
    if (window.location.hash === '#office-docs-card') {
        setTimeout(() => document.getElementById('office-docs-card')
            ?.scrollIntoView({behavior:'smooth'}), 350);
    }
}

// ── Timeline ──────────────────────────────────────────────────────────────────
function renderTimeline(histories) {
    const container = document.getElementById('timeline');
    if (!histories.length) {
        container.innerHTML = '<p class="text-muted small">No status changes yet.</p>';
        return;
    }
    const colors = {
        pending:'warning', processing:'info', approved:'success',
        rejected:'danger', completed:'success', missing_documents:'warning',
    };
    container.innerHTML = histories.map((h, i) => {
        const color = colors[h.new_status] ?? 'secondary';
        return `<div class="d-flex gap-3 mb-3">
            <div class="d-flex flex-column align-items-center">
                <div class="rounded-circle bg-${color} text-white d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;flex-shrink:0">
                    <i class="bi bi-arrow-right-circle-fill small"></i>
                </div>
                ${i < histories.length - 1
                    ? '<div style="width:2px;flex:1;background:#e2e8f0;margin:4px 0"></div>' : ''}
            </div>
            <div>
                <div class="fw-semibold small">
                    ${(h.new_status ?? '').charAt(0).toUpperCase()
                        + (h.new_status ?? '').slice(1).replace(/_/g,' ')}
                </div>
                ${h.comment ? `<div class="text-muted small">${h.comment}</div>` : ''}
                <div class="text-muted" style="font-size:.75rem">${fmtDateTime(h.created_at)}</div>
            </div>
        </div>`;
    }).join('');
}

// ── Documents from office ─────────────────────────────────────────────────────
async function loadResponseDocs() {
    const res  = await api('GET', `/requests/${requestId}/response-documents`);
    const list = document.getElementById('response-docs');
    if (!res || !res.ok) {
        list.innerHTML = '<li class="list-group-item text-muted small text-center py-3">Could not load documents.</li>';
        return;
    }
    const data = await res.json();
    const docs = data.data ?? data;
    if (!docs.length) {
        list.innerHTML = '<li class="list-group-item text-muted small text-center py-3">No documents from office yet.</li>';
        return;
    }
    document.getElementById('download-all-btn').classList.remove('d-none');
    list.innerHTML = docs.map(d => docRow(d, 'response')).join('');
}

// ── Citizen's submitted documents ─────────────────────────────────────────────
async function loadSubmittedDocs() {
    const res  = await api('GET', `/requests/${requestId}/documents`);
    const list = document.getElementById('submitted-docs');
    if (!res || !res.ok) {
        list.innerHTML = '<li class="list-group-item text-muted small text-center py-3">Could not load documents.</li>';
        return;
    }
    const data = await res.json();
    const docs = data.data ?? data;
    document.getElementById('submitted-docs-count').textContent = docs.length || '';
    if (!docs.length) {
        list.innerHTML = '<li class="list-group-item text-muted small text-center py-3">No documents uploaded.</li>';
        return;
    }
    list.innerHTML = docs.map(d => docRow(d, 'submitted')).join('');
}

// ── Upload document ────────────────────────────────────────────────────────────
async function uploadDocument() {
    const file = document.getElementById('upload-doc-file').files[0];
    if (!file) { showAlert('Please select a file.', 'warning'); return; }

    const form = new FormData();
    form.append('request_id', requestId);
    form.append('file', file);
    const docTypeId = document.getElementById('upload-doc-type').value;
    if (docTypeId) form.append('document_type_id', docTypeId);

    const token = localStorage.getItem('citizen_token');
    const res = await fetch('/api/request-documents', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
        body: form,
    });
    if (res && res.ok) {
        document.getElementById('upload-doc-file').value = '';
        document.getElementById('upload-doc-type').value = '';
        showAlert('Document uploaded successfully.');
        loadSubmittedDocs();
    } else {
        const json = await res?.json();
        showAlert(json?.message ?? 'Upload failed.', 'danger');
    }
}

// ── Required documents hint ────────────────────────────────────────────────────
async function loadRequiredDocs(serviceId) {
    if (!serviceId) return;
    const res = await api('GET', `/services/${serviceId}/required-documents`);
    if (!res || !res.ok) return;
    const data = await res.json();
    const docs = data.data ?? data;
    if (!docs.length) return;

    const select = document.getElementById('upload-doc-type');
    docs.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.document_type_id ?? d.id;
        opt.textContent = d.document_type?.name ?? d.name ?? `Type ${d.id}`;
        select.appendChild(opt);
    });

    const list = document.getElementById('required-docs-list');
    list.innerHTML = docs.map(d => {
        const name = d.document_type?.name ?? d.name ?? `Type ${d.id}`;
        const req  = d.is_required ? '<span class="text-danger ms-1">*required</span>' : '';
        return `<li class="mb-1"><i class="bi bi-dot text-primary"></i>${name}${req}</li>`;
    }).join('');
    document.getElementById('required-docs-hint').classList.remove('d-none');
}

// ── Download All (staggered to avoid browser pop-up blocking) ─────────────────
function downloadAll() {
    document.querySelectorAll('#response-docs .download-btn').forEach((btn, i) => {
        setTimeout(() => btn.click(), i * 600);
    });
}

// ── Messages ──────────────────────────────────────────────────────────────────
async function loadMessages() {
    const res = await api('GET', `/requests/${requestId}/messages`);
    if (!res || !res.ok) return;
    const data = await res.json();
    const msgs = data.data ?? data;
    const box  = document.getElementById('chat-box');

    if (!msgs.length) {
        box.innerHTML = '<div class="text-center text-muted small py-4">No messages yet. Start the conversation.</div>';
        return;
    }
    box.innerHTML = msgs.map(m => {
        const isMine = m.sender_id === currentUserId;
        return `<div class="d-flex ${isMine ? 'justify-content-end' : 'justify-content-start'} mb-2">
            <div class="px-3 py-2 rounded-3 small"
                 style="max-width:75%;background:${isMine ? '#1a56db' : '#fff'};color:${isMine ? '#fff' : '#1e293b'};box-shadow:0 1px 3px rgba(0,0,0,.1)">
                ${!isMine ? `<div class="fw-semibold mb-1" style="font-size:.75rem">${m.sender?.name ?? 'Office'}</div>` : ''}
                <div>${m.message}</div>
                <div class="mt-1" style="font-size:.7rem;opacity:.7">${fmtDateTime(m.created_at)}</div>
            </div>
        </div>`;
    }).join('');
    box.scrollTop = box.scrollHeight;
}

async function sendMessage() {
    const input = document.getElementById('msg-input');
    const text  = input.value.trim();
    if (!text) return;
    input.value = '';
    const res = await api('POST', '/messages', { request_id: requestId, message: text });
    if (res && res.ok) loadMessages();
    else showAlert('Failed to send message.', 'danger');
}

loadAll();
</script>
@endpush
