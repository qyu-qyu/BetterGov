@extends('admin.layout')
@section('page-title', 'Users')

@section('topbar_actions')
<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openCreate()">
    <i class="bi bi-person-plus me-1"></i>New User
</button>
@endsection

@section('content')
<!-- Filters -->
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <div class="input-group" style="max-width:260px">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="search" class="form-control border-start-0" placeholder="Search name or email…" oninput="filterTable()">
    </div>
    <select id="filter-role" class="form-select" style="max-width:160px" onchange="filterTable()">
        <option value="">All roles</option>
    </select>
    <select id="filter-status" class="form-select" style="max-width:160px" onchange="filterTable()">
        <option value="">All statuses</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    <tr><td colspan="6" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create / Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" id="f-name" class="form-control" placeholder="Enter full name">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" id="f-email" class="form-control" placeholder="email@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Password
                        <span id="pw-hint" class="text-muted fw-normal" style="font-size:.82rem;display:none"> — leave blank to keep current</span>
                    </label>
                    <input type="password" id="f-password" class="form-control" placeholder="Min. 8 characters">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Role</label>
                    <select id="f-role" class="form-select" onchange="toggleOfficeField()">
                        <option value="">Select a role…</option>
                    </select>
                </div>
                <div class="mb-3" id="office-field" style="display:none">
                    <label class="form-label fw-semibold">Assigned Office</label>
                    <select id="f-office" class="form-select">
                        <option value="">None</option>
                    </select>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="f-active" checked>
                    <label class="form-check-label" for="f-active">Account is active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveUser()">Save User</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete confirm -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete User</h5>
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
let allUsers = [], allRoles = [], allOffices = [], deleteId = null;

async function loadAll() {
    const [uRes, rRes, oRes] = await Promise.all([api('GET', '/users'), api('GET', '/roles'), api('GET', '/offices')]);
    if (uRes && uRes.ok) { const d = await uRes.json(); allUsers = d.data ?? d; }
    if (rRes && rRes.ok) { const d = await rRes.json(); allRoles = d.data ?? d; }
    if (oRes && oRes.ok) { const d = await oRes.json(); allOffices = d.data ?? d; }
    populateRoleDropdowns();
    renderTable(allUsers);
}

function populateRoleDropdowns() {
    const opts = allRoles.map(r => `<option value="${r.id}" data-name="${r.name}">${r.name}</option>`).join('');
    document.getElementById('f-role').innerHTML = '<option value="">Select a role…</option>' + opts;
    document.getElementById('filter-role').innerHTML =
        '<option value="">All roles</option>' + allRoles.map(r => `<option value="${r.name}">${r.name}</option>`).join('');
    document.getElementById('f-office').innerHTML =
        '<option value="">None</option>' + allOffices.map(o => `<option value="${o.id}">${o.name}</option>`).join('');
}

function toggleOfficeField() {
    const sel      = document.getElementById('f-role');
    const opt      = sel.options[sel.selectedIndex];
    const roleName = opt?.dataset?.name ?? '';
    document.getElementById('office-field').style.display = roleName === 'office' ? '' : 'none';
}

function renderTable(users) {
    const tbody = document.getElementById('users-tbody');
    if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-people display-6 d-block mb-2"></i>No users found.</td></tr>';
        return;
    }
    tbody.innerHTML = users.map(u => `
        <tr data-name="${(u.name||'').toLowerCase()}" data-email="${(u.email||'').toLowerCase()}" data-role="${u.role?.name||''}" data-status="${u.is_active ? 'active':'inactive'}">
            <td class="ps-3 fw-semibold">${u.name}</td>
            <td class="text-muted small">${u.email}</td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary text-capitalize">${u.role?.name ?? '—'}</span></td>
            <td>
                ${u.is_active
                    ? '<span class="badge badge-approved px-2 py-1">Active</span>'
                    : '<span class="badge badge-inactive px-2 py-1">Inactive</span>'}
            </td>
            <td class="text-muted small">${fmtDate(u.created_at)}</td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-secondary me-1" onclick='openEdit(${JSON.stringify(u)})' title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm ${u.is_active ? 'btn-outline-warning' : 'btn-outline-success'} me-1"
                    onclick="toggleActive(${u.id})" title="${u.is_active ? 'Deactivate' : 'Activate'}">
                    <i class="bi bi-${u.is_active ? 'pause-circle' : 'play-circle'}"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="openDelete(${u.id},'${u.name.replace(/'/g,"\\'")}')">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`).join('');
}

function filterTable() {
    const q      = document.getElementById('search').value.toLowerCase();
    const role   = document.getElementById('filter-role').value;
    const status = document.getElementById('filter-status').value;
    document.querySelectorAll('#users-tbody tr[data-name]').forEach(r => {
        const ok = (!q || r.dataset.name.includes(q) || r.dataset.email.includes(q))
                && (!role   || r.dataset.role   === role)
                && (!status || r.dataset.status === status);
        r.style.display = ok ? '' : 'none';
    });
}

function openCreate() {
    document.getElementById('modal-title').textContent = 'New User';
    document.getElementById('edit-id').value = '';
    document.getElementById('f-name').value = '';
    document.getElementById('f-email').value = '';
    document.getElementById('f-password').value = '';
    document.getElementById('f-role').value = '';
    document.getElementById('f-office').value = '';
    document.getElementById('f-active').checked = true;
    document.getElementById('pw-hint').style.display = 'none';
    document.getElementById('office-field').style.display = 'none';
}

function openEdit(u) {
    document.getElementById('modal-title').textContent = 'Edit User';
    document.getElementById('edit-id').value = u.id;
    document.getElementById('f-name').value  = u.name ?? '';
    document.getElementById('f-email').value = u.email ?? '';
    document.getElementById('f-password').value = '';
    document.getElementById('f-role').value   = allRoles.find(r => r.name === u.role?.name)?.id ?? '';
    document.getElementById('f-office').value = u.office_id ?? '';
    document.getElementById('f-active').checked = !!u.is_active;
    toggleOfficeField();
    document.getElementById('pw-hint').style.display = '';
    new bootstrap.Modal(document.getElementById('userModal')).show();
}

async function saveUser() {
    const id = document.getElementById('edit-id').value;
    const officeId = document.getElementById('f-office').value;
    const payload = {
        name:      document.getElementById('f-name').value.trim(),
        email:     document.getElementById('f-email').value.trim(),
        role_id:   document.getElementById('f-role').value,
        office_id: officeId ? parseInt(officeId) : null,
        is_active: document.getElementById('f-active').checked,
    };
    const pw = document.getElementById('f-password').value;
    if (pw) payload.password = pw;
    else if (!id) { showAlert('Password is required for new users.', 'danger'); return; }

    const res = id ? await api('PUT', `/users/${id}`, payload) : await api('POST', '/users', payload);
    const json = await res.json();
    if (!res.ok) { showAlert(Object.values(json.errors ?? {}).flat()[0] ?? json.message, 'danger'); return; }
    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
    showAlert(id ? 'User updated successfully.' : 'User created successfully.');
    loadAll();
}

async function toggleActive(id) {
    const res = await api('PATCH', `/users/${id}/toggle-active`);
    const json = await res.json();
    if (!res.ok) { showAlert(json.message, 'danger'); return; }
    showAlert(json.message);
    loadAll();
}

function openDelete(id, name) {
    deleteId = id;
    document.getElementById('del-name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

async function confirmDelete() {
    const res = await api('DELETE', `/users/${deleteId}`);
    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    res.ok ? (showAlert('User deleted.'), loadAll()) : showAlert('Failed to delete user.', 'danger');
}

loadAll();
</script>
@endpush
