@extends('citizen.layout')

@section('title', 'Request Details')
@section('page-title', 'Request Details')

@push('head')
<script src="https://js.stripe.com/v3/"></script>
<style>
    #payment-modal-backdrop {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.5); z-index: 2000;
        align-items: center; justify-content: center;
    }
    #payment-modal-backdrop.open { display: flex; }
    #payment-modal {
        background: #fff; border-radius: 16px;
        width: 100%; max-width: 460px; margin: 1rem;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        overflow: hidden;
    }
    .pm-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .pm-body { padding: 1.5rem; }

    #stripe-card-element {
        border: 1px solid #e2e8f0; border-radius: 8px;
        padding: .75rem 1rem; background: #fff;
        transition: border-color .15s;
    }
    #stripe-card-element.StripeElement--focus {
        border-color: #1a56db;
        box-shadow: 0 0 0 3px rgba(26,86,219,.1);
    }
    #stripe-card-element.StripeElement--invalid { border-color: #ef4444; }

    .pay-status-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .3rem .75rem; border-radius: 20px;
        font-size: .8rem; font-weight: 600;
    }
    .pay-status-pill.paid    { background: #d1fae5; color: #065f46; }
    .pay-status-pill.none    { background: #fee2e2; color: #991b1b; }

    #missing-docs-banner { display: none; }
    #missing-docs-banner.active { display: block; }
</style>
@endpush

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
        <span id="pay-status-pill"></span>
        <span class="text-muted small ms-auto" id="req-date"></span>
    </div>

    <div class="row g-4">
        <!-- Left: Info + Timeline -->
        <div class="col-lg-5">
            <!-- Request info card -->
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

            {{-- Payment card --}}
            <div class="card mb-4" id="payment-card" style="display:none!important">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-credit-card me-2 text-primary"></i>Payment</span>
                    <span id="payment-card-status"></span>
                </div>
                <div class="card-body">
                    <div id="payment-paid-info" class="d-none">
                        <div class="d-flex align-items-center gap-2 text-success mb-2">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <span class="fw-semibold">Payment received</span>
                        </div>
                        <div class="small text-muted" id="payment-paid-details"></div>
                    </div>
                    <div id="payment-action">
                        <p class="small text-muted mb-3" id="payment-desc"></p>
                        <button class="btn btn-primary w-100 fw-semibold" onclick="openPaymentModal()">
                            <i class="bi bi-credit-card me-2"></i>Pay Now
                        </button>
                    </div>
                </div>
            </div>

            {{-- QR Code card --}}
            <div class="card mb-4" id="qr-card" style="display:none">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-qr-code me-2 text-primary"></i>QR Tracking Code</span>
                    <a id="qr-download-btn" href="#" download class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                </div>
                <div class="card-body text-center py-3">
                    <div style="display:inline-block;padding:12px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:.75rem;">
                        <img id="qr-img" src="" width="160" height="160" alt="QR Code">
                    </div>
                    <p class="text-muted small mb-1">Scan to track this request</p>
                    <p class="text-muted" style="font-size:.72rem;word-break:break-all" id="qr-url"></p>
                </div>
            </div>
            <!-- qr code card testing
            {{-- Temporary debug card — remove after confirming QR works --}}
            <div class="card mb-4 border-warning">
                <div class="card-body py-2">
                    <p class="small fw-semibold mb-1">QR Debug</p>
                    <p class="small text-muted mb-0" id="qr-debug">Waiting...</p>
                </div>
            </div> -->

            {{-- Status timeline --}}
            <div class="card mb-4">
                <div class="card-header py-3">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Status Timeline
                </div>
                <div class="card-body">
                    <div id="timeline"></div>
                </div>
            </div>

            <!-- Upload documents (shown only when rejected or missing_documents) -->
            <div class="card mb-4 d-none" id="upload-docs-card">
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
                        <li class="list-group-item text-muted small">No documents yet</li>
                    </ul>
                </div>
            </div>

        </div>

        {{-- Right column: Chat --}}
        <div class="col-lg-7">
            <div class="card d-flex flex-column" style="min-height:560px">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-chat-dots me-2 text-primary"></i>Messages</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadMessages()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div id="chat-box" class="flex-grow-1 p-3 overflow-auto" style="max-height:400px;background:#f8fafc">
                    <div class="text-center text-muted small py-4">Loading messages…</div>
                </div>
                <div class="card-footer bg-white border-top">
                    <div class="input-group">
                        <input type="text" id="msg-input" class="form-control"
                            placeholder="Type a message…" onkeydown="if(event.key==='Enter')sendMessage()">
                        <button class="btn btn-primary" onclick="sendMessage()">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Payment modal -->
<div id="payment-modal-backdrop">
    <div id="payment-modal">
        <div class="pm-header">
            <div>
                <div class="fw-bold">Complete Payment</div>
                <div class="text-muted small" id="pm-amount-label"></div>
            </div>
            <button class="btn btn-sm btn-light" onclick="closePaymentModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="pm-body">
            <div id="pm-loading" class="text-center py-4">
                <div class="spinner-border text-primary spinner-border-sm"></div>
                <div class="small text-muted mt-2">Preparing payment…</div>
            </div>
            <div id="pm-form" class="d-none">
                <div class="mb-4">
                    <label class="form-label small fw-semibold mb-2">Card details</label>
                    <div id="stripe-card-element"></div>
                    <div id="card-errors" class="text-danger small mt-2"></div>
                </div>
                <button class="btn btn-primary w-100 fw-semibold py-2" onclick="confirmPayment()" id="pay-btn">
                    <i class="bi bi-lock-fill me-2"></i>Pay <span id="pm-pay-amount"></span>
                </button>
                <div class="text-center mt-3 text-muted" style="font-size:.78rem">
                    <i class="bi bi-shield-lock me-1"></i>Secured by Stripe
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const requestId = "{{ request()->segment(3) }}";
let currentUserId = null;
let stripe = null, cardElement = null;
let activePaymentId = null;
let currentFee = 0;

// ─── Bootstrap ────────────────────────────────────────────────────────────────

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
    if (meRes?.ok) {
        const me = await meRes.json();
        currentUserId = (me.user ?? me).id;
    }

    const res = await api('GET', `/requests/${requestId}`);
    if (!res?.ok) {
        document.getElementById('req-loading').innerHTML = '<p class="text-danger">Request not found.</p>';
        return;
    }
    const data = await res.json();
    const req  = data.data ?? data;

    // Header
    document.getElementById('req-id').textContent          = '#' + req.id;
    document.getElementById('req-status-badge').innerHTML   = statusBadge(req.status);
    document.getElementById('req-date').textContent         = 'Submitted ' + fmtDate(req.created_at);

    // Info
    document.getElementById('info-service').textContent = req.service?.name   ?? '—';
    document.getElementById('info-office').textContent   = req.office?.name   ?? '—';
    document.getElementById('info-fee').textContent      = req.service?.fee   ? '$' + parseFloat(req.service.fee).toFixed(2) : '—';
    document.getElementById('info-time').textContent     = req.service?.estimated_time ?? '—';
    document.getElementById('info-notes').textContent    = req.notes ?? 'None';

    // Upload card — only visible when citizen needs to act on docs
    if (['rejected', 'missing_documents'].includes(req.status)) {
        document.getElementById('upload-docs-card').classList.remove('d-none');
    }

    // Payment
    initPaymentUI(req);

    // Timeline
    renderTimeline(req.status_histories ?? []);

    // Documents
    loadRequiredDocs(req.service?.id);
    loadSubmittedDocs();
    loadResponseDocs();

    // Messages
    loadMessages();

    // QR Code — generate token on the fly if missing (existing requests)
    const qrDebug = document.getElementById('qr-debug');
    if (req.tracking_url) {
        if (qrDebug) qrDebug.textContent = 'tracking_url found: ' + req.tracking_url;
        renderQrCode(req.tracking_url);
    } else if (req.qr_token) {
        const url = window.location.origin + '/track/' + req.qr_token;
        if (qrDebug) qrDebug.textContent = 'built from qr_token: ' + url;
        renderQrCode(url);
    } else {
        if (qrDebug) qrDebug.textContent = 'No token — calling generate-qr...';
        api('POST', `/requests/${requestId}/generate-qr`).then(async (r) => {
            if (r?.ok) {
                const d = await r.json();
                if (qrDebug) qrDebug.textContent = 'generated: ' + JSON.stringify(d);
                if (d.tracking_url) renderQrCode(d.tracking_url);
            } else {
                if (qrDebug) qrDebug.textContent = 'generate-qr failed: ' + r?.status;
            }
        });
    }

    if (new URLSearchParams(window.location.search).get('payment') === 'success') {
        showAlert('Payment processed! Status will update shortly.', 'success');
        history.replaceState({}, '', window.location.pathname);
    }

    document.getElementById('req-loading').classList.add('d-none');
    document.getElementById('req-content').classList.remove('d-none');
}

function renderTimeline(histories) {
    const container = document.getElementById('timeline');
    if (!histories.length) {
        container.innerHTML = '<p class="text-muted small">No status changes yet.</p>';
        return;
    }
    const statusColors = {
        pending:'warning', processing:'info', approved:'success', rejected:'danger', completed:'purple'
    };
    container.innerHTML = histories.map((h, i) => {
        const color = statusColors[h.new_status] ?? 'secondary';
        return `<div class="d-flex gap-3 mb-3">
            <div class="d-flex flex-column align-items-center">
                <div class="rounded-circle bg-${color === 'purple' ? 'primary' : color} text-white d-flex align-items-center justify-content-center"
                    style="width:32px;height:32px;flex-shrink:0">
                    <i class="bi bi-arrow-right-circle-fill small"></i>
                </div>
                ${i < histories.length - 1 ? '<div style="width:2px;flex:1;background:#e2e8f0;margin:4px 0"></div>' : ''}
            </div>
            <div>
                <div class="fw-semibold small">${h.new_status?.charAt(0).toUpperCase() + h.new_status?.slice(1)}</div>
                ${h.comment ? `<div class="text-muted small">${h.comment}</div>` : ''}
                <div class="text-muted" style="font-size:.72rem">${fmtDateTime(h.created_at)}</div>
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

// ── Citizen submitted documents ───────────────────────────────────────────────
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
        list.innerHTML = '<li class="list-group-item text-muted small text-center py-3">No documents uploaded yet.</li>';
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

// ── Required docs hint ────────────────────────────────────────────────────────
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

// ── Payment ───────────────────────────────────────────────────────────────────
let _stripeClientSecret = null;

function initPaymentUI(req) {
    const fee = parseFloat(req.service?.fee ?? 0);
    if (fee <= 0) return;
    currentFee = fee;

    const card = document.getElementById('payment-card');
    card.removeAttribute('style');

    const paid = (req.payments ?? []).find(p => p.status === 'paid');
    const pill = document.getElementById('pay-status-pill');

    if (paid) {
        document.getElementById('payment-card-status').innerHTML =
            '<span class="pay-status-pill paid"><i class="bi bi-check-circle-fill me-1"></i>Paid</span>';
        document.getElementById('payment-paid-info').classList.remove('d-none');
        document.getElementById('payment-paid-details').textContent =
            `$${parseFloat(paid.amount).toFixed(2)} · ${(paid.payment_method ?? 'card').toUpperCase()} · ${fmtDate(paid.created_at)}`;
        document.getElementById('payment-action').classList.add('d-none');
        pill.innerHTML = '<span class="pay-status-pill paid"><i class="bi bi-check-circle-fill me-1"></i>Paid</span>';
    } else {
        document.getElementById('payment-card-status').innerHTML =
            '<span class="pay-status-pill none"><i class="bi bi-exclamation-circle-fill me-1"></i>Unpaid</span>';
        document.getElementById('payment-desc').textContent =
            `A fee of $${fee.toFixed(2)} is required to process this request.`;
        pill.innerHTML = '<span class="pay-status-pill none"><i class="bi bi-exclamation-circle-fill me-1"></i>Unpaid</span>';
    }
}

async function openPaymentModal() {
    document.getElementById('payment-modal-backdrop').classList.add('open');
    document.getElementById('pm-loading').classList.remove('d-none');
    document.getElementById('pm-form').classList.add('d-none');

    const res = await api('POST', '/payments/stripe/intent', { request_id: Number(requestId) });
    if (!res || !res.ok) {
        const j = await res?.json().catch(() => null);
        showAlert(j?.message ?? 'Could not initialize payment.', 'danger');
        closePaymentModal();
        return;
    }
    const { client_secret, payment_id, amount, publishable_key } = await res.json();
    activePaymentId      = payment_id;
    _stripeClientSecret  = client_secret;

    stripe = Stripe(publishable_key);
    const elements = stripe.elements();
    cardElement = elements.create('card', {
        style: { base: { fontSize: '15px', fontFamily: 'Segoe UI, sans-serif', color: '#1e293b' } },
    });
    cardElement.mount('#stripe-card-element');
    cardElement.on('change', e => {
        document.getElementById('card-errors').textContent = e.error?.message ?? '';
    });

    document.getElementById('pm-amount-label').textContent = `Amount due: $${parseFloat(amount).toFixed(2)}`;
    document.getElementById('pm-pay-amount').textContent   = `$${parseFloat(amount).toFixed(2)}`;
    document.getElementById('pm-loading').classList.add('d-none');
    document.getElementById('pm-form').classList.remove('d-none');
}

function closePaymentModal() {
    document.getElementById('payment-modal-backdrop').classList.remove('open');
    if (cardElement) { cardElement.destroy(); cardElement = null; }
    _stripeClientSecret = null;
}

async function confirmPayment() {
    const btn = document.getElementById('pay-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';
    document.getElementById('card-errors').textContent = '';

    const { error, paymentIntent } = await stripe.confirmCardPayment(_stripeClientSecret, {
        payment_method: { card: cardElement },
    });

    if (error) {
        document.getElementById('card-errors').textContent = error.message;
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-lock-fill me-2"></i>Pay $${currentFee.toFixed(2)}`;
        return;
    }

    const confirmRes = await api('POST', '/payments/stripe/confirm', {
        payment_id:        activePaymentId,
        payment_intent_id: paymentIntent.id,
    });

    if (confirmRes?.ok) {
        closePaymentModal();
        showAlert('Payment successful!');
        loadAll();
    } else {
        const j = await confirmRes?.json().catch(() => null);
        showAlert(j?.message ?? 'Payment confirmation failed.', 'danger');
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-lock-fill me-2"></i>Pay $${currentFee.toFixed(2)}`;
    }
}

// ── Download all office docs ───────────────────────────────────────────────────
function downloadAll() {
    document.querySelectorAll('#response-docs .download-btn').forEach((btn, i) => {
        setTimeout(() => btn.click(), i * 600);
    });
}

// ── Messages ──────────────────────────────────────────────────────────────────
async function loadMessages() {
    const res = await api('GET', `/requests/${requestId}/messages`);
    if (!res?.ok) return;
    const msgs = (await res.json()).data ?? [];
    const box  = document.getElementById('chat-box');

    if (!msgs.length) {
        box.innerHTML = '<div class="text-center text-muted small py-4">No messages yet. Start the conversation.</div>';
        return;
    }
    box.innerHTML = msgs.map(m => {
        const isMine = m.sender_id === currentUserId;
        return `<div class="d-flex ${isMine ? 'justify-content-end' : 'justify-content-start'} mb-2">
            <div class="px-3 py-2 rounded-3 small" style="max-width:75%;background:${isMine ? '#1a56db' : '#fff'};color:${isMine ? '#fff' : '#1e293b'};box-shadow:0 1px 3px rgba(0,0,0,.1)">
                ${!isMine ? `<div class="fw-semibold mb-1" style="font-size:.75rem">${m.sender?.name ?? 'Office'}</div>` : ''}
                <div>${m.message}</div>
                <div class="mt-1" style="font-size:.68rem;opacity:.7">${fmtDateTime(m.created_at)}</div>
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
    if (res?.ok) loadMessages();
    else showAlert('Failed to send message.', 'danger');
}

setInterval(loadMessages, 15000);

// ── QR Code ───────────────────────────────────────────────────────────────────

function renderQrCode(trackingUrl) {
    const qrDebug = document.getElementById('qr-debug');
    const card    = document.getElementById('qr-card');
    const urlEl   = document.getElementById('qr-url');
    const dlBtn   = document.getElementById('qr-download-btn');
    const img     = document.getElementById('qr-img');

    if (!img) { if (qrDebug) qrDebug.textContent = 'ERROR: img element not found'; return; }
    if (qrDebug) qrDebug.textContent = 'Fetching QR from server...';

    const token = getToken();
    fetch(`/api/requests/${requestId}/qr-image`, {
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'image/svg+xml',
        }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.blob();
    })
    .then(function(blob) {
        const objectUrl    = URL.createObjectURL(blob);
        img.src            = objectUrl;
        dlBtn.href         = objectUrl;
        dlBtn.download     = 'request-qr-' + requestId + '.svg';
        card.style.display = 'block';
        urlEl.textContent  = trackingUrl;
        if (qrDebug) qrDebug.textContent = 'QR rendered successfully!';
    })
    .catch(function(err) {
        if (qrDebug) qrDebug.textContent = 'QR fetch error: ' + err.message;
    });
}

loadAll();
</script>
@endpush