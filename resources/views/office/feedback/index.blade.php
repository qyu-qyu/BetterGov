@extends('office.layout')
@section('page-title', 'Feedback & Ratings')

@section('content')
<!-- Rating summary -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-warning" id="stat-avg">—</div>
            <div class="small text-muted">Avg. Rating</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-primary" id="stat-total">—</div>
            <div class="small text-muted">Total Reviews</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-success" id="stat-pos">—</div>
            <div class="small text-muted">Positive (4-5★)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-danger" id="stat-neg">—</div>
            <div class="small text-muted">Negative (1-2★)</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0" id="feedback-list">
        <div class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
        </div>
    </div>
</div>

<!-- Respond Modal -->
<div class="modal fade" id="respondModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Respond to Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="respond-id">
                <div class="mb-2 small text-muted" id="respond-preview"></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Your Response</label>
                    <textarea id="f-response" class="form-control" rows="4" placeholder="Write a public or professional response…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="submitResponse()">Send Response</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allFeedback = [];

function stars(n) {
    return '★'.repeat(n) + '☆'.repeat(5 - n);
}

async function loadAll() {
    const res = await api('GET', '/feedback');
    if (!res || !res.ok) return;
    const { data } = await res.json();
    allFeedback = data ?? [];
    renderStats();
    renderList();
}

function renderStats() {
    const total = allFeedback.length;
    const sum   = allFeedback.reduce((a, f) => a + Number(f.rating), 0);
    const pos   = allFeedback.filter(f => f.rating >= 4).length;
    const neg   = allFeedback.filter(f => f.rating <= 2).length;
    document.getElementById('stat-avg').textContent   = total ? (sum / total).toFixed(1) + '★' : '—';
    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-pos').textContent   = pos;
    document.getElementById('stat-neg').textContent   = neg;
}

function renderList() {
    const list = document.getElementById('feedback-list');
    if (!allFeedback.length) {
        list.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-star display-6 d-block mb-2"></i>No feedback yet.</div>';
        return;
    }
    list.innerHTML = allFeedback.map(f => {
        const ratingColor = f.rating >= 4 ? '#10b981' : f.rating >= 3 ? '#f59e0b' : '#ef4444';
        const responses   = f.responses ?? [];
        return `<div class="border-bottom px-4 py-3">
            <div class="d-flex align-items-start gap-3">
                <div style="width:40px;height:40px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;font-size:1rem;color:#1e293b">
                    ${(f.citizen_name ?? '?')[0].toUpperCase()}
                </div>
                <div class="flex-fill">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-semibold">${f.citizen_name ?? '—'}</span>
                        <span class="text-muted" style="font-size:.78rem">${fmtDate(f.created_at)}</span>
                        <span class="ms-auto fw-bold" style="color:${ratingColor}">${stars(f.rating)} <span style="font-size:.8rem">(${f.rating}/5)</span></span>
                    </div>
                    <div class="text-muted small">${f.comment || '<em>No comment.</em>'}</div>
                    ${responses.length ? `<div class="mt-2 p-2 rounded" style="background:#f8fafc;border-left:3px solid #1a56db">
                        <div class="small fw-semibold text-primary mb-1">Office Response:</div>
                        <div class="small">${responses[0].response}</div>
                    </div>` : ''}
                    ${!responses.length ? `<button class="btn btn-sm btn-outline-primary mt-2" onclick="openRespond(${f.id},'${(f.comment||'').replace(/'/g,"\\'")}')">
                        <i class="bi bi-reply me-1"></i>Respond
                    </button>` : ''}
                </div>
            </div>
        </div>`;
    }).join('');
}

function openRespond(id, comment) {
    document.getElementById('respond-id').value = id;
    document.getElementById('respond-preview').textContent = comment ? '"' + comment + '"' : '';
    document.getElementById('f-response').value = '';
    new bootstrap.Modal(document.getElementById('respondModal')).show();
}

async function submitResponse() {
    const id       = document.getElementById('respond-id').value;
    const response = document.getElementById('f-response').value.trim();
    if (!response) { showAlert('Response cannot be empty.', 'warning'); return; }
    const res  = await api('POST', `/feedback/${id}/response`, { response });
    const json = await res.json();
    if (!res.ok) { showAlert(json.message ?? 'Failed.', 'danger'); return; }
    bootstrap.Modal.getInstance(document.getElementById('respondModal')).hide();
    showAlert('Response sent.');
    loadAll();
}

loadAll();
</script>
@endpush
