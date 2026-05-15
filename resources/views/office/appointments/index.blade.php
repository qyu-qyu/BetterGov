@extends('office.layout')
@section('page-title', 'Appointments')

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <button class="btn btn-sm btn-primary" data-status="" onclick="setStatus(this,'')">All</button>
    <button class="btn btn-sm btn-outline-warning"   data-status="pending"   onclick="setStatus(this,'pending')">Pending</button>
    <button class="btn btn-sm btn-outline-success"   data-status="confirmed" onclick="setStatus(this,'confirmed')">Confirmed</button>
    <button class="btn btn-sm btn-outline-danger"    data-status="cancelled" onclick="setStatus(this,'cancelled')">Cancelled</button>
    <div class="ms-auto">
        <div class="input-group" style="max-width:220px">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="search" class="form-control border-start-0" placeholder="Search citizen…" oninput="applyFilters()">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Citizen</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Booked</th>
                    </tr>
                </thead>
                <tbody id="appt-tbody">
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
let allAppts = [], activeStatus = '';

async function loadAll() {
    const res = await api('GET', '/appointments');
    if (res && res.ok) { const d = await res.json(); allAppts = d.data ?? d; }
    applyFilters();
}

function setStatus(btn, status) {
    activeStatus = status;
    document.querySelectorAll('[data-status]').forEach(b => {
        b.classList.remove('btn-primary', 'btn-outline-warning', 'btn-outline-success', 'btn-outline-danger', 'btn-outline-secondary');
        const colorMap = { pending: 'warning', confirmed: 'success', cancelled: 'danger', '': 'secondary' };
        if (b.dataset.status === status) b.classList.add('btn-primary');
        else b.classList.add('btn-outline-' + (colorMap[b.dataset.status] ?? 'secondary'));
    });
    applyFilters();
}

function applyFilters() {
    const q = document.getElementById('search').value.toLowerCase();
    let filtered = allAppts;
    if (activeStatus) filtered = filtered.filter(a => a.status === activeStatus);
    if (q) filtered = filtered.filter(a => (a.user?.name || '').toLowerCase().includes(q));
    renderTable(filtered);
}

function renderTable(appts) {
    const tbody = document.getElementById('appt-tbody');
    if (!appts.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-calendar-x display-6 d-block mb-2"></i>No appointments found.</td></tr>';
        return;
    }
    tbody.innerHTML = appts.map(a => {
        const slot  = a.time_slot ?? a.timeSlot;
        const slotLabel = slot ? `${slot.day_of_week} ${slot.start_time}–${slot.end_time}` : '—';
        const statusColors = { pending: 'badge-pending', confirmed: 'badge-approved', cancelled: 'badge-rejected' };
        const badge = `<span class="badge ${statusColors[a.status] ?? 'bg-secondary text-white'} px-2 py-1">${a.status}</span>`;
        return `<tr>
            <td class="ps-3">
                <div class="fw-semibold">${a.user?.name ?? '—'}</div>
                <div class="text-muted" style="font-size:.78rem">${a.user?.email ?? ''}</div>
            </td>
            <td class="small">${fmtDate(a.appointment_date_only)}</td>
            <td class="small text-muted">${slotLabel}</td>
            <td>${badge}</td>
            <td class="text-muted small">${a.notes ?? '—'}</td>
            <td class="text-muted small">${fmtDate(a.created_at)}</td>
        </tr>`;
    }).join('');
}

loadAll();
</script>
@endpush
