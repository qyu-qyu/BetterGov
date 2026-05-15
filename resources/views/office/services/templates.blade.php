@extends('office.layout')
@section('page-title', 'Service Templates')

@section('topbar_actions')
<a href="{{ route('office.services.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Back to Services
</a>
@endsection

@section('content')
<div class="mb-4">
    <p class="text-muted mb-3">
        Pre-built service templates matched to your office type. Click <strong>Add to My Services</strong>
        to adopt one — you can then customize only the fee and description before saving.
    </p>
    <div class="input-group" style="max-width:300px">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="tmpl-search" class="form-control border-start-0"
            placeholder="Search templates…" oninput="filterTemplates()">
    </div>
</div>

<div id="tmpl-loading" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
</div>

<div id="tmpl-content" class="d-none"></div>
@endsection

@push('scripts')
<style>
    .template-card { transition: transform .15s, box-shadow .15s; border: 1px solid #e2e8f0; }
    .template-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.1); }
    .doc-list li { line-height: 1.5; }
</style>
<script>
let allTemplates = [];

const CAT_ICON = {
    'Civil Registry':   'bi-person-vcard-fill',
    'Mukhtar Services': 'bi-patch-check-fill',
    'Municipal Permits':'bi-building-fill-gear',
    'Public Health':    'bi-heart-pulse-fill',
    'General Security': 'bi-shield-lock-fill',
};
const CAT_COLOR = {
    'Civil Registry':   'primary',
    'Mukhtar Services': 'success',
    'Municipal Permits':'warning',
    'Public Health':    'danger',
    'General Security': 'info',
};

async function loadTemplates() {
    const res = await api('GET', '/service-templates');
    if (!res || !res.ok) {
        document.getElementById('tmpl-loading').innerHTML =
            '<p class="text-danger">Failed to load templates.</p>';
        return;
    }
    const data = await res.json();

    // Office type not configured on this office
    if (data.warning) {
        document.getElementById('tmpl-loading').innerHTML = `
            <div class="text-center py-5">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center
                            justify-content-center mb-3" style="width:64px;height:64px">
                    <i class="bi bi-gear-wide-connected fs-3 text-warning"></i>
                </div>
                <h6 class="fw-semibold">Office type not configured</h6>
                <p class="text-muted small mb-0">${data.warning}</p>
            </div>`;
        return;
    }

    allTemplates = Array.isArray(data) ? data : (data.data ?? []);

    renderTemplates(allTemplates);
    document.getElementById('tmpl-loading').classList.add('d-none');
    document.getElementById('tmpl-content').classList.remove('d-none');
}

function filterTemplates() {
    const q = document.getElementById('tmpl-search').value.toLowerCase();
    renderTemplates(allTemplates.filter(t =>
        !q ||
        t.name_en.toLowerCase().includes(q) ||
        (t.name_ar ?? '').includes(q) ||
        (t.description ?? '').toLowerCase().includes(q)
    ));
}

function renderTemplates(templates) {
    const container = document.getElementById('tmpl-content');
    if (!templates.length) {
        container.innerHTML = `<div class="text-center text-muted py-5">
            <i class="bi bi-search display-6 d-block mb-2"></i>No templates found.</div>`;
        return;
    }

    const grouped = templates.reduce((acc, t) => {
        (acc[t.category] = acc[t.category] ?? []).push(t); return acc;
    }, {});

    container.innerHTML = Object.entries(grouped).map(([cat, tmpls]) => {
        const icon  = CAT_ICON[cat]  ?? 'bi-grid-fill';
        const color = CAT_COLOR[cat] ?? 'secondary';
        return `
        <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <i class="bi ${icon} text-${color} fs-5"></i>
                <h5 class="mb-0 fw-bold">${cat}</h5>
                <span class="badge bg-${color} bg-opacity-10 text-${color}">
                    ${tmpls.length} template${tmpls.length !== 1 ? 's' : ''}
                </span>
            </div>
            <div class="row g-3">
                ${tmpls.map(t => templateCard(t, color)).join('')}
            </div>
        </div>`;
    }).join('');
}

function templateCard(t, color) {
    const days  = t.estimated_days;
    const time  = days === 1 ? '1 business day' : `${days} business days`;
    const docs  = t.required_documents ?? [];
    const shown = docs.slice(0, 3);
    const extra = docs.length - shown.length;

    return `
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 template-card">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-${color} bg-opacity-10 text-${color} small">${t.category}</span>
                    <span class="badge bg-light text-muted border small">
                        <i class="bi bi-clock me-1"></i>${time}
                    </span>
                </div>

                <h6 class="fw-bold mb-1">${t.name_en}</h6>
                ${t.name_ar
                    ? `<div class="text-muted small mb-2 fw-semibold" dir="rtl" style="font-family:serif">${t.name_ar}</div>`
                    : ''}
                ${t.description
                    ? `<p class="text-muted small mb-2">${t.description}</p>`
                    : ''}

                <div class="mt-auto">
                    ${docs.length ? `
                    <div class="mb-3">
                        <div class="text-muted small fw-semibold mb-1">
                            <i class="bi bi-file-earmark-text me-1"></i>Required Documents
                            <span class="fw-normal">(${docs.length})</span>
                        </div>
                        <ul class="list-unstyled mb-0 doc-list">
                            ${shown.map(d =>
                                `<li class="small text-muted"><i class="bi bi-dot text-${color}"></i>${d}</li>`
                            ).join('')}
                            ${extra > 0
                                ? `<li class="small text-muted fst-italic"><i class="bi bi-dot"></i>+${extra} more…</li>`
                                : ''}
                        </ul>
                    </div>` : ''}

                    <a href="/office/services?template=${t.id}"
                       class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i>Add to My Services
                    </a>
                </div>
            </div>
        </div>
    </div>`;
}

loadTemplates();
</script>
@endpush
