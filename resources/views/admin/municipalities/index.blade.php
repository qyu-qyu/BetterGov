@extends('admin.layout')
@section('page-title', 'Municipalities')

@section('topbar_actions')
<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#muniModal" onclick="openCreate()">
    <i class="bi bi-map me-1"></i>New Municipality
</button>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3">
    <div class="input-group" style="max-width:280px">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="search" class="form-control border-start-0" placeholder="Search name or city…" oninput="filterTable()">
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>City</th>
                        <th>Offices</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="munis-tbody">
                    <tr><td colspan="4" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="muniModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">New Municipality</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" id="f-name" class="form-control" placeholder="e.g. Beirut">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">City</label>
                    <input type="text" id="f-city" class="form-control" placeholder="e.g. Greater Beirut">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveMuni()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete confirm -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete Municipality</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-1">Delete <strong id="del-name"></strong>? Assigned offices will lose their municipality.</div>
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
let allMunis = [], deleteId = null;

async function loadAll() {
    const res = await api('GET', '/municipalities');
    if (res && res.ok) { const d = await res.json(); allMunis = d.data ?? d; }
    renderTable(allMunis);
}

function renderTable(munis) {
    const tbody = document.getElementById('munis-tbody');
    if (!munis.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-map display-6 d-block mb-2"></i>No municipalities yet.</td></tr>';
        return;
    }
    tbody.innerHTML = munis.map(m => `
        <tr data-q="${((m.name||'')+(m.city||'')).toLowerCase()}">
            <td class="ps-3 fw-semibold">${m.name}</td>
            <td class="text-muted small">${m.city ?? '—'}</td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary">${m.offices_count ?? 0} offices</span></td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-secondary me-1" onclick='openEdit(${JSON.stringify(m)})'><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="openDelete(${m.id},'${(m.name||'').replace(/'/g,"\\'")}')"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`).join('');
}

function filterTable() {
    const q = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('#munis-tbody tr[data-q]').forEach(r => {
        r.style.display = !q || r.dataset.q.includes(q) ? '' : 'none';
    });
}

function openCreate() {
    document.getElementById('modal-title').textContent = 'New Municipality';
    document.getElementById('edit-id').value = '';
    document.getElementById('f-name').value = '';
    document.getElementById('f-city').value = '';
}

function openEdit(m) {
    document.getElementById('modal-title').textContent = 'Edit Municipality';
    document.getElementById('edit-id').value = m.id;
    document.getElementById('f-name').value  = m.name ?? '';
    document.getElementById('f-city').value  = m.city ?? '';
    new bootstrap.Modal(document.getElementById('muniModal')).show();
}

async function saveMuni() {
    const id = document.getElementById('edit-id').value;
    const payload = {
        name: document.getElementById('f-name').value.trim(),
        city: document.getElementById('f-city').value.trim() || null,
    };
    const res  = id ? await api('PUT', `/municipalities/${id}`, payload) : await api('POST', '/municipalities', payload);
    const json = await res.json();
    if (!res.ok) { showAlert(Object.values(json.errors ?? {}).flat()[0] ?? json.message, 'danger'); return; }
    bootstrap.Modal.getInstance(document.getElementById('muniModal')).hide();
    showAlert(id ? 'Municipality updated.' : 'Municipality created.');
    loadAll();
}

function openDelete(id, name) {
    deleteId = id;
    document.getElementById('del-name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

async function confirmDelete() {
    const res = await api('DELETE', `/municipalities/${deleteId}`);
    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    res.ok ? (showAlert('Municipality deleted.'), loadAll()) : showAlert('Failed to delete.', 'danger');
}

loadAll();
</script>
@endpush
