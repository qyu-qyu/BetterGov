@extends('citizen.layout')

@section('title', 'Feedback')
@section('page-title', 'Feedback & Reviews')

@section('content')
<div class="row g-4">
    <!-- Submit Feedback -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-star-fill me-2 text-warning"></i>Leave a Review
            </div>
            <div class="card-body">
                <form id="feedback-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select a Completed Request</label>
                        <select id="fb-request" class="form-select" required onchange="onRequestChange()">
                            <option value="">Choose a request…</option>
                        </select>
                        <div class="form-text" id="request-preview"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rating</label>
                        <div id="star-rating" class="d-flex gap-2 mb-1" style="font-size:2rem">
                            @for($i = 1; $i <= 5; $i++)
                            <span class="star" data-val="{{ $i }}" onclick="setRating({{ $i }})"
                                style="cursor:pointer;color:#d1d5db;transition:color .15s">&#9733;</span>
                            @endfor
                        </div>
                        <input type="hidden" id="fb-rating" value="">
                        <div class="form-text" id="rating-label">Click a star to rate</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Comment <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea id="fb-comment" class="form-control" rows="4"
                            placeholder="Share your experience with this service…"></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-semibold" id="fb-btn">
                        <i class="bi bi-send me-2"></i>Submit Feedback
                    </button>
                </form>

                <div id="fb-success" class="d-none alert alert-success mt-3">
                    <i class="bi bi-check-circle me-2"></i>Thank you for your feedback!
                </div>
            </div>
        </div>
    </div>

    <!-- My past feedback -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-chat-square-quote me-2 text-primary"></i>My Feedback History
            </div>
            <div id="my-feedback-list" class="card-body">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let ratingVal = 0;
const ratingLabels = ['','Poor','Fair','Good','Very Good','Excellent'];

function setRating(val) {
    ratingVal = val;
    document.getElementById('fb-rating').value = val;
    document.getElementById('rating-label').textContent = ratingLabels[val] ?? '';
    document.querySelectorAll('.star').forEach(s => {
        s.style.color = parseInt(s.dataset.val) <= val ? '#f59e0b' : '#d1d5db';
    });
}

async function loadCompletedRequests() {
    const res = await api('GET', '/requests');
    if (!res || !res.ok) return;
    const data = await res.json();
    const requests = (data.data ?? data).filter(r => ['approved','completed'].includes(r.status));
    const sel = document.getElementById('fb-request');
    requests.forEach(r => sel.insertAdjacentHTML('beforeend',
        `<option value="${r.id}">${r.service_name ?? 'Request #' + r.id} — ${r.office_name ?? ''}</option>`));
}

function onRequestChange() {
    const sel  = document.getElementById('fb-request');
    const text = sel.selectedOptions[0]?.text ?? '';
    document.getElementById('request-preview').textContent = text ? `Selected: ${text}` : '';
}

document.getElementById('feedback-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!ratingVal) { showAlert('Please select a rating.', 'warning'); return; }
    const requestId = document.getElementById('fb-request').value;
    if (!requestId) { showAlert('Please select a request.', 'warning'); return; }

    const btn = document.getElementById('fb-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';

    const res = await api('POST', '/feedback', {
        request_id: requestId,
        rating:     ratingVal,
        comment:    document.getElementById('fb-comment').value || null,
    });

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-send me-2"></i>Submit Feedback';

    if (res && res.ok) {
        document.getElementById('feedback-form').classList.add('d-none');
        document.getElementById('fb-success').classList.remove('d-none');
        loadMyFeedback();
    } else {
        const err = await res?.json();
        showAlert(err?.message ?? 'Failed to submit feedback.', 'danger');
    }
});

async function loadMyFeedback() {
    const res = await api('GET', '/feedback');
    if (!res || !res.ok) return;
    const data = await res.json();
    const items = data.data ?? data;
    const container = document.getElementById('my-feedback-list');

    if (!items.length) {
        container.innerHTML = '<p class="text-muted text-center">No feedback submitted yet.</p>';
        return;
    }

    container.innerHTML = items.map(f => {
        const stars = '★'.repeat(f.rating) + '☆'.repeat(5 - f.rating);
        return `<div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-warning fs-5">${stars}</span>
                <small class="text-muted">${fmtDate(f.created_at)}</small>
            </div>
            <p class="mb-1 small">${f.comment ?? '<em class="text-muted">No comment</em>'}</p>
            <div class="small text-muted">Request #${f.request_id ?? '—'}</div>
            ${f.responses?.length ? f.responses.map(r => `
            <div class="mt-2 p-2 bg-light rounded small border-start border-primary border-3">
                <strong>Office response:</strong> ${r.response}
            </div>`).join('') : ''}
        </div>`;
    }).join('');
}

loadCompletedRequests();
loadMyFeedback();
</script>
@endpush
