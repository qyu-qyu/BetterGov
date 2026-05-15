@extends('citizen.layout')

@section('title', 'Find Offices')
@section('page-title', 'Find Offices')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #map { height: 450px; width: 100%; border-radius: 10px; }
    .office-card { cursor: pointer; transition: transform .15s, box-shadow .15s; }
    .office-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.12); }
    .office-card.selected { border: 2px solid #1a56db; }
    .leaflet-popup-content b { color: #1e293b; }
</style>
@endpush

@section('content')
<!-- Filters -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="office-search" class="form-control border-start-0" placeholder="Search offices…">
        </div>
    </div>
    <div class="col-md-3">
        <select id="type-filter" class="form-select">
            <option value="">All Types</option>
        </select>
    </div>
    <div class="col-md-3">
        <select id="service-filter" class="form-select">
            <option value="">Filter by Service</option>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-outline-primary w-100" onclick="findNearest()">
            <i class="bi bi-crosshair me-1"></i>Nearest
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Map -->
    <div class="col-lg-7">
        <div class="card mb-0">
            <div class="card-body p-2">
                <div id="map"></div>
            </div>
        </div>
        <div id="nearest-banner" class="d-none alert alert-info mt-2 mb-0 small">
            <i class="bi bi-geo-alt me-1"></i>Showing offices sorted by distance from your location.
            <button class="btn btn-sm btn-outline-info ms-2" onclick="clearNearest()">Clear</button>
        </div>
    </div>

    <!-- Office list -->
    <div class="col-lg-5">
        <div id="offices-list" style="max-height:500px;overflow-y:auto">
            <div class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm"></div></div>
        </div>
    </div>
</div>

<!-- Office detail modal -->
<div class="modal fade" id="officeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-office-name"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-2"></i><span id="modal-address"></span></p>
                        <p class="text-muted small mb-1"><i class="bi bi-telephone me-2"></i><span id="modal-phone"></span></p>
                        <p class="text-muted small mb-1"><i class="bi bi-envelope me-2"></i><span id="modal-email"></span></p>
                        <p class="text-muted small mb-3"><i class="bi bi-clock me-2"></i><span id="modal-hours"></span></p>
                        <a id="modal-map-link" href="#" target="_blank" class="btn btn-sm btn-outline-secondary d-none">
                            <i class="bi bi-map me-1"></i>Open in Maps
                        </a>
                    </div>
                    <div class="col-md-7">
                        <h6 class="fw-semibold mb-2">Available Services</h6>
                        <ul id="modal-services" class="list-group list-group-flush"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="book-appointment-btn" href="#" class="btn btn-primary">
                    <i class="bi bi-calendar-plus me-1"></i>Book Appointment
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let allOffices = [], allServices = [], map, markers = [], userMarker = null;

async function loadData() {
    const [offRes, svcRes] = await Promise.all([
        fetch('/api/offices', { headers: { Accept: 'application/json' } }),
        fetch('/api/services', { headers: { Accept: 'application/json' } }),
    ]);
    const offData = await offRes.json();
    allOffices  = Array.isArray(offData) ? offData : (offData.data ?? []);
    const svcData = await svcRes.json();
    allServices = Array.isArray(svcData) ? svcData : (svcData.data ?? []);

    // Build type filter
    const types = [...new Map(allOffices.filter(o => o.office_type).map(o => [o.office_type?.id, o.office_type])).values()];
    const typeSel = document.getElementById('type-filter');
    types.forEach(t => typeSel.insertAdjacentHTML('beforeend', `<option value="${t.id}">${t.name}</option>`));

    // Build service filter
    const svcSel = document.getElementById('service-filter');
    allServices.forEach(s => svcSel.insertAdjacentHTML('beforeend', `<option value="${s.office_id}">${s.name}</option>`));

    renderOffices(allOffices);
    initMap();
}

function initMap() {
    map = L.map('map').setView([33.8547, 35.8623], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);
    placeMarkers(allOffices);
}

function placeMarkers(offices) {
    markers.forEach(m => m.remove());
    markers = [];
    offices.forEach(o => {
        if (!o.latitude || !o.longitude) return;
        const marker = L.marker([parseFloat(o.latitude), parseFloat(o.longitude)])
            .addTo(map)
            .bindPopup(`<b>${o.name}</b><br><span style="font-size:12px;color:#64748b">${o.address ?? ''}</span>`);
        marker.on('click', () => openOfficeModal(o));
        markers.push(marker);
    });
}

function renderOffices(offices) {
    const list = document.getElementById('offices-list');
    if (!offices.length) {
        list.innerHTML = `<div class="text-center text-muted py-4"><i class="bi bi-building-x display-6 d-block mb-2"></i>No offices found.</div>`;
        return;
    }
    list.innerHTML = offices.map(o => `
        <div class="card office-card mb-2" onclick="selectOffice(${o.id}, this)">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">${o.name}</div>
                        <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>${o.address ?? '—'}</div>
                        <div class="text-muted small"><i class="bi bi-telephone me-1"></i>${o.phone ?? '—'}</div>
                    </div>
                    <div class="text-end">
                        ${o.office_type ? `<span class="badge bg-light text-dark border">${o.office_type.name}</span>` : ''}
                        ${o.distance != null ? `<div class="text-muted small mt-1">${o.distance.toFixed(1)} km</div>` : ''}
                    </div>
                </div>
            </div>
        </div>`).join('');
}

function selectOffice(id, el) {
    const office = allOffices.find(o => o.id === id);
    if (!office) return;
    document.querySelectorAll('.office-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    if (map && office.latitude && office.longitude) {
        map.setView([parseFloat(office.latitude), parseFloat(office.longitude)], 15);
    }
    openOfficeModal(office);
}

async function openOfficeModal(office) {
    document.getElementById('modal-office-name').textContent = office.name;
    document.getElementById('modal-address').textContent     = office.address ?? '—';
    document.getElementById('modal-phone').textContent       = office.phone ?? '—';
    document.getElementById('modal-email').textContent       = office.email ?? '—';
    document.getElementById('modal-hours').textContent       = office.working_hours ?? '—';

    const mapLink = document.getElementById('modal-map-link');
    if (office.latitude && office.longitude) {
        mapLink.href = `https://www.openstreetmap.org/?mlat=${office.latitude}&mlon=${office.longitude}#map=16/${office.latitude}/${office.longitude}`;
        mapLink.classList.remove('d-none');
    } else {
        mapLink.classList.add('d-none');
    }

    document.getElementById('book-appointment-btn').href = `/citizen/appointments?office_id=${office.id}`;

    const officeSvcs = allServices.filter(s => s.office_id === office.id);
    const svcList = document.getElementById('modal-services');
    svcList.innerHTML = officeSvcs.length
        ? officeSvcs.map(s => `<li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span class="small">${s.name}</span>
            <a href="/citizen/services/${s.id}" class="btn btn-sm btn-outline-primary py-0">Apply</a>
          </li>`).join('')
        : '<li class="list-group-item px-0 text-muted small">No services listed</li>';

    new bootstrap.Modal(document.getElementById('officeModal')).show();
}

function applyFilters() {
    const search          = document.getElementById('office-search').value.toLowerCase();
    const typeId          = document.getElementById('type-filter').value;
    const officeIdFromSvc = document.getElementById('service-filter').value;

    const filtered = allOffices.filter(o => {
        const matchSearch  = !search  || o.name.toLowerCase().includes(search) || (o.address ?? '').toLowerCase().includes(search);
        const matchType    = !typeId  || String(o.office_type_id) === typeId;
        const matchService = !officeIdFromSvc || String(o.id) === officeIdFromSvc;
        return matchSearch && matchType && matchService;
    });

    renderOffices(filtered);
    if (map) placeMarkers(filtered);
}

function findNearest() {
    if (!navigator.geolocation) { showAlert('Geolocation not supported.', 'warning'); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        const { latitude, longitude } = pos.coords;

        if (userMarker) userMarker.remove();
        if (map) {
            userMarker = L.marker([latitude, longitude], {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="width:14px;height:14px;background:#ef4444;border:3px solid #fff;border-radius:50%;box-shadow:0 0 6px rgba(0,0,0,.4)"></div>',
                    iconSize: [14, 14],
                    iconAnchor: [7, 7],
                }),
            }).addTo(map).bindPopup('You are here').openPopup();
            map.setView([latitude, longitude], 12);
        }

        const sorted = [...allOffices].map(o => ({
            ...o,
            distance: (o.latitude && o.longitude)
                ? haversine(latitude, longitude, parseFloat(o.latitude), parseFloat(o.longitude))
                : Infinity,
        })).sort((a, b) => a.distance - b.distance);

        renderOffices(sorted);
        if (map) placeMarkers(sorted);
        document.getElementById('nearest-banner').classList.remove('d-none');
    }, () => showAlert('Could not determine your location.', 'danger'));
}

function clearNearest() {
    if (userMarker) { userMarker.remove(); userMarker = null; }
    document.getElementById('nearest-banner').classList.add('d-none');
    renderOffices(allOffices);
    if (map) placeMarkers(allOffices);
}

function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371, dLat = rad(lat2 - lat1), dLon = rad(lon2 - lon1);
    const a = Math.sin(dLat/2)**2 + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}
function rad(d) { return d * Math.PI / 180; }

document.getElementById('office-search').addEventListener('input', applyFilters);
document.getElementById('type-filter').addEventListener('change', applyFilters);
document.getElementById('service-filter').addEventListener('change', applyFilters);

loadData();
</script>
@endpush
