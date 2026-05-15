@extends('office.layout')
@section('page-title', 'Time Slots')

@section('topbar_actions')
<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#slotModal" onclick="openCreate()">
    <i class="bi bi-plus-lg me-1"></i>New Slot
</button>
@endsection

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Day</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="slots-tbody">
                    <tr><td colspan="6" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Slot Modal -->
<div class="modal fade" id="slotModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">New Time Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Day of Week</label>
                    <select id="f-day" class="form-select">
                        <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                        <option>Thursday</option><option>Friday</option><option>Saturday</option><option>Sunday</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Start Time</label>
                    <input type="time" id="f-start" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">End Time</label>
                    <input type="time" id="f-end" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Max Capacity</label>
                    <input type="number" id="f-cap" class="form-control" value="10" min="1">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveSlot()">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let myOfficeId = null;

async function loadAll() {
    const meRes = await api('GET', '/me');
    if (meRes && meRes.ok) { const d = await meRes.json(); myOfficeId = d.user?.office_id; }

    const res = await api('GET', '/appointment-slots');
    if (!res || !res.ok) return;
    const { data } = await res.json();
    renderTable(data);
}

function renderTable(slots) {
    const tbody = document.getElementById('slots-tbody');
    if (!slots.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-clock display-6 d-block mb-2"></i>No time slots yet.</td></tr>';
        return;
    }
    tbody.innerHTML = slots.map(s => `
        <tr>
            <td class="ps-3 fw-semibold">${s.day_of_week}</td>
            <td class="small">${s.start_time}</td>
            <td class="small">${s.end_time}</td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary">${s.max_capacity} spots</span></td>
            <td>
                ${s.is_active
                    ? '<span class="badge badge-approved px-2 py-1">Active</span>'
                    : '<span class="badge badge-inactive px-2 py-1">Inactive</span>'}
            </td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-secondary me-1" onclick='openEdit(${JSON.stringify(s)})'><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm ${s.is_active ? 'btn-outline-warning' : 'btn-outline-success'}" onclick="toggleSlot(${s.id})" title="${s.is_active ? 'Deactivate' : 'Activate'}">
                    <i class="bi bi-${s.is_active ? 'pause-circle' : 'play-circle'}"></i>
                </button>
            </td>
        </tr>`).join('');
}

function openCreate() {
    document.getElementById('modal-title').textContent = 'New Time Slot';
    document.getElementById('edit-id').value  = '';
    document.getElementById('f-day').value    = 'Monday';
    document.getElementById('f-start').value  = '08:00';
    document.getElementById('f-end').value    = '09:00';
    document.getElementById('f-cap').value    = '10';
}

function openEdit(s) {
    document.getElementById('modal-title').textContent = 'Edit Time Slot';
    document.getElementById('edit-id').value  = s.id;
    document.getElementById('f-day').value    = s.day_of_week;
    document.getElementById('f-start').value  = s.start_time?.substring(0, 5);
    document.getElementById('f-end').value    = s.end_time?.substring(0, 5);
    document.getElementById('f-cap').value    = s.max_capacity;
    new bootstrap.Modal(document.getElementById('slotModal')).show();
}

async function saveSlot() {
    const id = document.getElementById('edit-id').value;
    const payload = {
        office_id:    myOfficeId,
        day_of_week:  document.getElementById('f-day').value,
        start_time:   document.getElementById('f-start').value,
        end_time:     document.getElementById('f-end').value,
        max_capacity: parseInt(document.getElementById('f-cap').value),
    };
    const res  = id ? await api('PUT', `/appointment-slots/${id}`, payload) : await api('POST', '/appointment-slots', payload);
    const json = await res.json();
    if (!res.ok) { showAlert(Object.values(json.errors ?? {}).flat()[0] ?? json.message, 'danger'); return; }
    bootstrap.Modal.getInstance(document.getElementById('slotModal')).hide();
    showAlert(id ? 'Slot updated.' : 'Slot created.');
    loadAll();
}

async function toggleSlot(id) {
    const res = await api('PATCH', `/appointment-slots/${id}/toggle-active`);
    if (res && res.ok) { showAlert('Slot status changed.'); loadAll(); }
    else showAlert('Failed.', 'danger');
}

loadAll();
</script>
@endpush
