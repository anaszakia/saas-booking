@extends('layouts.app')

@section('title', 'Tambah Paket Langganan SaaS')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.plans.index') }}">Plans</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Tambah Paket Baru</h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('superadmin.plans.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Pro, Bisnis, Enterprise" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga per Bulan (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 0) }}" min="0" step="1000" required>
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Batas Maksimal Lapangan / Unit <span class="text-danger">*</span></label>
                                <input type="number" name="max_resources" class="form-control @error('max_resources') is-invalid @enderror" value="{{ old('max_resources', 5) }}" min="1" required>
                                @error('max_resources')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Batas Maksimal Akun Staf / Kasir <span class="text-danger">*</span></label>
                                <input type="number" name="max_staff" class="form-control @error('max_staff') is-invalid @enderror" value="{{ old('max_staff', 3) }}" min="1" required>
                                @error('max_staff')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold">Fitur yang Termasuk:</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="features[online_booking]" id="f_booking" value="1" checked>
                                            <label class="form-check-label" for="f_booking">Portal Booking Publik</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="features[calendar_schedule]" id="f_cal" value="1" checked>
                                            <label class="form-check-label" for="f_cal">Kalender Jadwal Interaktif</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="features[export_reports]" id="f_rep" value="1">
                                            <label class="form-check-label" for="f_rep">Ekspor Laporan Finansial & Excel</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="features[whatsapp_reminder]" id="f_wa" value="1">
                                            <label class="form-check-label" for="f_wa">Pengingat WhatsApp Otomatis</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="features[custom_domain]" id="f_domain" value="1">
                                            <label class="form-check-label" for="f_domain">Dukungan Custom Domain</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">Aktifkan paket untuk pendaftaran baru</label>
                                </div>
                            </div>

                            <div class="col-12 text-end mt-4">
                                <a href="{{ route('superadmin.plans.index') }}" class="btn btn-light me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Paket</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
