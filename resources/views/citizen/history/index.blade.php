@extends('citizen.layout')

@section('title', 'My History')
@section('page-title', 'History & Records')

@section('content')
<!-- Summary cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-primary" id="total-requests">—</div>
            <div class="small text-muted">Total Requests</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-success" id="completed-requests">—</div>
            <div class="small text-muted">Completed</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-info" id="total-payments">—</div>
            <div class="small text-muted">Payments Made</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
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
                            <th>Fee</th>
                            <th>Date</th>
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
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="payments-history-tbody">
                        <tr><td colspan="8" class="text-center py-4">
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
const payStatusColor = { paid: 'success', pending: 'warning', failed: 'danger' };

function showTab(tab) {
    document.getElementById('tab-requests').classList.toggle('d-none', tab !== 'requests');
    document.getElementById('tab-payments').classList.toggle('d-none', tab !== 'payments');
    document.querySelectorAll('#history-tabs .nav-link').forEach((btn, i) => {
        btn.classList.toggle('active',
            (i === 0 && tab === 'requests') || (i === 1 && tab === 'payments'));
    });
}

async function loadHistory() {
    const [reqRes, payRes] = await Promise.all([
        api('GET', '/requests'),
        api('GET', '/payments'),
    ]);

    const reqData  = reqRes?.ok  ? await reqRes.json()  : {};
    const payData  = payRes?.ok  ? await payRes.json()  : {};
    const requests = reqData.data ?? reqData ?? [];
    const payments = payData.data ?? payData ?? [];
    const payArr   = Array.isArray(payments) ? payments : [];

    // Summary
    document.getElementById('total-requests').textContent    = requests.length;
    document.getElementById('completed-requests').textContent = requests.filter(r => r.status === 'completed').length;
    const paidPayments = payArr.filter(p => p.status === 'paid');
    document.getElementById('total-payments').textContent    = paidPayments.length;
    const totalSpent = paidPayments.reduce((sum, p) => sum + parseFloat(p.amount ?? 0), 0);
    document.getElementById('total-spent').textContent       = '$' + totalSpent.toFixed(2);

    // Requests table
    const reqTbody = document.getElementById('requests-history-tbody');
    if (!requests.length) {
        reqTbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">No requests found.</td></tr>';
    } else {
        reqTbody.innerHTML = requests.map(r => `
            <tr>
                <td class="ps-3 text-muted small">#${r.id}</td>
                <td class="fw-semibold small">${r.service_name ?? '—'}</td>
                <td class="text-muted small">${r.office_name ?? '—'}</td>
                <td>${statusBadge(r.status)}</td>
                <td class="small">${r.fee ? '$' + parseFloat(r.fee).toFixed(2) : '—'}</td>
                <td class="text-muted small">${fmtDate(r.created_at)}</td>
                <td class="text-end pe-3">
                    <a href="/citizen/requests/${r.id}" class="btn btn-sm btn-outline-primary py-0">
                        <i class="bi bi-eye me-1"></i>View
                    </a>
                </td>
            </tr>`).join('');
    }

    // Payments table
    const payTbody = document.getElementById('payments-history-tbody');
    if (!payArr.length) {
        payTbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted">No payments found.</td></tr>';
    } else {
        payTbody.innerHTML = payArr.map(p => {
            const col = payStatusColor[p.status] ?? 'secondary';
            const txShort = p.transaction_id
                ? `<span class="font-monospace" style="font-size:.75rem" title="${p.transaction_id}">
                    ${p.transaction_id.slice(0,16)}…</span>`
                : '—';
            const methodIcon = p.payment_method === 'crypto'
                ? '<i class="bi bi-currency-bitcoin text-warning me-1"></i>'
                : '<i class="bi bi-credit-card me-1"></i>';

            return `<tr>
                <td class="ps-3 text-muted small">#${p.id}</td>
                <td class="small fw-semibold">${p.service_name ?? ('Request #' + p.request_id)}</td>
                <td class="fw-semibold">$${parseFloat(p.amount ?? 0).toFixed(2)}</td>
                <td class="small">${methodIcon}${p.payment_method ?? '—'}</td>
                <td>
                    <span class="badge bg-${col} bg-opacity-10 text-${col} px-2 py-1">
                        ${p.status}
                    </span>
                </td>
                <td>${txShort}</td>
                <td class="text-muted small">${fmtDate(p.created_at)}</td>
                <td class="text-end pe-3">
                    <a href="/citizen/requests/${p.request_id}" class="btn btn-sm btn-outline-secondary py-0">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </td>
            </tr>`;
        }).join('');
    }
}

loadHistory();

// Switch to payments tab if ?tab=payments in URL
if (new URLSearchParams(window.location.search).get('tab') === 'payments') {
    showTab('payments');
}
</script>
@endpush