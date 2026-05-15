@extends('citizen.layout')

@section('title', 'Browse Services')
@section('page-title', 'Browse Services')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="search-input" class="form-control border-start-0" placeholder="Search services…">
        </div>
    </div>
    <div class="col-md-3">
        <select id="category-filter" class="form-select">
            <option value="">All Categories</option>
        </select>
    </div>
    <div class="col-md-3">
        <select id="office-filter" class="form-select">
            <option value="">All Offices</option>
        </select>
    </div>
    <div class="col-md-1">
        <button class="btn btn-outline-secondary w-100" onclick="resetFilters()" title="Reset">
            <i class="bi bi-x-circle"></i>
        </button>
    </div>
</div>

<!-- Category pills -->
<div id="category-pills" class="d-flex flex-wrap gap-2 mb-4"></div>

<!-- Services grid -->
<div id="services-grid" class="row g-3">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary"></div>
    </div>
</div>

<!-- Empty state -->
<div id="empty-state" class="text-center py-5 d-none">
    <i class="bi bi-inbox display-4 text-muted"></i>
    <p class="mt-3 text-muted">No services found matching your filters.</p>
    <button class="btn btn-outline-primary" onclick="resetFilters()">Clear Filters</button>
</div>
@endsection

@push('scripts')
<script>
let allServices = [], allCategories = [], allOffices = [];
let activeCategoryId = null;

async function loadData() {
    const [svcRes, catRes, offRes] = await Promise.all([
        api('GET', '/services'),
        api('GET', '/service-categories'),
        api('GET', '/offices'),
    ]);

    const svcData = await svcRes.json();
    allServices   = Array.isArray(svcData) ? svcData : (svcData.data ?? []);
    const catData = await catRes.json();
    const offData = await offRes.json();

    allCategories = Array.isArray(catData) ? catData : (catData.data ?? []);
    allOffices    = Array.isArray(offData) ? offData : (offData.data ?? []);

    buildCategoryPills();
    buildFilterSelects();
    renderServices(allServices);
}

function buildCategoryPills() {
    const container = document.getElementById('category-pills');
    container.innerHTML = `<button class="btn btn-sm btn-primary" onclick="filterCategory(null)">All</button>`;
    allCategories.forEach(c => {
        container.insertAdjacentHTML('beforeend',
            `<button class="btn btn-sm btn-outline-primary" id="pill-${c.id}" onclick="filterCategory(${c.id})">${c.name}</button>`);
    });
}

function buildFilterSelects() {
    const catSel = document.getElementById('category-filter');
    const offSel = document.getElementById('office-filter');
    allCategories.forEach(c => catSel.insertAdjacentHTML('beforeend', `<option value="${c.id}">${c.name}</option>`));
    allOffices.forEach(o => offSel.insertAdjacentHTML('beforeend', `<option value="${o.id}">${o.name}</option>`));
}

function filterCategory(id) {
    activeCategoryId = id;
    document.querySelectorAll('#category-pills .btn').forEach(b => {
        b.className = 'btn btn-sm btn-outline-primary';
    });
    const active = id ? document.getElementById('pill-' + id) : document.querySelector('#category-pills .btn');
    if (active) active.className = 'btn btn-sm btn-primary';
    document.getElementById('category-filter').value = id ?? '';
    applyFilters();
}

function applyFilters() {
    const search = document.getElementById('search-input').value.toLowerCase();
    const catId  = document.getElementById('category-filter').value;
    const offId  = document.getElementById('office-filter').value;

    const filtered = allServices.filter(s => {
        const matchSearch = !search || s.name.toLowerCase().includes(search);
        const matchCat    = !catId  || String(s.service_category_id) === catId;
        const matchOff    = !offId  || String(s.office_id) === offId;
        return matchSearch && matchCat && matchOff;
    });

    renderServices(filtered);
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('category-filter').value = '';
    document.getElementById('office-filter').value = '';
    activeCategoryId = null;
    buildCategoryPills();
    renderServices(allServices);
}

function renderServices(services) {
    const grid  = document.getElementById('services-grid');
    const empty = document.getElementById('empty-state');

    if (!services.length) {
        grid.innerHTML = '';
        empty.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');

    const categoryMap = Object.fromEntries(allCategories.map(c => [c.id, c.name]));
    const officeMap   = Object.fromEntries(allOffices.map(o => [o.id, o.name]));

    grid.innerHTML = services.map(s => {
        const cat = categoryMap[s.service_category_id] ?? '—';
        const off = officeMap[s.office_id] ?? '—';
        const docs = s.document_types ?? [];
        return `
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100 service-card" onclick="window.location='/citizen/services/${s.id}'" style="cursor:pointer">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary">${cat}</span>
                        <span class="fw-bold text-success">$${parseFloat(s.fee ?? 0).toFixed(2)}</span>
                    </div>
                    <h6 class="card-title fw-semibold mb-1">${s.name}</h6>
                    <p class="text-muted small mb-2"><i class="bi bi-building me-1"></i>${off}</p>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-clock me-1"></i>${s.estimated_time ?? '—'} min estimated
                    </p>
                    ${docs.length ? `<div class="small text-muted"><i class="bi bi-paperclip me-1"></i>${docs.length} document(s) required</div>` : ''}
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="/citizen/services/${s.id}" class="btn btn-sm btn-primary w-100">
                        Apply Now <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>`;
    }).join('');
}

document.getElementById('search-input').addEventListener('input', applyFilters);
document.getElementById('category-filter').addEventListener('change', applyFilters);
document.getElementById('office-filter').addEventListener('change', applyFilters);

loadData();
</script>
@endpush
