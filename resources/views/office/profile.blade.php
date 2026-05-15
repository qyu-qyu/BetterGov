@extends('office.layout')
@section('page-title', 'Office Profile')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
<div class="row g-3">
    <!-- Profile form -->
    <div class="col-12 col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-building-gear me-2 text-primary"></i>Office Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Office Name</label>
                    <input type="text" id="f-name" class="form-control" placeholder="Office name">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <input type="text" id="f-address" class="form-control" placeholder="Street address">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" id="f-phone" class="form-control" placeholder="+1 555 000 0000">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" id="f-email" class="form-control" placeholder="office@gov.lb">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Working Hours</label>
                    <input type="text" id="f-hours" class="form-control" placeholder="Mon–Fri 8am–4pm">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Latitude</label>
                        <input type="number" id="f-lat" class="form-control" step="any" placeholder="33.8938">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Longitude</label>
                        <input type="number" id="f-lng" class="form-control" step="any" placeholder="35.5018">
                    </div>
                </div>
                <button class="btn btn-primary" onclick="saveProfile()">
                    <i class="bi bi-check2 me-1"></i>Save Changes
                </button>
            </div>
        </div>

        <!-- Stats overview -->
        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>Quick Stats</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="fs-3 fw-bold text-primary" id="stat-services">—</div>
                            <div class="small text-muted">Services</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="fs-3 fw-bold text-info" id="stat-slots">—</div>
                            <div class="small text-muted">Time Slots</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-geo-alt me-2 text-danger"></i>Location on Map
                <span class="small text-muted fw-normal ms-2">(Click to set pin)</span>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height:420px;border-radius:0 0 10px 10px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map = null, marker = null;

async function loadProfile() {
    const res = await api('GET', '/office-portal/profile');
    if (!res || !res.ok) { showAlert('Failed to load profile.', 'danger'); return; }
    const { data: o } = await res.json();

    document.getElementById('f-name').value    = o.name ?? '';
    document.getElementById('f-address').value = o.address ?? '';
    document.getElementById('f-phone').value   = o.phone ?? '';
    document.getElementById('f-email').value   = o.email ?? '';
    document.getElementById('f-hours').value   = o.working_hours ?? '';
    document.getElementById('f-lat').value     = o.latitude ?? '';
    document.getElementById('f-lng').value     = o.longitude ?? '';
    document.getElementById('stat-services').textContent = o.services?.length ?? '—';

    // Update sidebar brand name
    const brand = document.getElementById('office-name-brand');
    if (brand) brand.textContent = o.name ?? 'Office Portal';

    initMap(parseFloat(o.latitude) || 33.8938, parseFloat(o.longitude) || 35.5018);

    // Load slot count
    const sRes = await api('GET', '/appointment-slots');
    if (sRes && sRes.ok) {
        const { data } = await sRes.json();
        document.getElementById('stat-slots').textContent = data?.length ?? '—';
    }
}

function initMap(lat, lng) {
    if (map) { map.remove(); map = null; }
    map = L.map('map').setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    marker.on('dragend', e => {
        const pos = e.target.getLatLng();
        document.getElementById('f-lat').value = pos.lat.toFixed(6);
        document.getElementById('f-lng').value = pos.lng.toFixed(6);
    });

    map.on('click', e => {
        marker.setLatLng(e.latlng);
        document.getElementById('f-lat').value = e.latlng.lat.toFixed(6);
        document.getElementById('f-lng').value = e.latlng.lng.toFixed(6);
    });
}

async function saveProfile() {
    const payload = {
        name:          document.getElementById('f-name').value.trim(),
        address:       document.getElementById('f-address').value.trim() || null,
        phone:         document.getElementById('f-phone').value.trim()   || null,
        email:         document.getElementById('f-email').value.trim()   || null,
        working_hours: document.getElementById('f-hours').value.trim()   || null,
        latitude:      document.getElementById('f-lat').value  || null,
        longitude:     document.getElementById('f-lng').value  || null,
    };
    const res  = await api('PUT', '/office-portal/profile', payload);
    const json = await res.json();
    if (!res.ok) { showAlert(json.message ?? 'Failed to save.', 'danger'); return; }
    showAlert('Profile updated.');
    // Update map if coordinates changed
    const lat = parseFloat(payload.latitude);
    const lng = parseFloat(payload.longitude);
    if (!isNaN(lat) && !isNaN(lng) && marker) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 14);
    }
}

loadProfile();
</script>
@endpush
