@extends('citizen.layout')

@section('title', 'My Requests')
@section('page-title', 'My Requests')

@section('content')
<!-- Stats row -->
<div class="row g-3 mb-4" id="stats-row">
    @foreach (['pending'=>['Pending','warning','hourglass-split'], 'processing'=>['In Review','info','search'], 'approved'=>['Approved','success','check-circle'], 'completed'=>['Completed','purple','trophy'], 'rejected'=>['Rejected','danger','x-circle']] as $key => $info)
    <div class="col-6 col-md-4 col-lg">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-{{ $info[1] }}" id="stat-{{ $key }}">—</div>
            <div class="small text-muted">{{ $info[0] }}</div>
        </div>
    </div>
    @endforeach
</div>

<!-- Filters -->
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <div class="input-group" style="max-width:260px">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="req-search" class="form-control border-start-0" placeholder="Search…">
    </div>
    <select id="status-filter" class="form-select" style="max-width:160px">
        <option value="">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="processing">In Review</option>
        <option value="approved">Approved</option>
        <option value="completed">Completed</option>
        <option value="rejected">Rejected</option>
    </select>
    <div class="ms-auto">
        <a href="{{ route('citizen.services.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Request
        </a>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Service</th>
                        <th>Office</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="requests-tbody">
                    <tr><td colspan="6" class="text-center py-4">
                        <div class="spinner-border text-primary spinner-border-sm"></div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allRequests = [];

async function loadRequests() {
    const res = await api('GET', '/requests');
    if (!res || !res.ok) return;
    const data = await res.json();
    allRequests = data.data ?? data;
    updateStats();
    renderTable(allRequests);
}

function updateStats() {
    const counts = { pending:0, processing:0, approved:0, completed:0, rejected:0 };
    allRequests.forEach(r => { if (counts[r.status] !== undefined) counts[r.status]++; });
    Object.keys(counts).forEach(k => {
        const el = document.getElementById('stat-' + k);
        if (el) el.textContent = counts[k];
    });
}

function renderTable(requests) {
    const tbody = document.getElementById('requests-tbody');
    if (!requests.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-6 d-block mb-2"></i>No requests found.
            <a href="{{ route('citizen.services.index') }}" class="btn btn-sm btn-primary mt-2">Browse Services</a>
        </td></tr>`;
        return;
    }
    tbody.innerHTML = requests.map(r => `
        <tr>
            <td class="ps-3 text-muted small">#${r.id}</td>
            <td class="fw-semibold">${r.service_name ?? '—'}</td>
            <td class="text-muted small">${r.office_name ?? '—'}</td>
            <td>${statusBadge(r.status)}</td>
            <td class="text-muted small">${fmtDate(r.created_at)}</td>
            <td class="text-end pe-3">
                <a href="/citizen/requests/${r.id}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>View
                </a>
            </td>
        </tr>`).join('');
}

function applyFilters() {
    const search = document.getElementById('req-search').value.toLowerCase();
    const status = document.getElementById('status-filter').value;
    const filtered = allRequests.filter(r => {
        const matchSearch = !search ||
            (r.service_name ?? '').toLowerCase().includes(search) ||
            (r.office_name ?? '').toLowerCase().includes(search);
        const matchStatus = !status || r.status === status;
        return matchSearch && matchStatus;
    });
    renderTable(filtered);
}

document.getElementById('req-search').addEventListener('input', applyFilters);
document.getElementById('status-filter').addEventListener('change', applyFilters);

loadRequests();
</script>
@endpush
