@extends('admin.layout')
@section('page-title', 'Services')

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <div class="input-group" style="max-width:260px">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="search" class="form-control border-start-0" placeholder="Search service…" oninput="filterTable()">
    </div>
    <select id="filter-office" class="form-select" style="max-width:200px" onchange="filterTable()">
        <option value="">All offices</option>
    </select>
    <select id="filter-cat" class="form-select" style="max-width:200px" onchange="filterTable()">
        <option value="">All categories</option>
    </select>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4" id="stats-row">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-primary" id="stat-total">—</div>
            <div class="small text-muted">Total Services</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-warning" id="stat-fee">—</div>
            <div class="small text-muted">With Fee</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-success" id="stat-free">—</div>
            <div class="small text-muted">Free Services</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-info" id="stat-avg">—</div>
            <div class="small text-muted">Avg. Fee</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Service</th>
                        <th>Office</th>
                        <th>Category</th>
                        <th>Fee</th>
                        <th>Est. Time</th>
                        <th>Req. Docs</th>
                    </tr>
                </thead>
                <tbody id="services-tbody">
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
let allServices = [];

async function loadAll() {
    const [sRes, oRes, cRes] = await Promise.all([
        api('GET', '/services'),
        api('GET', '/offices'),
        api('GET', '/service-categories'),
    ]);
    if (sRes && sRes.ok) { const d = await sRes.json(); allServices = d.data ?? d; }
    let offices = [], cats = [];
    if (oRes && oRes.ok) { const d = await oRes.json(); offices = d.data ?? d; }
    if (cRes && cRes.ok) { const d = await cRes.json(); cats = d.data ?? d; }

    document.getElementById('filter-office').innerHTML =
        '<option value="">All offices</option>' + offices.map(o => `<option value="${o.id}">${o.name}</option>`).join('');
    document.getElementById('filter-cat').innerHTML =
        '<option value="">All categories</option>' + cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

    updateStats();
    renderTable(allServices);
}

function updateStats() {
    const total   = allServices.length;
    const withFee = allServices.filter(s => Number(s.fee) > 0).length;
    const avg     = total ? allServices.reduce((a, s) => a + Number(s.fee || 0), 0) / total : 0;
    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-fee').textContent   = withFee;
    document.getElementById('stat-free').textContent  = total - withFee;
    document.getElementById('stat-avg').textContent   = '$' + avg.toFixed(2);
}

function renderTable(services) {
    const tbody = document.getElementById('services-tbody');
    if (!services.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-grid display-6 d-block mb-2"></i>No services found.</td></tr>';
        return;
    }
    tbody.innerHTML = services.map(s => `
        <tr data-name="${(s.name||'').toLowerCase()}" data-office="${s.office_id}" data-cat="${s.service_category_id ?? s.category?.id ?? ''}">
            <td class="ps-3 fw-semibold">${s.name}</td>
            <td class="small">${s.office?.name ?? '—'}</td>
            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">${s.category?.name ?? '—'}</span></td>
            <td>${Number(s.fee) > 0
                ? `<span class="fw-semibold">$${Number(s.fee).toLocaleString()}</span>`
                : '<span class="badge badge-approved px-2 py-1">Free</span>'}</td>
            <td class="text-muted small">${s.estimated_time ?? '—'}</td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary">${s.document_types?.length ?? 0} docs</span></td>
        </tr>`).join('');
}

function filterTable() {
    const q      = document.getElementById('search').value.toLowerCase();
    const office = document.getElementById('filter-office').value;
    const cat    = document.getElementById('filter-cat').value;
    document.querySelectorAll('#services-tbody tr[data-name]').forEach(r => {
        const ok = (!q      || r.dataset.name.includes(q))
                && (!office || r.dataset.office == office)
                && (!cat    || r.dataset.cat    == cat);
        r.style.display = ok ? '' : 'none';
    });
}

loadAll();
</script>
@endpush
