@extends('admin.layout')
@section('page-title', 'Offices')

@section('topbar_actions')
<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#officeModal" onclick="openCreate()">
    <i class="bi bi-building-add me-1"></i>New Office
</button>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <div class="input-group" style="max-width:260px">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="search" class="form-control border-start-0" placeholder="Search office name…" oninput="filterTable()">
    </div>
    <select id="filter-muni" class="form-select" style="max-width:220px" onchange="filterTable()">
        <option value="">All municipalities</option>
    </select>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Municipality</th>
                        <th>Type</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="offices-tbody">
                    <tr><td colspan="7" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create / Edit Modal -->
<div class="modal fade" id="officeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">New Office</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Office Name <span class="text-danger">*</span></label>
                        <input type="text" id="f-name" class="form-control" placeholder="e.g. Beirut Municipal Office">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Municipality <span class="text-danger">*</span></label>
                        <select id="f-muni" class="form-select"><option value="">Select…</option></select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Office Type <span class="text-danger">*</span></label>
                        <select id="f-type" class="form-select"><option value="">Select…</option></select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" id="f-address" class="form-control" placeholder="Street, district…">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" id="f-phone" class="form-control" placeholder="+961 X XXX XXX">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" id="f-email" class="form-control" placeholder="office@example.gov">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Latitude <span class="text-muted fw-normal">(for map)</span></label>
                        <input type="number" id="f-lat" class="form-control" step="0.0000001" placeholder="e.g. 33.8938">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Longitude <span class="text-muted fw-normal">(for map)</span></label>
                        <input type="number" id="f-lng" class="form-control" step="0.0000001" placeholder="e.g. 35.5018">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Working Hours</label>
                        <input type="text" id="f-hours" class="form-control" placeholder="e.g. Mon–Fri 08:00–16:00">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveOffice()">Save Office</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete confirm -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete Office</h5>
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
let allOffices = [], allMunis = [], deleteId = null;

const OFFICE_TYPE_OPTIONS = [
    { value: 'civil_registry',   label: 'Civil Registry' },
    { value: 'mukhtar',          label: 'Mukhtar' },
    { value: 'municipality',     label: 'Municipality' },
    { value: 'public_health',    label: 'Public Health' },
    { value: 'general_security', label: 'General Security' },
];

async function loadAll() {
    const [oRes, mRes] = await Promise.all([api('GET', '/offices'), api('GET', '/municipalities')]);
    if (oRes && oRes.ok) { const d = await oRes.json(); allOffices = d.data ?? d; }
    if (mRes && mRes.ok) { const d = await mRes.json(); allMunis = d.data ?? d; }
    populateDropdowns();
    renderTable(allOffices);
}

function populateDropdowns() {
    const muniOpts = allMunis.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
    document.getElementById('f-muni').innerHTML      = '<option value="">Select…</option>' + muniOpts;
    document.getElementById('filter-muni').innerHTML = '<option value="">All municipalities</option>' + muniOpts;

    document.getElementById('f-type').innerHTML = '<option value="">Select…</option>' +
        OFFICE_TYPE_OPTIONS.map(t => `<option value="${t.value}">${t.label}</option>`).join('');
}

function renderTable(offices) {
    const tbody = document.getElementById('offices-tbody');
    if (!offices.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-building display-6 d-block mb-2"></i>No offices found.</td></tr>';
        return;
    }
    tbody.innerHTML = offices.map(o => {
        const typeLabel = OFFICE_TYPE_OPTIONS.find(t => t.value === o.office_type)?.label ?? '—';
        return `
        <tr data-name="${(o.name||'').toLowerCase()}" data-muni="${o.municipality_id}">
            <td class="ps-3 fw-semibold">${o.name}</td>
            <td class="small">${o.municipality?.name ?? '—'}</td>
            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">${typeLabel}</span></td>
            <td class="text-muted small">${o.address ?? '—'}</td>
            <td class="text-muted small">${o.phone ?? '—'}</td>
            <td class="text-muted small">${o.email ?? '—'}</td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-secondary me-1" onclick='openEdit(${JSON.stringify(o)})'><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="openDelete(${o.id},'${(o.name||'').replace(/'/g,"\\'")}')"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    }).join('');
}

function filterTable() {
    const q    = document.getElementById('search').value.toLowerCase();
    const muni = document.getElementById('filter-muni').value;
    document.querySelectorAll('#offices-tbody tr[data-name]').forEach(r => {
        r.style.display = ((!q || r.dataset.name.includes(q)) && (!muni || r.dataset.muni == muni)) ? '' : 'none';
    });
}

function openCreate() {
    document.getElementById('modal-title').textContent = 'New Office';
    document.getElementById('edit-id').value = '';
    ['name','address','phone','email','lat','lng','hours'].forEach(f => {
        const el = document.getElementById('f-'+f);
        if (el) el.value = '';
    });
    document.getElementById('f-muni').value = '';
    document.getElementById('f-type').value = '';
}

function openEdit(o) {
    document.getElementById('modal-title').textContent = 'Edit Office';
    document.getElementById('edit-id').value   = o.id;
    document.getElementById('f-name').value    = o.name ?? '';
    document.getElementById('f-muni').value    = o.municipality_id ?? '';
    document.getElementById('f-type').value    = o.office_type ?? '';
    document.getElementById('f-address').value = o.address ?? '';
    document.getElementById('f-phone').value   = o.phone ?? '';
    document.getElementById('f-email').value   = o.email ?? '';
    document.getElementById('f-lat').value     = o.latitude ?? '';
    document.getElementById('f-lng').value     = o.longitude ?? '';
    document.getElementById('f-hours').value   = o.working_hours ?? '';
    new bootstrap.Modal(document.getElementById('officeModal')).show();
}

async function saveOffice() {
    const id = document.getElementById('edit-id').value;
    const payload = {
        name:            document.getElementById('f-name').value.trim(),
        municipality_id: document.getElementById('f-muni').value,
        address:         document.getElementById('f-address').value.trim() || null,
        phone:           document.getElementById('f-phone').value.trim() || null,
        email:           document.getElementById('f-email').value.trim() || null,
        latitude:        document.getElementById('f-lat').value || null,
        longitude:       document.getElementById('f-lng').value || null,
        working_hours:   document.getElementById('f-hours').value.trim() || null,
    };
    const typeEl = document.getElementById('f-type');
    payload.office_type = typeEl.value || null;

    const res  = id ? await api('PUT', `/offices/${id}`, payload) : await api('POST', '/offices', payload);
    const json = await res.json();
    if (!res.ok) { showAlert(Object.values(json.errors ?? {}).flat()[0] ?? json.message, 'danger'); return; }
    bootstrap.Modal.getInstance(document.getElementById('officeModal')).hide();
    showAlert(id ? 'Office updated.' : 'Office created.');
    loadAll();
}

function openDelete(id, name) {
    deleteId = id;
    document.getElementById('del-name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

async function confirmDelete() {
    const res = await api('DELETE', `/offices/${deleteId}`);
    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    res.ok ? (showAlert('Office deleted.'), loadAll()) : showAlert('Failed to delete office.', 'danger');
}

loadAll();
</script>
@endpush
