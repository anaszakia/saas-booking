@extends('layouts.app')

@section('title', 'Tambah Venue Baru')

@section('content')

    <div class="mb-6">
        <h4 class="mb-0">Tambah Venue / Cabang Baru</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('venues.index') }}">Venues</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-building me-2 text-primary"></i>Informasi Tempat
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('venues.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="name">Nama Venue / Cabang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Aero Padel Cilandak" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="phone">Nomor Telepon / WhatsApp</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone') }}" placeholder="081234567890">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="city">Kota / Daerah</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                    id="city" name="city" value="{{ old('city') }}" placeholder="Contoh: Jakarta Selatan">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="address">Alamat Lengkap</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                id="address" name="address" rows="3" placeholder="Jl. Raya Boulevard No...">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="latitude">Latitude</label>
                                <input type="text" class="form-control @error('latitude') is-invalid @enderror"
                                    id="latitude" name="latitude" value="{{ old('latitude', '-6.2088') }}" placeholder="-6.2088" readonly>
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="longitude">Longitude</label>
                                <input type="text" class="form-control @error('longitude') is-invalid @enderror"
                                    id="longitude" name="longitude" value="{{ old('longitude', '106.8456') }}" placeholder="106.8456" readonly>
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Leaflet Map Picker --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0 fw-semibold">
                                    <i class="ti ti-map-pin me-1 text-danger"></i>Pilih Titik Lokasi Venue di Peta
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-current-location">
                                    <i class="ti ti-current-location me-1"></i> Gunakan Lokasi Saat Ini
                                </button>
                            </div>
                            <div id="venueMap" style="height: 350px; border-radius: 8px; border: 1px solid #dee2e6; z-index: 1;"></div>
                            <small class="text-muted d-block mt-1">
                                <i class="ti ti-info-circle me-1"></i>Klik pada peta atau geser (drag) pin merah untuk menentukan posisi venue yang tepat.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="image">Foto Tempat (Maks 2MB)</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                id="image" name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Venue Aktif dan Siap Menerima Booking</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('venues.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Venue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .leaflet-container {
            font-family: inherit;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const btnCurrentLoc = document.getElementById('btn-current-location');

            // Default koordinat (Jakarta Pusat jika belum ada)
            let currentLat = parseFloat(latInput.value) || -6.2088;
            let currentLng = parseFloat(lngInput.value) || 106.8456;

            // Inisialisasi Peta Leaflet
            const map = L.map('venueMap').setView([currentLat, currentLng], 15);

            // Tile layer OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // Marker dengan drag enabled
            const marker = L.marker([currentLat, currentLng], {
                draggable: true
            }).addTo(map);

            marker.bindPopup('<b>Lokasi Venue</b><br>Geser pin ke titik yang tepat.').openPopup();

            function updateInputs(lat, lng) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
            }

            // Saat marker digeser
            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                updateInputs(position.lat, position.lng);
            });

            // Saat peta diklik di titik mana pun
            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });

            // Fungsi ambil lokasi perangkat saat ini dengan fallback IP Location jika GPS diblokir / timeout
            function fetchCurrentLocation() {
                btnCurrentLoc.disabled = true;
                btnCurrentLoc.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mencari lokasi...';

                function fallbackIpLocation(reason) {
                    // Fallback gratis via ipapi.co jika Geolocation browser ditolak/timeout di desktop Mac/Chrome
                    fetch('https://ipapi.co/json/')
                        .then(res => res.json())
                        .then(data => {
                            if (data.latitude && data.longitude) {
                                const lat = parseFloat(data.latitude);
                                const lng = parseFloat(data.longitude);
                                map.setView([lat, lng], 15);
                                marker.setLatLng([lat, lng]);
                                updateInputs(lat, lng);
                            }
                        })
                        .catch(() => {
                            if (reason) {
                                alert(reason);
                            }
                        })
                        .finally(() => {
                            btnCurrentLoc.disabled = false;
                            btnCurrentLoc.innerHTML = '<i class="ti ti-current-location me-1"></i> Gunakan Lokasi Saat Ini';
                        });
                }

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            map.setView([lat, lng], 16);
                            marker.setLatLng([lat, lng]);
                            updateInputs(lat, lng);

                            btnCurrentLoc.disabled = false;
                            btnCurrentLoc.innerHTML = '<i class="ti ti-current-location me-1"></i> Gunakan Lokasi Saat Ini';
                        },
                        function (error) {
                            console.warn('Browser GPS error code ' + error.code + ': ' + error.message);
                            let msg = 'Izin lokasi browser tidak diberikan.';
                            if (error.code === error.TIMEOUT) {
                                msg = 'Waktu pencarian GPS habis. Menggunakan estimasi jaringan...';
                            } else if (error.code === error.POSITION_UNAVAILABLE) {
                                msg = 'Informasi lokasi tidak tersedia. Menggunakan estimasi jaringan...';
                            }
                            // Jalankan fallback berbasis jaringan IP
                            fallbackIpLocation(msg);
                        },
                        { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 }
                    );
                } else {
                    fallbackIpLocation('Browser tidak mendukung Geolocation.');
                }
            }

            // Jalankan deteksi lokasi saat ini sebagai default saat buka halaman pertama kali (jika belum diisi manual)
            @if(!old('latitude'))
                fetchCurrentLocation();
            @endif

            // Tombol manual "Gunakan Lokasi Saat Ini"
            btnCurrentLoc.addEventListener('click', function (e) {
                e.preventDefault();
                fetchCurrentLocation();
            });

            // Fix tampilan peta saat render di card
            setTimeout(() => {
                map.invalidateSize();
            }, 300);
        });
    </script>
@endpush
