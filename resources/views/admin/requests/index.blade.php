@extends('admin.layout')
@section('page-title', 'Requests')

@section('content')
<!-- Status filter pills -->
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <button class="btn btn-sm btn-primary" data-status="" onclick="setStatus(this,'')">All</button>
    <button class="btn btn-sm btn-outline-warning"  data-status="pending"    onclick="setStatus(this,'pending')">Pending</button>
    <button class="btn btn-sm btn-outline-secondary" data-status="processing" onclick="setStatus(this,'processing')">In Review</button>
    <button class="btn btn-sm btn-outline-success"  data-status="approved"   onclick="setStatus(this,'approved')">Approved</button>
    <button class="btn btn-sm btn-outline-success"  data-status="completed"  onclick="setStatus(this,'completed')">Completed</button>
    <button class="btn btn-sm btn-outline-danger"   data-status="rejected"   onclick="setStatus(this,'rejected')">Rejected</button>
    <div class="ms-auto d-flex gap-2 flex-wrap">
        <div class="input-group" style="max-width:220px">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="search" class="form-control border-start-0" placeholder="Citizen or service…" oninput="applyFilters()">
        </div>
        <select id="filter-office" class="form-select" style="max-width:180px" onchange="applyFilters()">
            <option value="">All offices</option>
        </select>
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
                        <th>Office</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-3"></th>
                    </tr>
                </thead>
                <tbody id="requests-tbody">
                    <tr><td colspan="7" class="text-center py-4">
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
    const [rRes, oRes] = await Promise.all([api('GET', '/requests'), api('GET', '/offices')]);
    if (rRes && rRes.ok) { const d = await rRes.json(); allRequests = d.data ?? d; }
    let offices = [];
    if (oRes && oRes.ok) { const d = await oRes.json(); offices = d.data ?? d; }
    document.getElementById('filter-office').innerHTML =
        '<option value="">All offices</option>' + offices.map(o => `<option value="${o.name}">${o.name}</option>`).join('');
    applyFilters();
}

function setStatus(btn, status) {
    activeStatus = status;
    document.querySelectorAll('[data-status]').forEach(b => {
        b.className = b.className.replace(/btn-(?!sm|outline)\S+/, '');
        if (b.dataset.status === status) {
            b.classList.remove(...[...b.classList].filter(c => c.startsWith('btn-outline')));
            b.classList.add('btn-primary');
        } else {
            if (!b.className.includes('btn-outline')) {
                b.classList.remove('btn-primary');
                const colorMap = {pending:'warning', processing:'secondary', approved:'success', completed:'success', rejected:'danger', '':'secondary'};
                b.classList.add('btn-outline-' + (colorMap[b.dataset.status] ?? 'secondary'));
            }
        }
    });
    applyFilters();
}

function applyFilters() {
    const q      = document.getElementById('search').value.toLowerCase();
    const office = document.getElementById('filter-office').value;
    let filtered = allRequests;
    if (activeStatus) filtered = filtered.filter(r => r.status === activeStatus);
    if (office)       filtered = filtered.filter(r => r.office_name === office);
    if (q)            filtered = filtered.filter(r =>
        (r.citizen_name  || '').toLowerCase().includes(q) ||
        (r.service_name  || '').toLowerCase().includes(q));
    renderTable(filtered);
}

function renderTable(requests) {
    const tbody = document.getElementById('requests-tbody');
    if (!requests.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-6 d-block mb-2"></i>No requests found.
        </td></tr>`;
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
            <td class="text-muted small">${r.office_name ?? '—'}</td>
            <td>${statusBadge(r.status)}</td>
            <td class="text-muted small">${fmtDate(r.created_at)}</td>
            <td class="text-end pe-3">
                <a href="/admin/requests/${r.id}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>View
                </a>
            </td>
        </tr>`).join('');
}

loadAll();
</script>
@endpush
