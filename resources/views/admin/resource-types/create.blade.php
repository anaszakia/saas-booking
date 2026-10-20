@extends('layouts.app')

@section('title', 'Tambah Jenis Olahraga')

@section('content')

    <div class="mb-6">
        <h4 class="mb-0">Tambah Jenis Olahraga Baru</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('resource-types.index') }}">Jenis Olahraga</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-ball-football me-2 text-primary"></i>Informasi Cabang Olahraga
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('resource-types.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold" for="name">Nama Cabang Olahraga <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Contoh: Badminton, Futsal, Basket, Padel Tennis"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="icon">Ikon Tabler</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="ti ti-icons"></i></span>
                                    <input type="text"
                                        class="form-control @error('icon') is-invalid @enderror"
                                        id="icon"
                                        name="icon"
                                        value="{{ old('icon', 'ti ti-ball-football') }}"
                                        placeholder="ti ti-ball-football">
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Gunakan nama class Tabler Icons.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="default_slot_minutes">Default Slot Booking <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number"
                                        class="form-control @error('default_slot_minutes') is-invalid @enderror"
                                        id="default_slot_minutes"
                                        name="default_slot_minutes"
                                        value="{{ old('default_slot_minutes', 60) }}"
                                        min="15"
                                        step="15"
                                        required>
                                    <span class="input-group-text">Menit</span>
                                </div>
                                @error('default_slot_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted" style="font-size: 11px;">Durasi 1 sesi booking (umumnya 60 menit).</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="min_duration_minutes">Min. Durasi Booking <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number"
                                        class="form-control @error('min_duration_minutes') is-invalid @enderror"
                                        id="min_duration_minutes"
                                        name="min_duration_minutes"
                                        value="{{ old('min_duration_minutes', 60) }}"
                                        min="15"
                                        step="15"
                                        required>
                                    <span class="input-group-text">Menit</span>
                                </div>
                                @error('min_duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="max_duration_minutes">Maks. Durasi Booking <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number"
                                        class="form-control @error('max_duration_minutes') is-invalid @enderror"
                                        id="max_duration_minutes"
                                        name="max_duration_minutes"
                                        value="{{ old('max_duration_minutes', 180) }}"
                                        min="15"
                                        step="15"
                                        required>
                                    <span class="input-group-text">Menit</span>
                                </div>
                                @error('max_duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="buffer_minutes">Buffer Waktu (Jeda antar Sesi)</label>
                                <div class="input-group">
                                    <input type="number"
                                        class="form-control @error('buffer_minutes') is-invalid @enderror"
                                        id="buffer_minutes"
                                        name="buffer_minutes"
                                        value="{{ old('buffer_minutes', 0) }}"
                                        min="0"
                                        step="5">
                                    <span class="input-group-text">Menit</span>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Waktu persiapan/pembersihan sebelum pembooking berikutnya.</small>
                            </div>

                            <div class="col-md-6 d-flex align-items-center pt-3">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Aktif dan Siap Digunakan</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <a href="{{ route('resource-types.index') }}" class="btn btn-white me-2">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Jenis Olahraga
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
