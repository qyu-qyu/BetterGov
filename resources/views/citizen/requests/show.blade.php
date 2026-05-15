@extends('citizen.layout')

@section('title', 'Request Details')
@section('page-title', 'Request Details')

@push('head')
<script src="https://js.stripe.com/v3/"></script>
<style>
    #payment-modal-backdrop {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.5); z-index: 2000;
        align-items: center; justify-content: center;
    }
    #payment-modal-backdrop.open { display: flex; }
    #payment-modal {
        background: #fff; border-radius: 16px;
        width: 100%; max-width: 460px; margin: 1rem;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        overflow: hidden;
    }
    .pm-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .pm-body { padding: 1.5rem; }

    #stripe-card-element {
        border: 1px solid #e2e8f0; border-radius: 8px;
        padding: .75rem 1rem; background: #fff;
        transition: border-color .15s;
    }
    #stripe-card-element.StripeElement--focus {
        border-color: #1a56db;
        box-shadow: 0 0 0 3px rgba(26,86,219,.1);
    }
    #stripe-card-element.StripeElement--invalid { border-color: #ef4444; }

    .pay-status-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .3rem .75rem; border-radius: 20px;
        font-size: .8rem; font-weight: 600;
    }
    .pay-status-pill.paid    { background: #d1fae5; color: #065f46; }
    .pay-status-pill.none    { background: #fee2e2; color: #991b1b; }

    #missing-docs-banner { display: none; }
    #missing-docs-banner.active { display: block; }
</style>
@endpush

@section('content')
<div id="req-loading" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
</div>

<div id="req-content" class="d-none">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <a href="{{ route('citizen.requests.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <h5 class="mb-0 fw-bold">Request <span id="req-id"></span></h5>
        <span id="req-status-badge"></span>
        <span id="pay-status-pill"></span>
        <span class="text-muted small ms-auto" id="req-date"></span>
    </div>

    {{-- Missing documents alert --}}
    <div id="missing-docs-banner" class="alert alert-warning d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>
            <strong>Documents required.</strong>
            The office has flagged this request as needing additional documents.
            Please upload the missing files below and message the office.
        </div>
    </div>

    <div class="row g-4">

        {{-- Left column --}}
        <div class="col-lg-5">

            {{-- Request info --}}
            <div class="card mb-4">
                <div class="card-header py-3">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Request Info
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Service</dt>
                        <dd class="col-7 fw-semibold" id="info-service">—</dd>
                        <dt class="col-5 text-muted">Office</dt>
                        <dd class="col-7" id="info-office">—</dd>
                        <dt class="col-5 text-muted">Fee</dt>
                        <dd class="col-7" id="info-fee">—</dd>
                        <dt class="col-5 text-muted">Est. Time</dt>
                        <dd class="col-7" id="info-time">—</dd>
                        <dt class="col-5 text-muted">Notes</dt>
                        <dd class="col-7 text-muted" id="info-notes">—</dd>
                    </dl>
                </div>
            </div>

            {{-- Payment card --}}
            <div class="card mb-4" id="payment-card" style="display:none!important">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-credit-card me-2 text-primary"></i>Payment</span>
                    <span id="payment-card-status"></span>
                </div>
                <div class="card-body">
                    <div id="payment-paid-info" class="d-none">
                        <div class="d-flex align-items-center gap-2 text-success mb-2">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <span class="fw-semibold">Payment received</span>
                        </div>
                        <div class="small text-muted" id="payment-paid-details"></div>
                    </div>
                    <div id="payment-action">
                        <p class="small text-muted mb-3" id="payment-desc"></p>
                        <button class="btn btn-primary w-100 fw-semibold" onclick="openPaymentModal()">
                            <i class="bi bi-credit-card me-2"></i>Pay Now
                        </button>
                    </div>
                </div>
            </div>

            {{-- Upload documents --}}
            <div class="card mb-4">
                <div class="card-header py-3">
                    <i class="bi bi-cloud-upload me-2 text-primary"></i>Upload Documents
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="file" id="extra-doc-file" class="form-control form-control-sm"
                            accept=".pdf,.jpg,.jpeg,.png" multiple>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" onclick="uploadExtraDoc()">
                        <i class="bi bi-upload me-1"></i>Upload
                    </button>
                    <div id="upload-result" class="mt-2 small"></div>
                </div>
            </div>

            {{-- Status timeline --}}
            <div class="card mb-4">
                <div class="card-header py-3">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Status Timeline
                </div>
                <div class="card-body">
                    <div id="timeline"></div>
                </div>
            </div>

            {{-- Documents from office --}}
            <div class="card">
                <div class="card-header py-3">
                    <i class="bi bi-file-earmark-arrow-down me-2 text-primary"></i>Documents from Office
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="response-docs">
                        <li class="list-group-item text-muted small px-3">No documents yet.</li>
                    </ul>
                </div>
            </div>

        </div>

        {{-- Right column: Chat --}}
        <div class="col-lg-7">
            <div class="card d-flex flex-column" style="min-height:560px">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-chat-dots me-2 text-primary"></i>Messages</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadMessages()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div id="chat-box" class="flex-grow-1 p-3 overflow-auto"
                    style="max-height:440px; background:#f8fafc">
                    <div class="text-center text-muted small py-4">Loading messages…</div>
                </div>
                <div class="card-footer bg-white border-top">
                    <div class="input-group">
                        <input type="text" id="msg-input" class="form-control"
                            placeholder="Type a message…"
                            onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); sendMessage(); }">
                        <button class="btn btn-primary" onclick="sendMessage()">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     PAYMENT MODAL (Stripe card only)
══════════════════════════════════════════════════════ --}}
<div id="payment-modal-backdrop" onclick="if(event.target===this) closePaymentModal()">
    <div id="payment-modal">

        <div class="pm-header">
            <div>
                <div class="fw-bold fs-6">Complete Payment</div>
                <div class="text-muted small" id="pm-service-label"></div>
            </div>
            <button class="btn btn-sm btn-light" onclick="closePaymentModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="pm-body">

            <div class="text-center mb-4">
                <div class="text-muted small mb-1">Amount due</div>
                <div class="fs-1 fw-bold text-primary" id="pm-amount">$0.00</div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Card details</label>
                <div id="stripe-card-element"></div>
                <div id="stripe-card-error" class="text-danger small mt-1"></div>
            </div>

            <button class="btn btn-primary w-100 fw-semibold py-2" id="pay-card-btn" onclick="payWithCard()">
                <i class="bi bi-lock-fill me-2"></i>Pay <span id="pm-btn-amount"></span>
            </button>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi bi-shield-check me-1"></i>Secured by Stripe · SSL encrypted
                </small>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const requestId = "{{ request()->segment(3) }}";
let currentUserId = null;
let stripe = null, cardElement = null;
let activePaymentId = null;
let currentFee = 0;

// ─── Bootstrap ────────────────────────────────────────────────────────────────

async function loadAll() {
    const meRes = await api('GET', '/me');
    if (meRes?.ok) {
        const me = await meRes.json();
        currentUserId = (me.user ?? me).id;
    }

    const res = await api('GET', `/requests/${requestId}`);
    if (!res?.ok) {
        document.getElementById('req-loading').innerHTML = '<p class="text-danger">Request not found.</p>';
        return;
    }
    const data = await res.json();
    const req  = data.data ?? data;

    document.getElementById('req-id').textContent        = '#' + req.id;
    document.getElementById('req-status-badge').innerHTML = statusBadge(req.status);
    document.getElementById('req-date').textContent       = 'Submitted ' + fmtDate(req.created_at);

    if (req.status === 'missing_documents') {
        document.getElementById('missing-docs-banner').classList.add('active');
    }

    document.getElementById('info-service').textContent = req.service?.name ?? '—';
    document.getElementById('info-office').textContent   = req.office?.name  ?? '—';
    document.getElementById('info-time').textContent     = req.service?.estimated_time
        ? req.service.estimated_time + ' days est.' : '—';
    document.getElementById('info-notes').textContent    = req.notes ?? 'None';

    const fee = parseFloat(req.service?.fee ?? 0);
    currentFee = fee;
    document.getElementById('info-fee').textContent = fee > 0 ? '$' + fee.toFixed(2) : 'Free';

    if (fee > 0) {
        document.getElementById('payment-card').style.removeProperty('display');
        setupPaymentCard(req, fee);
    }

    renderTimeline(req.status_histories ?? []);
    loadResponseDocs();
    loadMessages();

    if (new URLSearchParams(window.location.search).get('payment') === 'success') {
        showAlert('Payment processed! Status will update shortly.', 'success');
        history.replaceState({}, '', window.location.pathname);
    }

    document.getElementById('req-loading').classList.add('d-none');
    document.getElementById('req-content').classList.remove('d-none');
}

function setupPaymentCard(req, fee) {
    const paid    = (req.payments ?? []).find(p => p.status === 'paid');
    const statusEl = document.getElementById('payment-card-status');
    const paidInfo = document.getElementById('payment-paid-info');
    const actionEl = document.getElementById('payment-action');

    if (paid) {
        statusEl.innerHTML = '<span class="pay-status-pill paid"><i class="bi bi-check-circle-fill"></i>Paid</span>';
        document.getElementById('pay-status-pill').innerHTML =
            '<span class="pay-status-pill paid ms-1"><i class="bi bi-check-circle-fill"></i>Paid</span>';
        paidInfo.classList.remove('d-none');
        actionEl.classList.add('d-none');
        document.getElementById('payment-paid-details').innerHTML =
            `$${parseFloat(paid.amount).toFixed(2)} via ${paid.payment_method} &bull; ${fmtDate(paid.created_at)}` +
            (paid.transaction_id
                ? `<br><span class="font-monospace" style="font-size:.75rem">${paid.transaction_id.slice(0,28)}…</span>`
                : '');
    } else {
        statusEl.innerHTML = '<span class="pay-status-pill none"><i class="bi bi-x-circle-fill"></i>Unpaid</span>';
        document.getElementById('pay-status-pill').innerHTML =
            '<span class="pay-status-pill none ms-1"><i class="bi bi-x-circle-fill"></i>Unpaid</span>';
        document.getElementById('payment-desc').textContent =
            `A fee of $${fee.toFixed(2)} is required to process your request.`;
    }
}

// ─── Payment modal ─────────────────────────────────────────────────────────────

function openPaymentModal() {
    document.getElementById('pm-service-label').textContent =
        document.getElementById('info-service').textContent;
    document.getElementById('pm-amount').textContent     = '$' + currentFee.toFixed(2);
    document.getElementById('pm-btn-amount').textContent = '$' + currentFee.toFixed(2);
    document.getElementById('payment-modal-backdrop').classList.add('open');
    initStripeElement();
}

function closePaymentModal() {
    document.getElementById('payment-modal-backdrop').classList.remove('open');
}

// ─── Stripe ────────────────────────────────────────────────────────────────────

function initStripeElement() {
    if (stripe && cardElement) return;
    prefetchStripeIntent();
}

async function prefetchStripeIntent() {
    const res = await api('POST', '/payments/stripe/intent', { request_id: requestId });
    if (!res?.ok) {
        const err = await res?.json();
        showAlert(err?.message ?? 'Could not initialise payment.', 'danger');
        return;
    }
    const data = await res.json();
    window._stripeClientSecret = data.client_secret;
    activePaymentId             = data.payment_id;
    mountCard(data.publishable_key);
}

function mountCard(publishableKey) {
    stripe = Stripe(publishableKey);
    const elements = stripe.elements();
    cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '15px',
                color: '#1e293b',
                '::placeholder': { color: '#94a3b8' },
            },
            invalid: { color: '#ef4444' },
        },
    });
    cardElement.mount('#stripe-card-element');
    cardElement.on('change', e => {
        document.getElementById('stripe-card-error').textContent = e.error ? e.error.message : '';
    });
}

async function payWithCard() {
    const btn = document.getElementById('pay-card-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

    try {
        if (!window._stripeClientSecret) {
            const res = await api('POST', '/payments/stripe/intent', { request_id: requestId });
            if (!res?.ok) {
                const err = await res?.json();
                throw new Error(err?.message ?? 'Could not create payment.');
            }
            const data = await res.json();
            window._stripeClientSecret = data.client_secret;
            activePaymentId = data.payment_id;
            if (!stripe) mountCard(data.publishable_key);
        }

        const { paymentIntent, error } = await stripe.confirmCardPayment(
            window._stripeClientSecret,
            { payment_method: { card: cardElement } }
        );

        if (error) throw new Error(error.message);

        if (paymentIntent.status === 'succeeded') {
            const confirmRes = await api('POST', '/payments/stripe/confirm', {
                payment_id:         activePaymentId,
                payment_intent_id:  paymentIntent.id,
            });
            if (confirmRes?.ok) {
                closePaymentModal();
                showAlert('🎉 Payment successful! Your request is being processed.', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error('Payment confirmed by Stripe but server update failed. Please contact support.');
            }
        }
    } catch (err) {
        document.getElementById('stripe-card-error').textContent = err.message;
        showAlert(err.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay $' + currentFee.toFixed(2);
    }
}

// ─── Timeline ──────────────────────────────────────────────────────────────────

function renderTimeline(histories) {
    const container = document.getElementById('timeline');
    if (!histories.length) {
        container.innerHTML = '<p class="text-muted small">No status changes yet.</p>';
        return;
    }
    const colors = {
        pending: 'warning', processing: 'info', approved: 'success',
        rejected: 'danger', completed: 'primary', missing_documents: 'warning',
    };
    const labels = {
        pending: 'Pending', processing: 'In Review', approved: 'Approved',
        rejected: 'Rejected', completed: 'Completed', missing_documents: 'Missing Documents',
    };
    container.innerHTML = histories.map((h, i) => {
        const c = colors[h.new_status] ?? 'secondary';
        return `<div class="d-flex gap-3 mb-3">
            <div class="d-flex flex-column align-items-center">
                <div class="rounded-circle bg-${c} text-white d-flex align-items-center justify-content-center"
                    style="width:30px;height:30px;flex-shrink:0;font-size:.75rem">
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </div>
                ${i < histories.length - 1
                    ? '<div style="width:2px;flex:1;background:#e2e8f0;margin:4px 0"></div>'
                    : ''}
            </div>
            <div>
                <div class="fw-semibold small">${labels[h.new_status] ?? h.new_status}</div>
                ${h.comment ? `<div class="text-muted small">${h.comment}</div>` : ''}
                <div class="text-muted" style="font-size:.72rem">${fmtDateTime(h.created_at)}</div>
            </div>
        </div>`;
    }).join('');
}

// ─── Response docs ─────────────────────────────────────────────────────────────

async function loadResponseDocs() {
    const res = await api('GET', `/requests/${requestId}/response-documents`);
    if (!res?.ok) return;
    const docs = (await res.json()).data ?? [];
    if (!docs.length) return;
    document.getElementById('response-docs').innerHTML = docs.map(d => `
        <li class="list-group-item d-flex align-items-center justify-content-between px-3">
            <span class="small">
                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                ${d.title ?? d.file_name ?? 'Document'}
            </span>
            <a href="/storage/${d.file_path}" target="_blank" class="btn btn-sm btn-outline-secondary py-0">
                <i class="bi bi-download"></i>
            </a>
        </li>`).join('');
}

// ─── Extra document upload ──────────────────────────────────────────────────────

async function uploadExtraDoc() {
    const input    = document.getElementById('extra-doc-file');
    const resultEl = document.getElementById('upload-result');
    if (!input.files.length) {
        resultEl.innerHTML = '<span class="text-warning">Please select a file first.</span>';
        return;
    }
    resultEl.innerHTML = '<span class="text-muted">Uploading…</span>';
    let uploaded = 0;
    for (const file of input.files) {
        const form = new FormData();
        form.append('request_id', requestId);
        form.append('file', file);
        const res = await api('POST', '/request-documents', form, true);
        if (res?.ok) uploaded++;
    }
    resultEl.innerHTML = uploaded
        ? `<span class="text-success"><i class="bi bi-check-circle me-1"></i>${uploaded} file(s) uploaded.</span>`
        : `<span class="text-danger">Upload failed.</span>`;
    input.value = '';
}

// ─── Chat ───────────────────────────────────────────────────────────────────────

async function loadMessages() {
    const res = await api('GET', `/requests/${requestId}/messages`);
    if (!res?.ok) return;
    const msgs = (await res.json()).data ?? [];
    const box  = document.getElementById('chat-box');

    if (!msgs.length) {
        box.innerHTML = '<div class="text-center text-muted small py-4">No messages yet. Start the conversation.</div>';
        return;
    }
    box.innerHTML = msgs.map(m => {
        const mine = m.sender_id === currentUserId;
        return `<div class="d-flex ${mine ? 'justify-content-end' : 'justify-content-start'} mb-2">
            <div class="px-3 py-2 rounded-3 small" style="max-width:75%;
                background:${mine ? '#1a56db' : '#fff'};
                color:${mine ? '#fff' : '#1e293b'};
                box-shadow:0 1px 3px rgba(0,0,0,.08)">
                ${!mine ? `<div class="fw-semibold mb-1" style="font-size:.72rem">${m.sender?.name ?? 'Office'}</div>` : ''}
                <div>${m.message}</div>
                <div class="mt-1" style="font-size:.68rem;opacity:.7">${fmtDateTime(m.created_at)}</div>
            </div>
        </div>`;
    }).join('');
    box.scrollTop = box.scrollHeight;
}

async function sendMessage() {
    const input = document.getElementById('msg-input');
    const text  = input.value.trim();
    if (!text) return;
    input.value = '';
    const res = await api('POST', '/messages', { request_id: requestId, message: text });
    if (res?.ok) loadMessages();
    else showAlert('Failed to send message.', 'danger');
}

setInterval(loadMessages, 15000);
loadAll();
</script>
@endpush