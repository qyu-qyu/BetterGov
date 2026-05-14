@extends('admin.layout')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stat cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-primary" id="stat-total">—</div>
            <div class="small text-muted">Total Requests</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-warning" id="stat-pending">—</div>
            <div class="small text-muted">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-success" id="stat-completed">—</div>
            <div class="small text-muted">Completed</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-info" id="stat-revenue">—</div>
            <div class="small text-muted">Total Revenue</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-3 fw-bold text-secondary" id="stat-processing">—</div>
            <div class="small text-muted">In Review</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-3 fw-bold text-success" id="stat-approved">—</div>
            <div class="small text-muted">Approved</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-3 fw-bold text-danger" id="stat-rejected">—</div>
            <div class="small text-muted">Rejected</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-3 fw-bold text-primary" id="stat-slots">—</div>
            <div class="small text-muted">Active Slots</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Recent requests -->
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Requests</span>
                <a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Citizen</th>
                                <th>Service</th>
                                <th>Office</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recent-requests">
                            <tr><td colspan="5" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick links -->
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-lightning-fill me-2 text-warning"></i>Quick Actions</div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary text-start">
                    <i class="bi bi-person-plus me-2"></i>Manage Users
                </a>
                <a href="{{ route('admin.offices.index') }}" class="btn btn-outline-primary text-start">
                    <i class="bi bi-building me-2"></i>Manage Offices
                </a>
                <a href="{{ route('admin.municipalities.index') }}" class="btn btn-outline-primary text-start">
                    <i class="bi bi-map me-2"></i>Municipalities
                </a>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary text-start">
                    <i class="bi bi-bar-chart-line me-2"></i>Revenue Reports
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadDashboard() {
    const [dashRes, revenueRes, reqRes] = await Promise.all([
        api('GET', '/office/dashboard'),
        api('GET', '/office/dashboard/revenue'),
        api('GET', '/requests'),
    ]);

    if (dashRes && dashRes.ok) {
        const { data } = await dashRes.json();
        document.getElementById('stat-total').textContent      = data.requests.total;
        document.getElementById('stat-pending').textContent    = data.requests.pending;
        document.getElementById('stat-completed').textContent  = data.requests.completed;
        document.getElementById('stat-processing').textContent = data.requests.processing;
        document.getElementById('stat-approved').textContent   = data.requests.approved;
        document.getElementById('stat-rejected').textContent   = data.requests.rejected;
        document.getElementById('stat-slots').textContent      = data.appointments.active_slots;
    }

    if (revenueRes && revenueRes.ok) {
        const { data } = await revenueRes.json();
        document.getElementById('stat-revenue').textContent = '$' + Number(data.total_revenue).toLocaleString('en', { minimumFractionDigits: 2 });
    }

    if (reqRes && reqRes.ok) {
        const { data } = await reqRes.json();
        const tbody = document.getElementById('recent-requests');
        const recent = (data ?? []).slice(0, 8);
        if (!recent.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No requests yet.</td></tr>';
            return;
        }
        tbody.innerHTML = recent.map(r => `
            <tr>
                <td class="ps-3 fw-semibold">${r.citizen_name ?? '—'}</td>
                <td class="small">${r.service_name ?? '—'}</td>
                <td class="text-muted small">${r.office_name ?? '—'}</td>
                <td>${statusBadge(r.status)}</td>
                <td class="text-muted small">${fmtDate(r.created_at)}</td>
            </tr>`).join('');
    }
}

loadDashboard();
</script>
@endpush
