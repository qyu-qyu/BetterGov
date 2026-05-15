@extends('office.layout')
@section('page-title', 'Requests')

@section('content')
<!-- Status filter pills -->
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <button class="btn btn-sm btn-primary" data-status="" onclick="setStatus(this,'')">All</button>
    <button class="btn btn-sm btn-outline-warning"   data-status="pending"           onclick="setStatus(this,'pending')">Pending</button>
    <button class="btn btn-sm btn-outline-secondary" data-status="processing"        onclick="setStatus(this,'processing')">In Review</button>
    <button class="btn btn-sm btn-outline-warning"   data-status="missing_documents" onclick="setStatus(this,'missing_documents')">Missing Docs</button>
    <button class="btn btn-sm btn-outline-success"   data-status="approved"          onclick="setStatus(this,'approved')">Approved</button>
    <button class="btn btn-sm btn-outline-success"   data-status="completed"         onclick="setStatus(this,'completed')">Completed</button>
    <button class="btn btn-sm btn-outline-danger"    data-status="rejected"          onclick="setStatus(this,'rejected')">Rejected</button>
    <div class="ms-auto">
        <div class="input-group" style="max-width:220px">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="search" class="form-control border-start-0" placeholder="Citizen or service…" oninput="applyFilters()">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Citizen</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-3"></th>
                    </tr>
                </thead>
                <tbody id="requests-tbody">
                    <tr><td colspan="6" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allRequests = [], activeStatus = '';

async function loadAll() {
    const res = await api('GET', '/requests');
    if (res && res.ok) { const d = await res.json(); allRequests = d.data ?? d; }
    applyFilters();
}

function setStatus(btn, status) {
    activeStatus = status;
    document.querySelectorAll('[data-status]').forEach(b => {
        const colorMap = {
            pending: 'warning', processing: 'secondary', missing_documents: 'warning',
            approved: 'success', completed: 'success', rejected: 'danger', '': 'secondary'
        };
        b.classList.remove('btn-primary', ...['warning','secondary','success','danger','info'].map(c => 'btn-outline-' + c));
        if (b.dataset.status === status) {
            b.classList.add('btn-primary');
        } else {
            b.classList.add('btn-outline-' + (colorMap[b.dataset.status] ?? 'secondary'));
        }
    });
    applyFilters();
}

function applyFilters() {
    const q = document.getElementById('search').value.toLowerCase();
    let filtered = allRequests;
    if (activeStatus) filtered = filtered.filter(r => r.status === activeStatus);
    if (q)            filtered = filtered.filter(r =>
        (r.citizen_name  || '').toLowerCase().includes(q) ||
        (r.service_name  || '').toLowerCase().includes(q));
    renderTable(filtered);
}

function renderTable(requests) {
    const tbody = document.getElementById('requests-tbody');
    if (!requests.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-6 d-block mb-2"></i>No requests found.</td></tr>`;
        return;
    }
    tbody.innerHTML = requests.map(r => `
        <tr>
            <td class="ps-3 text-muted small">#${r.id}</td>
            <td>
                <div class="fw-semibold">${r.citizen_name ?? '—'}</div>
                <div class="text-muted" style="font-size:.78rem">${r.citizen_email ?? ''}</div>
            </td>
            <td class="small">${r.service_name ?? '—'}</td>
            <td>${statusBadge(r.status)}</td>
            <td class="text-muted small">${fmtDate(r.created_at)}</td>
            <td class="text-end pe-3">
                <a href="/office/requests/${r.id}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>View
                </a>
            </td>
        </tr>`).join('');
}

loadAll();
</script>
@endpush
