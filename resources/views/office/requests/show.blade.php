@extends('office.layout')
@section('page-title', 'Request Detail')

@section('topbar_actions')
<a href="{{ route('office.requests.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>All Requests
</a>
@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
<div class="row g-3">
    <!-- Left column -->
    <div class="col-12 col-lg-7">

        <!-- Request info card -->
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-file-earmark-text me-2 text-primary"></i>Request <span id="req-id" class="text-muted"></span></span>
                <div class="d-flex gap-2 align-items-center">
                    <span id="req-status-badge"></span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="printDocument()" title="Print / Export PDF">
                        <i class="bi bi-printer"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="small text-muted mb-1">Citizen</div>
                        <div class="fw-semibold" id="req-citizen">—</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Email</div>
                        <div class="small" id="req-email">—</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Service</div>
                        <div class="fw-semibold" id="req-service">—</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Fee</div>
                        <div id="req-fee">—</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted mb-1">Notes</div>
                        <div class="text-muted small" id="req-notes">—</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Submitted</div>
                        <div class="small text-muted" id="req-date">—</div>
                    </div>
                </div>

                <!-- Status update -->
                <div class="border-top pt-3">
                    <div class="small fw-semibold text-muted mb-2 text-uppercase" style="letter-spacing:.5px">Update Status</div>
                    <div class="d-flex gap-2 flex-wrap align-items-end">
                        <div>
                            <label class="form-label small mb-1">New Status</label>
                            <select id="new-status" class="form-select form-select-sm" style="min-width:170px">
                                <option value="pending">Pending</option>
                                <option value="processing">In Review</option>
                                <option value="missing_documents">Missing Documents</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="flex-fill">
                            <label class="form-label small mb-1">Comment (optional)</label>
                            <input type="text" id="status-comment" class="form-control form-control-sm" placeholder="Note for the citizen…">
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="updateStatus()">
                            <i class="bi bi-check2 me-1"></i>Update
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload response document -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-upload me-2 text-success"></i>Upload Response Document</div>
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small mb-1">Title (optional)</label>
                        <input type="text" id="doc-title" class="form-control form-control-sm" placeholder="e.g. Approval Letter">
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small mb-1">File (PDF/image)</label>
                        <input type="file" id="doc-file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="col-12 col-md-2">
                        <button class="btn btn-success btn-sm w-100" onclick="uploadDoc()">
                            <i class="bi bi-cloud-upload me-1"></i>Upload
                        </button>
                    </div>
                </div>
                <div id="docs-list" class="mt-3"></div>
            </div>
        </div>

        <!-- Citizen documents -->
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-folder2 me-2 text-warning"></i>Submitted Documents</span>
                <span id="citizen-docs-count" class="badge bg-secondary bg-opacity-10 text-secondary"></span>
            </div>
            <div class="card-body p-0" id="citizen-docs">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
            </div>
        </div>

        <!-- Status history -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-clock-history me-2 text-secondary"></i>Status History</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">From</th><th>To</th><th>Comment</th><th>Date</th></tr>
                        </thead>
                        <tbody id="history-tbody">
                            <tr><td colspan="4" class="text-center py-3 text-muted">No history yet.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- QR code -->
        <div class="card">
            <div class="card-header"><i class="bi bi-qr-code me-2 text-dark"></i>QR Code — Citizen Tracking</div>
            <div class="card-body d-flex align-items-center gap-4">
                <div id="qr-container"></div>
                <div class="small text-muted">
                    Citizens can scan this QR code to track the status of this request offline.
                </div>
            </div>
        </div>
    </div>

    <!-- Right column: chat -->
    <div class="col-12 col-lg-5">
        <div class="card" style="height:100%;min-height:520px;display:flex;flex-direction:column;">
            <div class="card-header"><i class="bi bi-chat-dots me-2 text-primary"></i>Conversation</div>
            <div class="card-body flex-fill overflow-auto" id="chat-box"
                 style="display:flex;flex-direction:column;gap:8px;max-height:440px;padding:1rem;">
                <p class="text-muted text-center small mt-3">Loading messages…</p>
            </div>
            <div class="card-body border-top" style="flex-shrink:0;">
                <div class="input-group">
                    <input type="text" id="msg-input" class="form-control form-control-sm"
                           placeholder="Type a reply…" onkeydown="if(event.key==='Enter')sendMsg()">
                    <button class="btn btn-primary btn-sm" onclick="sendMsg()">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document preview modal -->
<div class="modal fade" id="docPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold" id="doc-preview-title"></h6>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadCurrentPreview()">
                        <i class="bi bi-download me-1"></i>Download
                    </button>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0" style="min-height:500px">
                <div id="doc-preview-loading" class="d-flex align-items-center justify-content-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>
                <iframe id="doc-preview-frame" class="d-none w-100" style="height:72vh;border:none"></iframe>
                <div id="doc-preview-img-wrap" class="d-none text-center p-3">
                    <img id="doc-preview-img" class="img-fluid" style="max-height:72vh">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print template (hidden) -->
<div id="print-area" style="display:none">
    <div style="font-family:sans-serif;max-width:700px;margin:0 auto;padding:24px">
        <div style="text-align:center;margin-bottom:24px">
            <h2 style="color:#1a56db;margin:0">BetterGov — Official Response</h2>
            <div style="color:#64748b;font-size:14px" id="print-date"></div>
        </div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:16px">
            <tr><td style="padding:6px 8px;border:1px solid #e2e8f0;color:#64748b;width:35%">Request ID</td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0;font-weight:600" id="p-id"></td></tr>
            <tr><td style="padding:6px 8px;border:1px solid #e2e8f0;color:#64748b">Citizen</td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0" id="p-citizen"></td></tr>
            <tr><td style="padding:6px 8px;border:1px solid #e2e8f0;color:#64748b">Service</td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0" id="p-service"></td></tr>
            <tr><td style="padding:6px 8px;border:1px solid #e2e8f0;color:#64748b">Status</td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0;font-weight:600" id="p-status"></td></tr>
            <tr><td style="padding:6px 8px;border:1px solid #e2e8f0;color:#64748b">Notes</td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0" id="p-notes"></td></tr>
        </table>
        <div style="margin-top:40px;text-align:right;color:#64748b;font-size:12px">Generated by BetterGov — Municipal Services Platform</div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
const reqId = window.location.pathname.split('/').pop();
let currentUserId = null, reqData = null;

async function loadRequest() {
    const [meRes, res] = await Promise.all([
        api('GET', '/me'),
        api('GET', `/requests/${reqId}`),
    ]);
    if (meRes && meRes.ok) { const d = await meRes.json(); currentUserId = d.user?.id; }

    if (!res || !res.ok) { showAlert('Request not found.', 'danger'); return; }
    const { data: r } = await res.json();
    reqData = r;

    document.getElementById('req-id').textContent          = '#' + r.id;
    document.getElementById('req-status-badge').innerHTML  = statusBadge(r.status);
    document.getElementById('req-citizen').textContent     = r.user?.name  ?? '—';
    document.getElementById('req-email').textContent       = r.user?.email ?? '—';
    document.getElementById('req-service').textContent     = r.service?.name ?? '—';
    document.getElementById('req-fee').textContent         = Number(r.service?.fee) > 0 ? '$' + r.service.fee : 'Free';
    document.getElementById('req-notes').textContent       = r.notes || 'None';
    document.getElementById('req-date').textContent        = fmtDateTime(r.created_at);
    document.getElementById('new-status').value            = r.status;

    // Print template data
    document.getElementById('p-id').textContent      = '#' + r.id;
    document.getElementById('p-citizen').textContent = r.user?.name ?? '—';
    document.getElementById('p-service').textContent = r.service?.name ?? '—';
    document.getElementById('p-status').textContent  = r.status;
    document.getElementById('p-notes').textContent   = r.notes || '—';
    document.getElementById('print-date').textContent= 'Printed on ' + new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'long', year:'numeric' });

    // Status history
    const histBody = document.getElementById('history-tbody');
    if (r.status_histories?.length) {
        histBody.innerHTML = r.status_histories.map(h => `
            <tr>
                <td class="ps-3">${statusBadge(h.old_status)}</td>
                <td>${statusBadge(h.new_status)}</td>
                <td class="text-muted small">${h.comment ?? '—'}</td>
                <td class="text-muted small">${fmtDateTime(h.created_at)}</td>
            </tr>`).join('');
    }

    renderCitizenDocs(r.request_documents ?? []);
    loadResponseDocs();
    loadMessages();

    // QR code — last, so a CDN failure never blocks the rest
    if (typeof QRCode !== 'undefined') {
        QRCode.toCanvas(document.createElement('canvas'), window.location.href, { width: 120 }, (err, canvas) => {
            if (!err) document.getElementById('qr-container').appendChild(canvas);
        });
    }
}

function renderCitizenDocs(docs) {
    const container  = document.getElementById('citizen-docs');
    const countBadge = document.getElementById('citizen-docs-count');

    if (!docs.length) {
        container.innerHTML = '<p class="text-muted small p-3 mb-0">No documents submitted yet.</p>';
        countBadge.textContent = '0';
        return;
    }

    countBadge.textContent = docs.length;

    const iconFor = name => {
        const ext = (name ?? '').split('.').pop().toLowerCase();
        if (ext === 'pdf')                                    return 'bi-file-earmark-pdf text-danger';
        if (['jpg','jpeg','png','gif','webp'].includes(ext)) return 'bi-file-earmark-image text-success';
        return 'bi-file-earmark text-primary';
    };

    container.innerHTML = `
        <ul class="list-group list-group-flush">
            ${docs.map(d => {
                const name = d.file_name ?? 'Document';
                const safeName = name.replace(/'/g, "\\'");
                return `
                <li class="list-group-item d-flex align-items-center gap-3 py-2 px-3"
                    style="cursor:pointer" onclick="previewDoc(${d.id}, '${safeName}')"
                    title="Click to view">
                    <i class="bi ${iconFor(name)} fs-5 flex-shrink-0"></i>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-semibold small text-truncate">${name}</div>
                        ${d.document_type ? `<div class="text-muted" style="font-size:.75rem">${d.document_type.name}</div>` : ''}
                        <div class="text-muted" style="font-size:.72rem">${fmtDateTime(d.created_at)}</div>
                    </div>
                    <i class="bi bi-eye text-muted flex-shrink-0"></i>
                </li>`;
            }).join('')}
        </ul>`;
}

let _previewBlobUrl = null, _previewFileName = null;

async function previewDoc(id, fileName) {
    _previewFileName = fileName;
    document.getElementById('doc-preview-title').textContent = fileName;
    document.getElementById('doc-preview-frame').classList.add('d-none');
    document.getElementById('doc-preview-img-wrap').classList.add('d-none');
    document.getElementById('doc-preview-loading').classList.remove('d-none');

    const modal = new bootstrap.Modal(document.getElementById('docPreviewModal'));
    modal.show();

    const res = await api('GET', `/request-documents/${id}/download`);
    if (!res || !res.ok) { showAlert('Could not load document.', 'danger'); return; }

    const blob = await res.blob();
    if (_previewBlobUrl) URL.revokeObjectURL(_previewBlobUrl);
    _previewBlobUrl = URL.createObjectURL(blob);

    document.getElementById('doc-preview-loading').classList.add('d-none');

    const ext = fileName.split('.').pop().toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
        document.getElementById('doc-preview-img').src = _previewBlobUrl;
        document.getElementById('doc-preview-img-wrap').classList.remove('d-none');
    } else {
        document.getElementById('doc-preview-frame').src = _previewBlobUrl;
        document.getElementById('doc-preview-frame').classList.remove('d-none');
    }
}

function downloadCurrentPreview() {
    if (!_previewBlobUrl || !_previewFileName) return;
    const a = document.createElement('a');
    a.href = _previewBlobUrl;
    a.download = _previewFileName;
    a.click();
}

async function loadResponseDocs() {
    const res = await api('GET', `/requests/${reqId}/response-documents`);
    if (!res || !res.ok) return;
    const { data } = await res.json();
    const list = document.getElementById('docs-list');
    if (!data?.length) { list.innerHTML = '<p class="text-muted small mb-0">No documents uploaded yet.</p>'; return; }
    list.innerHTML = data.map(d => `
        <div class="d-flex align-items-center gap-2 py-1 border-bottom">
            <i class="bi bi-file-earmark-pdf text-danger"></i>
            <span class="small fw-semibold">${d.title ?? '—'}</span>
            <span class="small text-muted">${d.file_name}</span>
            <span class="text-muted small ms-auto">${fmtDate(d.created_at)}</span>
        </div>`).join('');
}

async function uploadDoc() {
    const file  = document.getElementById('doc-file').files[0];
    const title = document.getElementById('doc-title').value.trim();
    if (!file) { showAlert('Please select a file.', 'warning'); return; }
    const form = new FormData();
    form.append('request_id', reqId);
    form.append('file', file);
    if (title) form.append('title', title);
    const res = await api('POST', '/response-documents', form, true);
    if (res && res.ok) {
        document.getElementById('doc-file').value  = '';
        document.getElementById('doc-title').value = '';
        showAlert('Document uploaded.');
        loadResponseDocs();
    } else {
        const j = await res.json();
        showAlert(j.message ?? 'Upload failed.', 'danger');
    }
}

async function loadMessages() {
    const res = await api('GET', `/requests/${reqId}/messages`);
    if (!res || !res.ok) return;
    const { data } = await res.json();
    const box = document.getElementById('chat-box');
    if (!data?.length) { box.innerHTML = '<p class="text-muted text-center small mt-3">No messages yet.</p>'; return; }
    box.innerHTML = data.map(m => {
        const mine = m.sender_id === currentUserId;
        return `<div style="display:flex;flex-direction:${mine ? 'row-reverse' : 'row'};gap:8px;align-items:flex-end;">
            <div style="max-width:78%;background:${mine ? '#dbeafe' : '#f8fafc'};border:1px solid #e2e8f0;border-radius:10px;padding:8px 12px;">
                <div style="font-size:13px;color:#1e293b;">${m.message ?? ''}</div>
                <div style="font-size:10px;color:#94a3b8;margin-top:3px;text-align:${mine ? 'right' : 'left'}">${fmtDateTime(m.created_at)}</div>
            </div>
        </div>`;
    }).join('');
    box.scrollTop = box.scrollHeight;
}

async function sendMsg() {
    const input = document.getElementById('msg-input');
    const body  = input.value.trim();
    if (!body) return;
    const res = await api('POST', '/messages', { request_id: Number(reqId), message: body });
    if (res && res.ok) { input.value = ''; loadMessages(); }
    else showAlert('Failed to send.', 'danger');
}

async function updateStatus() {
    const status  = document.getElementById('new-status').value;
    const comment = document.getElementById('status-comment').value.trim();
    const res  = await api('PUT', `/requests/${reqId}/status`, { status, comment: comment || null });
    const json = await res.json();
    if (!res.ok) { showAlert(json.message ?? 'Failed to update.', 'danger'); return; }
    showAlert('Status updated.');
    loadRequest();
}

function printDocument() {
    const printContent = document.getElementById('print-area').innerHTML;
    const win = window.open('', '_blank', 'width=800,height=600');
    win.document.write('<html><head><title>Request #' + reqId + '</title></head><body>' + printContent + '</body></html>');
    win.document.close();
    win.print();
}

loadRequest();
// Refresh messages every 30s
setInterval(loadMessages, 30000);
</script>
@endpush
