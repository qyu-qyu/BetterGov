@extends('citizen.layout')

@section('title', 'Appointments')
@section('page-title', 'Appointments')

@section('content')
<div class="row g-4">
    <!-- Book New Appointment -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-calendar-plus me-2 text-primary"></i>Book an Appointment
            </div>
            <div class="card-body">
                <form id="appt-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Office</label>
                        <select id="appt-office" class="form-select" required onchange="loadSlots()">
                            <option value="">Select an office…</option>
                        </select>
                    </div>

                    <div class="mb-3" id="slot-section" style="display:none">
                        <label class="form-label fw-semibold">Available Time Slots</label>
                        <div id="slots-container" class="row g-2"></div>
                        <input type="hidden" id="selected-slot-id">
                        <input type="hidden" id="selected-day-of-week">
                    </div>

                    <div class="mb-3" id="date-section" style="display:none">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" id="appt-date" class="form-control" required min="{{ now()->toDateString() }}">
                        <div class="form-text" id="date-hint"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea id="appt-notes" class="form-control" rows="2" placeholder="Purpose of visit…"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="book-btn" disabled>
                        <i class="bi bi-calendar-check me-2"></i>Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- My Appointments -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-calendar3 me-2 text-primary"></i>My Appointments</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="loadMyAppointments()">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Office</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="appt-tbody">
                            <tr><td colspan="5" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Slot selection card template handled inline -->
@endsection

@push('scripts')
<script>
let allOffices = [], selectedSlot = null;

async function loadOffices() {
    const res = await fetch('/api/offices', { headers: { Accept: 'application/json' } });
    allOffices = await res.json();
    const sel = document.getElementById('appt-office');
    allOffices.forEach(o => sel.insertAdjacentHTML('beforeend', `<option value="${o.id}">${o.name}</option>`));

    // Pre-select from query string
    const params = new URLSearchParams(window.location.search);
    const preId  = params.get('office_id');
    if (preId) { sel.value = preId; loadSlots(); }
}

async function loadSlots() {
    const officeId = document.getElementById('appt-office').value;
    if (!officeId) return;
    selectedSlot = null;
    document.getElementById('book-btn').disabled = true;
    document.getElementById('slot-section').style.display = 'none';
    document.getElementById('date-section').style.display = 'none';

    const res = await api('GET', `/offices/${officeId}/slots`);
    if (!res || !res.ok) return;
    const data = await res.json();
    const slots = data.data ?? [];

    const container = document.getElementById('slots-container');
    if (!slots.length) {
        container.innerHTML = '<div class="col-12 text-muted small">No time slots available for this office.</div>';
        document.getElementById('slot-section').style.display = 'block';
        return;
    }

    const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    container.innerHTML = slots.map(s => `
        <div class="col-6">
            <div class="card slot-card border" id="slot-${s.id}" onclick="selectSlot(${s.id}, '${s.day_of_week}', '${s.start_time}', '${s.end_time}')"
                style="cursor:pointer;transition:all .15s">
                <div class="card-body py-2 px-3 text-center">
                    <div class="fw-semibold small">${s.day_of_week}</div>
                    <div class="text-muted small">${s.start_time.slice(0,5)} – ${s.end_time.slice(0,5)}</div>
                    <div class="small text-muted">Up to ${s.max_capacity} people</div>
                </div>
            </div>
        </div>`).join('');

    document.getElementById('slot-section').style.display = 'block';
}

function selectSlot(id, day, start, end) {
    document.querySelectorAll('.slot-card').forEach(c => {
        c.style.background = '';
        c.style.borderColor = '';
        c.style.color = '';
    });
    const card = document.getElementById('slot-' + id);
    if (card) {
        card.style.background = '#1a56db';
        card.style.color = '#fff';
        card.querySelector('.text-muted') && (card.querySelectorAll('.text-muted').forEach(e => e.style.color = 'rgba(255,255,255,.8)'));
    }
    selectedSlot = { id, day, start, end };
    document.getElementById('selected-slot-id').value = id;
    document.getElementById('date-hint').textContent = `Select a ${day} date.`;
    document.getElementById('date-section').style.display = 'block';
    document.getElementById('book-btn').disabled = false;

    // Set min date to next occurrence of selected day
    const dayIndex = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'].indexOf(day);
    const today = new Date();
    const diff  = (dayIndex + 7 - today.getDay() + 1) % 7 || 7;
    const next  = new Date(today);
    next.setDate(today.getDate() + diff);
    document.getElementById('appt-date').value = next.toISOString().split('T')[0];
}

document.getElementById('appt-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!selectedSlot) { showAlert('Please select a time slot.', 'warning'); return; }

    const btn = document.getElementById('book-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking…';

    const res = await api('POST', '/appointments', {
        office_id:            document.getElementById('appt-office').value,
        office_time_slot_id:  selectedSlot.id,
        appointment_date_only: document.getElementById('appt-date').value,
        notes:                document.getElementById('appt-notes').value || null,
    });

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-calendar-check me-2"></i>Confirm Booking';

    if (res && res.ok) {
        showAlert('Appointment booked successfully!', 'success');
        document.getElementById('appt-form').reset();
        selectedSlot = null;
        document.getElementById('slot-section').style.display = 'none';
        document.getElementById('date-section').style.display = 'none';
        loadMyAppointments();
    } else {
        const err = await res?.json();
        showAlert(err?.message ?? 'Booking failed.', 'danger');
    }
});

async function loadMyAppointments() {
    const res = await api('GET', '/appointments');
    if (!res || !res.ok) return;
    const data = await res.json();
    const appts = data.data ?? [];
    const tbody = document.getElementById('appt-tbody');

    if (!appts.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No appointments yet.</td></tr>';
        return;
    }

    const statusColors = { pending:'warning', confirmed:'success', cancelled:'danger' };
    tbody.innerHTML = appts.map(a => `
        <tr>
            <td class="ps-3 fw-semibold small">${a.office?.name ?? '—'}</td>
            <td class="small">${fmtDate(a.appointment_date_only)}</td>
            <td class="small text-muted">${a.time_slot ? a.time_slot.start_time?.slice(0,5) + ' – ' + a.time_slot.end_time?.slice(0,5) : '—'}</td>
            <td><span class="badge badge-${statusColors[a.status] ?? 'secondary'} bg-${statusColors[a.status] ?? 'secondary'} bg-opacity-10 text-${statusColors[a.status] ?? 'secondary'}">${a.status}</span></td>
            <td class="text-end pe-3">
                ${a.status !== 'cancelled' ? `<button class="btn btn-sm btn-outline-danger" onclick="cancelAppt(${a.id})">Cancel</button>` : ''}
            </td>
        </tr>`).join('');
}

async function cancelAppt(id) {
    if (!confirm('Cancel this appointment?')) return;
    const res = await api('PATCH', `/appointments/${id}/cancel`);
    if (res && res.ok) { showAlert('Appointment cancelled.', 'success'); loadMyAppointments(); }
    else showAlert('Could not cancel appointment.', 'danger');
}

loadOffices();
loadMyAppointments();
</script>
@endpush
