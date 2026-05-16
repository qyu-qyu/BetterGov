@extends('admin.layout')
@section('page-title', 'Request Detail')

@section('topbar_actions')
<a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>All Requests
</a>
@endsection

@section('content')
<div class="row g-3">
    <!-- Left column -->
    <div class="col-12 col-lg-7">

        <!-- Request info -->
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-file-earmark-text me-2 text-primary"></i>Request <span id="req-id" class="text-muted"></span></span>
                <span id="req-status-badge"></span>
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
                        <div class="small text-muted mb-1">Office</div>
                        <div id="req-office">—</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Fee</div>
                        <div id="req-fee">—</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Submitted</div>
                        <div class="small text-muted" id="req-date">—</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted mb-1">Notes</div>
                        <div class="text-muted small" id="req-notes">—</div>
                    </div>
                </div>

                <!-- Status update -->
                <div class="border-top pt-3">
                    <div class="small fw-semibold text-muted mb-2 text-uppercase" style="letter-spacing:.5px">Update Status</div>
                    <div class="d-flex gap-2 flex-wrap align-items-end">
                        <div>
                            <label class="form-label small mb-1">New Status</label>
                            <select id="new-status" class="form-select form-select-sm" style="min-width:150px">
                                <option value="pending">Pending</option>
                                <option value="processing">In Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="flex-fill">
                            <label class="form-label small mb-1">Comment (optional)</label>
                            <input type="text" id="status-comment" class="form-control form-control-sm" placeholder="Add a note for the citizen…">
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="updateStatus()">
                            <i class="bi bi-check2 me-1"></i>Update
                        </button>
                    </div>
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

        <!-- Documents -->
        <div class="card">
            <div class="card-header"><i class="bi bi-folder2 me-2 text-warning"></i>Submitted Documents</div>
            <div class="card-body" id="docs-list">
                <p class="text-muted small mb-0">—</p>
            </div>
        </div>
    </div>

    <!-- Right column: chat 
    <div class="col-12 col-lg-5">
        <div class="card" style="height:100%;min-height:520px;display:flex;flex-direction:column;">
            <div class="card-header"><i class="bi bi-chat-dots me-2 text-primary"></i>Conversation</div>
            <div class="card-body flex-fill overflow-auto" id="chat-box"
                 style="display:flex;flex-direction:column;gap:8px;max-height:400px;padding:1rem;">
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
    </div> -->
</div>
@endsection

@push('scripts')
<script>
const reqId = window.location.pathname.split('/').pop();
let currentUserId = null;

async function loadRequest() {
    const meRes = await api('GET', '/me');
    if (meRes && meRes.ok) { const d = await meRes.json(); currentUserId = d.user?.id; }

    const res = await api('GET', `/requests/${reqId}`);
    if (!res || !res.ok) { showAlert('Request not found.', 'danger'); return; }
    const { data: r } = await res.json();

    document.getElementById('req-id').textContent           = '#' + r.id;
    document.getElementById('req-status-badge').innerHTML   = statusBadge(r.status);
    document.getElementById('req-citizen').textContent      = r.user?.name ?? '—';
    document.getElementById('req-email').textContent        = r.user?.email ?? '—';
    document.getElementById('req-service').textContent      = r.service?.name ?? '—';
    document.getElementById('req-office').textContent       = r.office?.name ?? '—';
    document.getElementById('req-fee').textContent          = Number(r.service?.fee) > 0 ? '$' + r.service.fee : 'Free';
    document.getElementById('req-date').textContent         = fmtDateTime(r.created_at);
    document.getElementById('req-notes').textContent        = r.notes || 'None';
    document.getElementById('new-status').value             = r.status;

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

    // Documents
    const docsList = document.getElementById('docs-list');
    if (r.request_documents?.length) {
        docsList.innerHTML = r.request_documents.map(d => `
            <div class="d-flex align-items-center gap-2 py-1 border-bottom">
                <i class="bi bi-file-earmark-pdf text-danger"></i>
                <span class="small">${d.file_name ?? d.file_path?.split('/').pop() ?? 'Document'}</span>
            </div>`).join('');
    } else {
        docsList.innerHTML = '<p class="text-muted small mb-0">No documents submitted.</p>';
    }

    loadMessages();
}

async function loadMessages() {
    const res = await api('GET', `/requests/${reqId}/messages`);
    if (!res || !res.ok) return;
    const { data } = await res.json();
    const box = document.getElementById('chat-box');
    if (!data?.length) {
        box.innerHTML = '<p class="text-muted text-center small mt-3">No messages yet.</p>';
        return;
    }
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
    else { showAlert('Failed to send message.', 'danger'); }
}

async function updateStatus() {
    const status  = document.getElementById('new-status').value;
    const comment = document.getElementById('status-comment').value.trim();
    const res  = await api('PUT', `/requests/${reqId}/status`, { status, comment: comment || null });
    const json = await res.json();
    if (!res.ok) { showAlert(json.message ?? 'Failed to update status.', 'danger'); return; }
    showAlert('Status updated successfully.');
    loadRequest();
}

loadRequest();
</script>
@endpush
