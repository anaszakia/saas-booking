@extends('layouts.app')

@section('title', 'Tambah Lapangan / Meja Baru')

@section('content')

    <div class="mb-6">
        <h4 class="mb-0">Tambah Lapangan / Meja Baru</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('resources.index') }}">Resources</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-ball-football me-2 text-primary"></i>Informasi Unit Fasilitas
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('resources.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="venue_id">Cabang / Venue <span class="text-danger">*</span></label>
                                <select class="form-select @error('venue_id') is-invalid @enderror" id="venue_id" name="venue_id" required>
                                    <option value="">Pilih Venue</option>
                                    @foreach($venues as $vId => $vName)
                                        <option value="{{ $vId }}" {{ old('venue_id', request('venue_id')) == $vId ? 'selected' : '' }}>
                                            {{ $vName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('venue_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="resource_type_id">Jenis Olahraga <span class="text-danger">*</span></label>
                                <select class="form-select @error('resource_type_id') is-invalid @enderror" id="resource_type_id" name="resource_type_id" required>
                                    <option value="">Pilih Jenis</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('resource_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} (Slot {{ $type->default_slot_minutes }} mnt)
                                        </option>
                                    @endforeach
                                </select>
                                @error('resource_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label" for="name">Nama Lapangan / Meja <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Court Padel 1, Lapangan Mini Soccer A, Meja 5" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="code">Kode Unit</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                    id="code" name="code" value="{{ old('code') }}" placeholder="COURT-01">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="capacity">Kapasitas Pemain (Maksimal)</label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                id="capacity" name="capacity" value="{{ old('capacity', 4) }}" min="1" max="100" required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="image">Foto Lapangan / Meja (Maks 2MB)</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                id="image" name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Unit Aktif dan Siap Dibooking</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('resources.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Unit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
