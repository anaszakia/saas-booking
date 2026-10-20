@extends('layouts.app')

@section('title', 'Tambah Tenant Baru')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.tenants.index') }}">Tenant SaaS</a></li>
                    <li class="breadcrumb-item active">Tambah Tenant Baru</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Daftarkan Tenant Baru</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-bold mb-1"><i class="ti ti-alert-triangle me-1"></i> Mohon periksa kembali form berikut:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('superadmin.tenants.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            {{-- Kolom Kiri: Profil Bisnis & Venue Tenant --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                                <i class="ti ti-building-store fs-5"></i>
                            </span>
                            <div>
                                <h5 class="card-title mb-0">1. Profil Usaha / Tenant Venue</h5>
                                <small class="text-muted">Informasi bisnis venue olahraga yang akan didaftarkan.</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="tenant_name" class="form-label fw-semibold">
                                Nama Bisnis / Venue <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="tenant_name"
                                id="tenant_name"
                                class="form-control @error('tenant_name') is-invalid @enderror"
                                value="{{ old('tenant_name') }}"
                                placeholder="Misal: Smash Arena Badminton, Aero Padel Club"
                                required>
                            @error('tenant_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Slug portal publik (/b/slug) dan subdomain akan dibuat otomatis dari nama ini.</small>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Alamat Pusat / Lokasi Utama</label>
                            <textarea
                                name="address"
                                id="address"
                                rows="3"
                                class="form-control @error('address') is-invalid @enderror"
                                placeholder="Jl. Sudirman No. 123, Jakarta Selatan...">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-info-subtle text-info p-2 rounded-3">
                                <i class="ti ti-packages fs-5"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0">Paket Langganan Awal</h6>
                                <small class="text-muted">Tentukan paket SaaS awal untuk tenant ini.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="plan_id" class="form-label fw-semibold">Pilih Paket Langganan</label>
                            <select name="plan_id" id="plan_id" class="form-select @error('plan_id') is-invalid @enderror">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} — Rp {{ number_format($plan->price, 0, ',', '.') }} /bln (Maks {{ $plan->max_resources }} Lapangan)
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="duration_months" class="form-label fw-semibold">Durasi Langganan</label>
                            <select name="duration_months" id="duration_months" class="form-select @error('duration_months') is-invalid @enderror">
                                <option value="0" {{ old('duration_months', '0') == '0' ? 'selected' : '' }}>
                                    🎁 Gratis Trial 14 Hari (Default)
                                </option>
                                <option value="1" {{ old('duration_months') == '1' ? 'selected' : '' }}>1 Bulan (Langsung Aktif)</option>
                                <option value="3" {{ old('duration_months') == '3' ? 'selected' : '' }}>3 Bulan (Langsung Aktif)</option>
                                <option value="6" {{ old('duration_months') == '6' ? 'selected' : '' }}>6 Bulan (Langsung Aktif)</option>
                                <option value="12" {{ old('duration_months') == '12' ? 'selected' : '' }}>12 Bulan / 1 Tahun (Langsung Aktif)</option>
                            </select>
                            @error('duration_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih durasi berbayar jika tenant sudah melakukan transfer manual offline ke SuperAdmin.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Akun Penanggung Jawab (Owner) --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success p-2 rounded-3">
                                <i class="ti ti-user-shield fs-5"></i>
                            </span>
                            <div>
                                <h5 class="card-title mb-0">2. Akun Pemilik (Tenant Owner)</h5>
                                <small class="text-muted">Kredensial login pengelola venue untuk mengakses dashboard tenant.</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="owner_name" class="form-label fw-semibold">
                                Nama Lengkap Owner <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="owner_name"
                                id="owner_name"
                                class="form-control @error('owner_name') is-invalid @enderror"
                                value="{{ old('owner_name') }}"
                                placeholder="Contoh: Budi Santoso"
                                required>
                            @error('owner_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                Alamat Email Login <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                name="email"
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                placeholder="owner@venue.com"
                                required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Digunakan sebagai email login ke dashboard dan notifikasi booking.</small>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Nomor WhatsApp / Telepon</label>
                            <input type="text"
                                name="phone"
                                id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}"
                                placeholder="081234567890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">
                                    Password Akun <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password"
                                        name="password"
                                        id="password"
                                        class="form-control pe-5 @error('password') is-invalid @enderror"
                                        placeholder="Min. 8 karakter"
                                        required>
                                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 border-0 bg-transparent" onclick="togglePasswordVisibility('password', this)" tabindex="-1">
                                        <i class="ti ti-eye-off fs-5"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    Konfirmasi Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password"
                                        name="password_confirmation"
                                        id="password_confirmation"
                                        class="form-control pe-5"
                                        placeholder="Ulangi password"
                                        required>
                                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 border-0 bg-transparent" onclick="togglePasswordVisibility('password_confirmation', this)" tabindex="-1">
                                        <i class="ti ti-eye-off fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border small text-muted mt-4">
                            <i class="ti ti-info-circle me-1 text-primary"></i>
                            Role <strong>Owner</strong> akan otomatis diberikan kepada pengguna ini. Owner dapat menambahkan staf, mengatur lapangan olahraga, dan mengelola pembayaran secara terisolasi.
                        </div>

                        <div class="mt-4 pt-2 text-end">
                            <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-white me-2">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="ti ti-device-floppy me-1"></i> Simpan & Buat Tenant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ti-eye-off');
            icon.classList.add('ti-eye');
            btn.classList.add('text-primary');
            btn.classList.remove('text-muted');
        } else {
            input.type = 'password';
            icon.classList.remove('ti-eye');
            icon.classList.add('ti-eye-off');
            btn.classList.remove('text-primary');
            btn.classList.add('text-muted');
        }
    }
</script>
@endpush
