@extends('office.layout')
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
            <div class="fs-2 fw-bold text-info" id="stat-appointments">—</div>
            <div class="small text-muted">Appointments</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Revenue & rating -->
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-currency-dollar fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Total Revenue</div>
                        <div class="fs-4 fw-bold" id="stat-revenue">—</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;background:#fce7f3;border-radius:10px;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-star-fill fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Avg. Rating</div>
                        <div class="fs-4 fw-bold" id="stat-rating">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status breakdown -->
    <div class="col-12 col-md-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>Request Status Breakdown</div>
            <div class="card-body">
                <div id="status-bars">
                    <div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent requests -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Requests</span>
        <a href="{{ route('office.requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Citizen</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-3"></th>
                    </tr>
                </thead>
                <tbody id="recent-tbody">
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
async function loadDashboard() {
    const res = await api('GET', '/office-portal/dashboard');
    if (!res || !res.ok) { showAlert('Failed to load dashboard.', 'danger'); return; }
    const { data } = await res.json();

    const r = data.requests;
    document.getElementById('stat-total').textContent       = r.total;
    document.getElementById('stat-pending').textContent     = r.pending;
    document.getElementById('stat-completed').textContent   = r.completed;
    document.getElementById('stat-appointments').textContent= data.appointments;
    document.getElementById('stat-revenue').textContent     = '$' + Number(data.revenue).toLocaleString('en', { minimumFractionDigits: 2 });
    document.getElementById('stat-rating').textContent      = data.avg_rating ? '★ ' + data.avg_rating : '—';

    // Status bars
    const statuses = [
        { label: 'Pending',        val: r.pending,           color: '#f59e0b' },
        { label: 'In Review',      val: r.processing,        color: '#3b82f6' },
        { label: 'Approved',       val: r.approved,          color: '#10b981' },
        { label: 'Completed',      val: r.completed,         color: '#8b5cf6' },
        { label: 'Rejected',       val: r.rejected,          color: '#ef4444' },
        { label: 'Missing Docs',   val: r.missing_documents, color: '#f97316' },
    ];
    const max = Math.max(...statuses.map(s => s.val), 1);
    document.getElementById('status-bars').innerHTML = statuses.map(s => `
        <div class="mb-2">
            <div class="d-flex justify-content-between small mb-1">
                <span>${s.label}</span><span class="fw-semibold">${s.val}</span>
            </div>
            <div style="background:#e2e8f0;border-radius:4px;height:8px;overflow:hidden">
                <div style="background:${s.color};height:100%;width:${(s.val/max*100).toFixed(1)}%;border-radius:4px;transition:width .4s"></div>
            </div>
        </div>`).join('');

    // Recent requests table
    const tbody = document.getElementById('recent-tbody');
    if (!data.recent_requests?.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No requests yet.</td></tr>';
    } else {
        tbody.innerHTML = data.recent_requests.map(r => `
            <tr>
                <td class="ps-3 text-muted small">#${r.id}</td>
                <td class="fw-semibold">${r.citizen_name ?? '—'}</td>
                <td class="small text-muted">${r.service_name ?? '—'}</td>
                <td>${statusBadge(r.status)}</td>
                <td class="text-muted small">${fmtDate(r.created_at)}</td>
                <td class="text-end pe-3">
                    <a href="/office/requests/${r.id}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>View
                    </a>
                </td>
            </tr>`).join('');
    }
}

loadDashboard();
</script>
@endpush
