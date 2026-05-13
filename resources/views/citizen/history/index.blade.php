@extends('citizen.layout')

@section('title', 'My History')
@section('page-title', 'History & Records')

@section('content')
<!-- Summary cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-primary" id="total-requests">—</div>
            <div class="small text-muted">Total Requests</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-success" id="completed-requests">—</div>
            <div class="small text-muted">Completed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-info" id="total-payments">—</div>
            <div class="small text-muted">Payments</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-warning" id="total-spent">—</div>
            <div class="small text-muted">Total Paid</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="history-tabs">
    <li class="nav-item">
        <button class="nav-link active" onclick="showTab('requests')">
            <i class="bi bi-file-earmark-text me-1"></i>Requests
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="showTab('payments')">
            <i class="bi bi-credit-card me-1"></i>Payments
        </button>
    </li>
</ul>

<!-- Requests tab -->
<div id="tab-requests">
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
                            <th>Date</th>
                            <th>Documents</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="requests-history-tbody">
                        <tr><td colspan="7" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payments tab -->
<div id="tab-payments" class="d-none">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Request</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="payments-history-tbody">
                        <tr><td colspan="6" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showTab(tab) {
    document.getElementById('tab-requests').classList.toggle('d-none', tab !== 'requests');
    document.getElementById('tab-payments').classList.toggle('d-none', tab !== 'payments');
    document.querySelectorAll('#history-tabs .nav-link').forEach((btn, i) => {
        btn.classList.toggle('active', (i === 0 && tab === 'requests') || (i === 1 && tab === 'payments'));
    });
}

async function loadHistory() {
    const [reqRes, payRes] = await Promise.all([
        api('GET', '/requests'),
        api('GET', '/payments'),
    ]);

    const reqData = reqRes && reqRes.ok ? await reqRes.json() : {};
    const payData = payRes && payRes.ok ? await payRes.json() : {};

    const requests = reqData.data ?? reqData ?? [];
    const payments = Array.isArray(payData) ? payData : (payData.data ?? []);

    // Summary
    document.getElementById('total-requests').textContent   = requests.length;
    document.getElementById('completed-requests').textContent = requests.filter(r => r.status === 'completed').length;
    document.getElementById('total-payments').textContent    = payments.length;
    const totalSpent = payments.filter(p => p.status === 'paid').reduce((sum, p) => sum + parseFloat(p.amount ?? 0), 0);
    document.getElementById('total-spent').textContent      = '$' + totalSpent.toFixed(2);

    // Requests table
    const reqTbody = document.getElementById('requests-history-tbody');
    if (!requests.length) {
        reqTbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">No requests found.</td></tr>';
    } else {
        reqTbody.innerHTML = requests.map(r => `
            <tr>
                <td class="ps-3 text-muted small">#${r.id}</td>
                <td class="fw-semibold">${r.service_name ?? '—'}</td>
                <td class="text-muted small">${r.office_name ?? '—'}</td>
                <td>${statusBadge(r.status)}</td>
                <td class="text-muted small">${fmtDate(r.created_at)}</td>
                <td>
                    ${['approved','completed'].includes(r.status)
                        ? `<button class="btn btn-sm btn-outline-secondary" onclick="viewDocs(${r.id})">
                            <i class="bi bi-folder2-open me-1"></i>Docs
                           </button>`
                        : '<span class="text-muted small">—</span>'}
                </td>
                <td class="text-end pe-3">
                    <a href="/citizen/requests/${r.id}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>View
                    </a>
                </td>
            </tr>`).join('');
    }

    // Payments table
    const payTbody = document.getElementById('payments-history-tbody');
    if (!payments.length) {
        payTbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No payments found.</td></tr>';
    } else {
        const payStatusColor = { paid:'success', pending:'warning', failed:'danger' };
        payTbody.innerHTML = payments.map(p => `
            <tr>
                <td class="ps-3 text-muted small">#${p.id}</td>
                <td class="small">Request #${p.request_id ?? '—'}</td>
                <td class="fw-semibold">$${parseFloat(p.amount ?? 0).toFixed(2)}</td>
                <td class="text-muted small">${p.payment_method ?? '—'}</td>
                <td><span class="badge bg-${payStatusColor[p.status] ?? 'secondary'} bg-opacity-10 text-${payStatusColor[p.status] ?? 'secondary'}">${p.status}</span></td>
                <td class="text-muted small">${fmtDate(p.created_at)}</td>
            </tr>`).join('');
    }
}

// Documents modal (simple)
async function viewDocs(requestId) {
    const res = await api('GET', `/requests/${requestId}/response-documents`);
    if (!res || !res.ok) return;
    const data = await res.json();
    const docs = data.data ?? data;
    if (!docs.length) { showAlert('No documents available for this request.', 'info'); return; }
    docs.forEach(d => window.open('/storage/' + d.file_path, '_blank'));
}

loadHistory();
</script>
@endpush
