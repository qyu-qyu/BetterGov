@extends('office.layout')
@section('page-title', 'Services')

@section('topbar_actions')
<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="openCreate()">
    <i class="bi bi-plus-lg me-1"></i>New Service
</button>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <div class="input-group" style="max-width:260px">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="search" class="form-control border-start-0" placeholder="Search service…" oninput="filterTable()">
    </div>
    <select id="filter-cat" class="form-select" style="max-width:200px" onchange="filterTable()">
        <option value="">All categories</option>
    </select>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Service</th>
                        <th>Category</th>
                        <th>Fee</th>
                        <th>Est. Time</th>
                        <th>Req. Docs</th>
                        <th class="text-end pe-3">Actions</th>
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

<!-- Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
                    <input type="text" id="f-name" class="form-control" placeholder="e.g. Birth Certificate">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select id="f-cat" class="form-select">
                            <option value="">Select…</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Fee ($)</label>
                        <input type="number" id="f-fee" class="form-control" value="0" min="0" step="0.01">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Estimated Time</label>
                    <input type="text" id="f-time" class="form-control" placeholder="e.g. 3-5 business days">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea id="f-desc" class="form-control" rows="3" placeholder="What this service provides…"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Required Documents</label>
                    <div id="doc-types-list" class="d-flex flex-wrap gap-2">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveService()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete confirm -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-1">Delete <strong id="del-name"></strong>? This cannot be undone.</div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allServices = [], allCats = [], allDocTypes = [], myOfficeId = null, deleteId = null;

async function loadAll() {
    const meRes = await api('GET', '/me');
    if (meRes && meRes.ok) { const d = await meRes.json(); myOfficeId = d.user?.office_id; }

    const [sRes, cRes, dRes] = await Promise.all([
        api('GET', '/services'),
        api('GET', '/service-categories'),
        api('GET', '/office-portal/profile'),
    ]);
    if (sRes && sRes.ok) { const d = await sRes.json(); allServices = (d.data ?? d).filter(s => s.office_id == myOfficeId); }
    if (cRes && cRes.ok) { const d = await cRes.json(); allCats = d.data ?? d; }

    // Populate category dropdown in modal
    const catOpts = allCats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    document.getElementById('f-cat').innerHTML     = '<option value="">Select…</option>' + catOpts;
    document.getElementById('filter-cat').innerHTML = '<option value="">All categories</option>' + catOpts;

    // Load document types from profile
    if (dRes && dRes.ok) {
        // We'll fetch doc types separately; for now just get them from services
    }

    renderTable(allServices);
}

function renderTable(services) {
    const tbody = document.getElementById('services-tbody');
    if (!services.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-grid display-6 d-block mb-2"></i>No services yet.</td></tr>';
        return;
    }
    tbody.innerHTML = services.map(s => `
        <tr data-name="${(s.name||'').toLowerCase()}" data-cat="${s.service_category_id ?? ''}">
            <td class="ps-3 fw-semibold">${s.name}</td>
            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">${s.category?.name ?? '—'}</span></td>
            <td>${Number(s.fee) > 0
                ? `<span class="fw-semibold">$${Number(s.fee).toLocaleString()}</span>`
                : '<span class="badge badge-approved px-2 py-1">Free</span>'}</td>
            <td class="text-muted small">${s.estimated_time ?? '—'}</td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary">${s.document_types?.length ?? 0} docs</span></td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-secondary me-1" onclick='openEdit(${JSON.stringify(s)})'><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="openDelete(${s.id},'${(s.name||'').replace(/'/g,"\\'")}')"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`).join('');
}

function filterTable() {
    const q   = document.getElementById('search').value.toLowerCase();
    const cat = document.getElementById('filter-cat').value;
    document.querySelectorAll('#services-tbody tr[data-name]').forEach(r => {
        const ok = (!q || r.dataset.name.includes(q)) && (!cat || r.dataset.cat == cat);
        r.style.display = ok ? '' : 'none';
    });
}

async function loadDocTypes(selectedIds = []) {
    const res = await fetch('/api/document-types', { headers: { Accept: 'application/json' } });
    if (!res.ok) { document.getElementById('doc-types-list').innerHTML = '<span class="text-muted small">Failed to load document types.</span>'; return; }
    const data = await res.json();
    const types = Array.isArray(data) ? data : (data.data ?? []);
    document.getElementById('doc-types-list').innerHTML = types.length
        ? types.map(dt => `
            <div class="form-check">
                <input class="form-check-input doc-type-cb" type="checkbox" value="${dt.id}" id="dt-${dt.id}"
                    ${selectedIds.includes(dt.id) ? 'checked' : ''}>
                <label class="form-check-label small" for="dt-${dt.id}">${dt.name}</label>
            </div>`).join('')
        : '<span class="text-muted small">No document types available.</span>';
}

function openCreate() {
    document.getElementById('modal-title').textContent = 'New Service';
    document.getElementById('edit-id').value   = '';
    document.getElementById('f-name').value    = '';
    document.getElementById('f-cat').value     = '';
    document.getElementById('f-fee').value     = '0';
    document.getElementById('f-time').value    = '';
    document.getElementById('f-desc').value    = '';
    loadDocTypes([]);
}

function openEdit(s) {
    document.getElementById('modal-title').textContent = 'Edit Service';
    document.getElementById('edit-id').value   = s.id;
    document.getElementById('f-name').value    = s.name ?? '';
    document.getElementById('f-cat').value     = s.service_category_id ?? '';
    document.getElementById('f-fee').value     = s.fee ?? '0';
    document.getElementById('f-time').value    = s.estimated_time ?? '';
    document.getElementById('f-desc').value    = s.description ?? '';
    const selectedIds = (s.document_types ?? []).map(d => d.id);
    loadDocTypes(selectedIds);
    new bootstrap.Modal(document.getElementById('serviceModal')).show();
}

async function saveService() {
    const id = document.getElementById('edit-id').value;
    const checkedDocTypes = [...document.querySelectorAll('.doc-type-cb:checked')].map(cb => parseInt(cb.value));
    const payload = {
        name:                document.getElementById('f-name').value.trim(),
        service_category_id: document.getElementById('f-cat').value,
        office_id:           myOfficeId,
        fee:                 document.getElementById('f-fee').value || '0',
        estimated_time:      document.getElementById('f-time').value.trim() || null,
        description:         document.getElementById('f-desc').value.trim() || null,
        document_type_ids:   checkedDocTypes,
    };
    if (!payload.name || !payload.service_category_id) {
        showAlert('Name and category are required.', 'warning'); return;
    }
    const res  = id ? await api('PUT', `/services/${id}`, payload) : await api('POST', '/services', payload);
    const json = await res.json();
    if (!res.ok) { showAlert(Object.values(json.errors ?? {}).flat()[0] ?? json.message, 'danger'); return; }
    bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
    showAlert(id ? 'Service updated.' : 'Service created.');
    loadAll();
}

function openDelete(id, name) {
    deleteId = id;
    document.getElementById('del-name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

async function confirmDelete() {
    const res = await api('DELETE', `/services/${deleteId}`);
    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    res.ok ? (showAlert('Service deleted.'), loadAll()) : showAlert('Failed to delete.', 'danger');
}

loadAll();
</script>
@endpush
