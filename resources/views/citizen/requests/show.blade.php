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

            <!-- Status timeline -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Status Timeline
                </div>
                <div class="card-body">
                    <div id="timeline"></div>
                </div>
            </div>

            <!-- Documents from office -->
            <div class="card">
                <div class="card-header py-3">
                    <i class="bi bi-file-earmark-arrow-down me-2 text-primary"></i>Documents from Office
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="response-docs">
                        <li class="list-group-item text-muted small">No documents yet</li>
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
@endsection

@push('scripts')
<script>
const requestId = {{ request()->segment(3) }};
let currentUserId = null;

async function loadAll() {
    // Get current user
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

    // Timeline
    renderTimeline(req.status_histories ?? []);

    // Response documents
    loadResponseDocs();

    // Messages
    loadMessages();

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
                <div class="text-muted" style="font-size:.75rem">${fmtDateTime(h.created_at)}</div>
            </div>
        </div>`;
    }).join('');
}

async function loadResponseDocs() {
    const res = await api('GET', `/requests/${requestId}/response-documents`);
    if (!res || !res.ok) return;
    const data = await res.json();
    const docs = data.data ?? data;
    const list = document.getElementById('response-docs');
    if (!docs.length) return;
    list.innerHTML = docs.map(d => `
        <li class="list-group-item d-flex align-items-center justify-content-between">
            <span class="small"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>${d.title ?? d.file_path?.split('/').pop()}</span>
            <a href="/storage/${d.file_path}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download"></i>
            </a>
        </li>`).join('');
}

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
            <div class="px-3 py-2 rounded-3 small" style="max-width:75%;background:${isMine ? '#1a56db' : '#fff'};color:${isMine ? '#fff' : '#1e293b'};box-shadow:0 1px 3px rgba(0,0,0,.1)">
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
