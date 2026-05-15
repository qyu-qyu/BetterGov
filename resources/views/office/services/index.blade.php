@extends('office.layout')
@section('page-title', 'Services')

@section('topbar_actions')
<a href="{{ route('office.services.templates') }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-layout-text-window-reverse me-1"></i>Browse Templates
</a>
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
                <div class="mb-3" id="service-type-wrap">
                    <label class="form-label fw-semibold">Service Type <span class="text-danger">*</span></label>
                    <select id="f-type" class="form-select">
                        <option value="">Select…</option>
                    </select>
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
                    <div id="template-lock-note" class="alert alert-info py-2 px-3 small d-none mb-2">
                        This service was started from a template. Name, category, estimated time, and required documents are locked.
                        You can edit only fee and description.
                    </div>
                    <div id="template-required-docs" class="mb-2 d-none">
                        <div class="small fw-semibold text-muted mb-2">Template requirements</div>
                        <ul class="list-group list-group-flush border rounded-3 bg-white" id="template-required-docs-list"></ul>
                    </div>
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
let isTemplateMode = false;
let templateDocTypeIds = [];
let templateIdForCreate = null;
let allTypes = [];

async function loadAll() {
    const meRes = await api('GET', '/me');
    if (meRes && meRes.ok) { const d = await meRes.json(); myOfficeId = d.user?.office_id; }

    const [sRes, cRes, dRes] = await Promise.all([
        api('GET', '/services'),
        api('GET', '/service-categories'),
        api('GET', '/service-types'),
        api('GET', '/office-portal/profile'),
    ]);
    if (sRes && sRes.ok) { const d = await sRes.json(); allServices = (d.data ?? d).filter(s => s.office_id == myOfficeId); }
    if (cRes && cRes.ok) { const d = await cRes.json(); allCats = d.data ?? d; }
    if (dRes && dRes.ok) {
        const d = await dRes.json();
        allTypes = d.data ?? d;
    }

    // Populate category dropdown in modal
    const catOpts = allCats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    const typeOpts = allTypes.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    document.getElementById('f-cat').innerHTML     = '<option value="">Select…</option>' + catOpts;
    document.getElementById('f-type').innerHTML    = '<option value="">Select…</option>' + typeOpts;
    document.getElementById('filter-cat').innerHTML = '<option value="">All categories</option>' + catOpts;

    // Load document types from profile
    if (dRes && dRes.ok) {
        // We'll fetch doc types separately; for now just get them from services
    }

    renderTable(allServices);
    await checkTemplateParam();
}

async function checkTemplateParam() {
    const templateId = new URLSearchParams(window.location.search).get('template');
    if (!templateId) return;

    const res = await api('GET', `/service-templates/${templateId}`);
    if (!res.ok) return;
    const data = await res.json();
    const tmpl = data.data ?? data;

    isTemplateMode = true;
    templateIdForCreate = parseInt(templateId);

    document.getElementById('modal-title').textContent = 'New Service (from Template)';
    document.getElementById('edit-id').value = '';
    document.getElementById('f-name').value  = tmpl.name_en ?? '';

    const matchedCat = allCats.find(c => c.name.toLowerCase() === (tmpl.category ?? '').toLowerCase());
    document.getElementById('f-cat').value   = matchedCat ? matchedCat.id : '';
    document.getElementById('f-type').value   = (function() {
        const map = {
            'Municipal Permits': 'Application',
            'Civil Registry': 'Request',
            'Mukhtar Services': 'Request',
            'Public Health': 'Request',
            'General Security': 'Request',
        };
        const typeName = map[tmpl.category] ?? 'Request';
        const found = allTypes.find(t => t.name === typeName);
        return found ? found.id : '';
    })();
    document.getElementById('f-fee').value   = '0';

    const days = tmpl.estimated_days;
    document.getElementById('f-time').value  = days === 1 ? '1 business day' : `${days} business days`;
    document.getElementById('f-desc').value  = tmpl.description ?? '';

    // Match template required doc names to existing document types by exact normalized name.
    const dtRes = await api('GET', '/document-types');
    let types = [];
    if (dtRes && dtRes.ok) {
        const dtData = await dtRes.json();
        types = Array.isArray(dtData) ? dtData : (dtData.data ?? []);
    }
    const normalize = (s) => (s ?? '').toString().trim().toLowerCase().replace(/\s+/g, ' ');
    const typeMap = new Map(types.map(t => [normalize(t.name), t.id]));
    templateDocTypeIds = (tmpl.required_documents ?? [])
        .map(n => typeMap.get(normalize(n)))
        .filter(Boolean);

    const reqDocs = tmpl.required_documents ?? [];
    document.getElementById('template-required-docs').classList.toggle('d-none', !reqDocs.length);
    document.getElementById('template-required-docs-list').innerHTML = reqDocs.length
        ? reqDocs.map(doc => `<li class="list-group-item small py-2 px-3">${doc}</li>`).join('')
        : '';

    await loadDocTypes(templateDocTypeIds, true);
    setTemplateModeUI(true);
    window.history.replaceState({}, '', window.location.pathname);
    new bootstrap.Modal(document.getElementById('serviceModal')).show();
}

function setTemplateModeUI(on) {
    document.getElementById('template-lock-note').classList.toggle('d-none', !on);
    document.getElementById('f-name').disabled = on;
    document.getElementById('f-cat').disabled = on;
    document.getElementById('f-type').disabled = on;
    document.getElementById('f-time').disabled = on;
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
            <td><span class="badge bg-primary bg-opacity-10 text-primary">${s.required_documents_count ?? s.document_types?.length ?? 0} docs</span></td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-secondary me-1" onclick="openEdit(${s.id})"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="openDelete(${s.id})"><i class="bi bi-trash"></i></button>
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

async function loadDocTypes(selectedIds = [], readOnly = false) {
    const res = await api('GET', '/document-types');
    if (!res.ok) { document.getElementById('doc-types-list').innerHTML = '<span class="text-muted small">Failed to load document types.</span>'; return; }
    const data = await res.json();
    const types = Array.isArray(data) ? data : (data.data ?? []);
    document.getElementById('doc-types-list').innerHTML = types.length
        ? types.map(dt => `
            <div class="form-check">
                <input class="form-check-input doc-type-cb" type="checkbox" value="${dt.id}" id="dt-${dt.id}"
                    ${selectedIds.includes(dt.id) ? 'checked' : ''}
                    ${readOnly ? 'disabled' : ''}>
                <label class="form-check-label small" for="dt-${dt.id}">${dt.name}</label>
            </div>`).join('')
        : '<span class="text-muted small">No document types available.</span>';
}

function openCreate() {
    isTemplateMode = false;
    templateDocTypeIds = [];
    templateIdForCreate = null;
    setTemplateModeUI(false);

    document.getElementById('modal-title').textContent = 'New Service';
    document.getElementById('edit-id').value   = '';
    document.getElementById('f-name').value    = '';
    document.getElementById('f-cat').value     = '';
    document.getElementById('f-type').value    = '';
    document.getElementById('f-fee').value     = '0';
    document.getElementById('f-time').value    = '';
    document.getElementById('f-desc').value    = '';
    loadDocTypes([]);
}

function openEdit(id) {
    const s = allServices.find(x => x.id == id);
    if (!s) return;

    isTemplateMode = false;
    templateDocTypeIds = [];
    templateIdForCreate = null;
    setTemplateModeUI(false);

    document.getElementById('modal-title').textContent = 'Edit Service';
    document.getElementById('edit-id').value   = s.id;
    document.getElementById('f-name').value    = s.name ?? '';
    document.getElementById('f-cat').value     = s.service_category_id ?? '';
    document.getElementById('f-type').value    = s.service_type_id ?? '';
    document.getElementById('f-fee').value     = s.fee ?? '0';
    document.getElementById('f-time').value    = s.estimated_time ?? '';
    document.getElementById('f-desc').value    = s.description ?? '';
    const selectedIds = (s.document_types ?? []).map(d => d.id);
    loadDocTypes(selectedIds);
    new bootstrap.Modal(document.getElementById('serviceModal')).show();
}

async function saveService() {
    const id = document.getElementById('edit-id').value;
    const checkedDocTypes = isTemplateMode
        ? templateDocTypeIds
        : [...document.querySelectorAll('.doc-type-cb:checked')].map(cb => parseInt(cb.value));
    const payload = {
        name:                document.getElementById('f-name').value.trim(),
        service_category_id: document.getElementById('f-cat').value,
        service_type_id:     document.getElementById('f-type').value,
        office_id:           myOfficeId,
        fee:                 document.getElementById('f-fee').value || '0',
        estimated_time:      document.getElementById('f-time').value.trim() || null,
        description:         document.getElementById('f-desc').value.trim() || null,
        document_type_ids:   checkedDocTypes,
    };
    if (!id && isTemplateMode && templateIdForCreate) {
        payload.template_id = templateIdForCreate;
    }
    if (!payload.name || !payload.service_category_id) {
        showAlert('Name and category are required.', 'warning'); return;
    }
    if (!payload.service_type_id) {
        showAlert('Service type is required.', 'warning'); return;
    }
    const res  = id ? await api('PUT', `/services/${id}`, payload) : await api('POST', '/services', payload);
    const json = await res.json();
    if (!res.ok) {
        if (res.status === 403) {
            showAlert('Your current session is not authorized for office actions. Please sign in again with an office account.', 'danger');
            return;
        }
        showAlert(Object.values(json.errors ?? {}).flat()[0] ?? json.message, 'danger');
        return;
    }
    bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
    showAlert(id ? 'Service updated.' : 'Service created.');
    loadAll();
}

function openDelete(id) {
    const s = allServices.find(x => x.id == id);
    deleteId = id;
    document.getElementById('del-name').textContent = s?.name ?? '';
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
