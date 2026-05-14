@extends('admin.layout')
@section('page-title', 'Reports & Analytics')

@section('content')
<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-warning" id="total-revenue">—</div>
            <div class="small text-muted">Total Revenue</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-primary" id="total-requests">—</div>
            <div class="small text-muted">Total Requests</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-success" id="total-completed">—</div>
            <div class="small text-muted">Completed</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-danger" id="total-rejected">—</div>
            <div class="small text-muted">Rejected</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Requests per office -->
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-building me-2 text-primary"></i>Requests per Office</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Office</th>
                                <th>Total</th>
                                <th>Pending</th>
                                <th>Completed</th>
                                <th>Rejected</th>
                            </tr>
                        </thead>
                        <tbody id="rpo-tbody">
                            <tr><td colspan="5" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue per office -->
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-currency-dollar me-2 text-warning"></i>Revenue per Office</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Office</th>
                                <th>Payments</th>
                                <th>Revenue</th>
                                <th style="min-width:120px">Share</th>
                            </tr>
                        </thead>
                        <tbody id="rev-tbody">
                            <tr><td colspan="4" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-pie-chart me-2 text-primary"></i>Requests by Status</div>
            <div class="card-body d-flex align-items-center justify-content-center" style="height:280px;">
                <canvas id="status-chart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-warning"></i>Revenue by Office</div>
            <div class="card-body" style="height:280px;">
                <canvas id="revenue-chart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
async function loadReports() {
    const [dashRes, rpoRes, revRes] = await Promise.all([
        api('GET', '/office/dashboard'),
        api('GET', '/office/dashboard/requests-per-office'),
        api('GET', '/office/dashboard/revenue'),
    ]);

    if (dashRes && dashRes.ok) {
        const { data } = await dashRes.json();
        const r = data.requests;
        document.getElementById('total-requests').textContent  = r.total;
        document.getElementById('total-completed').textContent = r.completed;
        document.getElementById('total-rejected').textContent  = r.rejected;

        new Chart(document.getElementById('status-chart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Review', 'Approved', 'Completed', 'Rejected'],
                datasets: [{
                    data: [r.pending, r.processing, r.approved, r.completed, r.rejected],
                    backgroundColor: ['#fbbf24', '#60a5fa', '#34d399', '#10b981', '#f87171'],
                    borderColor: '#fff',
                    borderWidth: 2,
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, color: '#475569' } }
                },
                cutout: '62%',
            }
        });
    }

    if (rpoRes && rpoRes.ok) {
        const { data } = await rpoRes.json();
        const tbody = document.getElementById('rpo-tbody');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No data.</td></tr>';
        } else {
            tbody.innerHTML = data.map(o => `
                <tr>
                    <td class="ps-3 fw-semibold">${o.name}</td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary">${o.total_requests}</span></td>
                    <td><span class="badge badge-pending px-2 py-1">${o.pending_requests}</span></td>
                    <td><span class="badge badge-completed px-2 py-1">${o.completed_requests}</span></td>
                    <td><span class="badge badge-rejected px-2 py-1">${o.rejected_requests}</span></td>
                </tr>`).join('');
        }
    }

    if (revRes && revRes.ok) {
        const { data } = await revRes.json();
        document.getElementById('total-revenue').textContent =
            '$' + Number(data.total_revenue).toLocaleString('en', { minimumFractionDigits: 2 });

        const total = Number(data.total_revenue) || 1;
        const tbody = document.getElementById('rev-tbody');
        if (!data.per_office.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No revenue data.</td></tr>';
        } else {
            tbody.innerHTML = data.per_office.map(o => {
                const pct = ((Number(o.revenue) / total) * 100).toFixed(1);
                return `<tr>
                    <td class="ps-3 fw-semibold">${o.name}</td>
                    <td class="text-muted small">${o.payment_count}</td>
                    <td class="fw-semibold text-warning">$${Number(o.revenue).toLocaleString('en', { minimumFractionDigits: 2 })}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="flex-fill" style="background:#e2e8f0;border-radius:4px;height:6px;overflow:hidden;">
                                <div style="background:#f59e0b;height:100%;width:${pct}%;border-radius:4px;"></div>
                            </div>
                            <span class="small text-muted">${pct}%</span>
                        </div>
                    </td>
                </tr>`;
            }).join('');

            new Chart(document.getElementById('revenue-chart'), {
                type: 'bar',
                data: {
                    labels: data.per_office.map(o => o.name),
                    datasets: [{
                        label: 'Revenue ($)',
                        data:  data.per_office.map(o => Number(o.revenue)),
                        backgroundColor: '#1a56db',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: '#64748b' }, grid: { color: '#f1f5f9' } },
                        y: { ticks: { color: '#64748b' }, grid: { color: '#f1f5f9' } },
                    }
                }
            });
        }
    }
}

loadReports();
</script>
@endpush
