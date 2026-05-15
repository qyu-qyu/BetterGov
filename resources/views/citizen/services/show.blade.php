@extends('citizen.layout')

@section('title', 'Service Details')
@section('page-title', 'Service Details')

@section('content')
<div id="service-loading" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
</div>

<div id="service-content" class="d-none">
    <div class="row g-4">
        <!-- Service Info -->
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span id="svc-category" class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2"></span>
                        <span id="svc-fee" class="fs-4 fw-bold text-success"></span>
                    </div>
                    <h4 id="svc-name" class="fw-bold mb-2"></h4>
                    <div class="text-muted mb-1"><i class="bi bi-building me-2"></i><span id="svc-office"></span></div>
                    <div class="text-muted mb-3"><i class="bi bi-clock me-2"></i><span id="svc-time"></span></div>

                    <h6 class="fw-semibold mt-3 mb-2">Required Documents</h6>
                    <ul id="svc-docs" class="list-group list-group-flush"></ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Office Contact</h6>
                    <div class="text-muted small" id="office-phone"></div>
                    <div class="text-muted small" id="office-email"></div>
                    <div class="text-muted small" id="office-address"></div>
                    <a id="office-map-link" href="#" class="btn btn-sm btn-outline-secondary mt-3 d-none" target="_blank">
                        <i class="bi bi-geo-alt me-1"></i>View on Map
                    </a>
                </div>
            </div>
        </div>

        <!-- Submit Request -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header py-3">
                    <i class="bi bi-send-fill me-2 text-primary"></i>Submit a Request
                </div>
                <div class="card-body">
                    <form id="request-form" enctype="multipart/form-data">
                        <input type="hidden" id="service-id">
                        <input type="hidden" id="office-id">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea id="notes" class="form-control" rows="4"
                                placeholder="Describe your request or add any relevant details…"></textarea>
                        </div>

                        <div id="doc-upload-section">
                            <label class="form-label fw-semibold">Upload Documents</label>
                            <p class="text-muted small">Please upload all required documents listed on the left.</p>
                            <div id="doc-upload-fields"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addGenericUpload()">
                                <i class="bi bi-plus-circle me-1"></i>Add another file
                            </button>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary px-4" id="submit-btn">
                            <i class="bi bi-send me-2"></i>Submit Request
                        </button>
                    </form>

                    <div id="success-box" class="d-none mt-3">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Request submitted successfully!
                            <a id="view-request-link" href="#" class="alert-link ms-2">View your request</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const serviceId = {{ request()->segment(3) }};
let requiredDocs = [];
let officeData = null;

async function loadService() {
    const [svcRes, detailRes] = await Promise.all([
        api('GET', `/services/${serviceId}`),
        api('GET', `/services/${serviceId}/required-documents`),
    ]);

    if (!svcRes || !svcRes.ok) {
        document.getElementById('service-loading').innerHTML = '<p class="text-danger">Service not found.</p>';
        return;
    }

    const svcWrapper = await svcRes.json();
    const svc        = svcWrapper.data ?? svcWrapper;
    const docData    = await detailRes.json();

    requiredDocs = docData.data?.required_documents ?? svc.document_types ?? [];

    document.getElementById('svc-name').textContent     = svc.name;
    document.getElementById('svc-category').textContent = svc.category?.name ?? '—';
    document.getElementById('svc-fee').textContent      = '$' + parseFloat(svc.fee ?? 0).toFixed(2);
    document.getElementById('svc-time').textContent     = svc.estimated_time ?? '—';
    document.getElementById('service-id').value         = svc.id;
    document.getElementById('office-id').value          = svc.office_id;

    // Office info
    if (svc.office) {
        officeData = svc.office;
        document.getElementById('svc-office').textContent  = svc.office.name ?? '—';
        document.getElementById('office-phone').innerHTML   = `<i class="bi bi-telephone me-1"></i>${svc.office.phone ?? '—'}`;
        document.getElementById('office-email').innerHTML   = `<i class="bi bi-envelope me-1"></i>${svc.office.email ?? '—'}`;
        document.getElementById('office-address').innerHTML = `<i class="bi bi-geo-alt me-1"></i>${svc.office.address ?? '—'}`;
        if (svc.office.latitude && svc.office.longitude) {
            const link = document.getElementById('office-map-link');
            link.href = `https://www.google.com/maps?q=${svc.office.latitude},${svc.office.longitude}`;
            link.classList.remove('d-none');
        }
    } else {
        // Fetch office separately if needed
        const offRes = await api('GET', `/offices/${svc.office_id}`);
        if (offRes && offRes.ok) {
            officeData = await offRes.json();
            document.getElementById('svc-office').textContent  = officeData.name ?? '—';
            document.getElementById('office-phone').innerHTML   = `<i class="bi bi-telephone me-1"></i>${officeData.phone ?? '—'}`;
            document.getElementById('office-email').innerHTML   = `<i class="bi bi-envelope me-1"></i>${officeData.email ?? '—'}`;
            document.getElementById('office-address').innerHTML = `<i class="bi bi-geo-alt me-1"></i>${officeData.address ?? '—'}`;
        }
    }

    // Required docs list
    const docList = document.getElementById('svc-docs');
    if (requiredDocs.length) {
        docList.innerHTML = requiredDocs.map(d =>
            `<li class="list-group-item d-flex align-items-center gap-2 px-0">
                <i class="bi bi-file-earmark-check text-primary"></i>${d.name ?? d}
            </li>`
        ).join('');
        buildUploadFields();
    } else {
        docList.innerHTML = '<li class="list-group-item px-0 text-muted">No specific documents required</li>';
        document.getElementById('doc-upload-section').innerHTML =
            '<p class="text-muted small">No documents required for this service.</p>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="addGenericUpload()"><i class="bi bi-plus-circle me-1"></i>Add supporting file</button>';
    }

    document.getElementById('service-loading').classList.add('d-none');
    document.getElementById('service-content').classList.remove('d-none');
}

function buildUploadFields() {
    const container = document.getElementById('doc-upload-fields');
    container.innerHTML = requiredDocs.map((d, i) => `
        <div class="mb-3">
            <label class="form-label small fw-semibold">
                ${d.name ?? 'Document ' + (i+1)}
                <span class="text-danger">*</span>
                <span class="text-muted fw-normal">(required)</span>
            </label>
            <input type="file" class="form-control form-control-sm doc-file"
                data-doc-type-id="${d.id ?? ''}"
                accept=".pdf,.jpg,.jpeg,.png"
                required>
        </div>`
    ).join('');
}

function addGenericUpload() {
    document.getElementById('doc-upload-fields').insertAdjacentHTML('beforeend', `
        <div class="mb-2">
            <input type="file" class="form-control form-control-sm doc-file" data-doc-type-id="" accept=".pdf,.jpg,.jpeg,.png">
        </div>`);
}

document.getElementById('request-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';

    // 1. Create the request
    const reqRes = await api('POST', '/requests', {
        service_id: document.getElementById('service-id').value,
        office_id:  document.getElementById('office-id').value,
        notes:      document.getElementById('notes').value || null,
    });

    if (!reqRes || !reqRes.ok) {
        const err = await reqRes?.json();
        showAlert(err?.message ?? 'Failed to submit request.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Submit Request';
        return;
    }

    const reqData = await reqRes.json();
    const requestId = reqData.data?.id ?? reqData.id;

    // 2. Upload documents
    const fileInputs = document.querySelectorAll('.doc-file');
    for (const input of fileInputs) {
        if (!input.files[0]) continue;
        const form = new FormData();
        form.append('request_id', requestId);
        form.append('file', input.files[0]);
        if (input.dataset.docTypeId) form.append('document_type_id', input.dataset.docTypeId);
        await api('POST', '/request-documents', form, true);
    }

    document.getElementById('success-box').classList.remove('d-none');
    document.getElementById('view-request-link').href = `/citizen/requests/${requestId}`;
    document.getElementById('request-form').classList.add('d-none');
});

loadService();
</script>
@endpush
