@extends('layouts.app')

@section('title', 'Edit Lapangan / Meja')

@section('content')

    <div class="mb-6">
        <h4 class="mb-0">Edit Lapangan / Meja: {{ $resource->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('resources.index') }}">Resources</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-pencil me-2 text-primary"></i>Ubah Unit Fasilitas
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('resources.update', $resource->ulid) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="venue_id">Cabang / Venue <span class="text-danger">*</span></label>
                                <select class="form-select @error('venue_id') is-invalid @enderror" id="venue_id" name="venue_id" required>
                                    <option value="">Pilih Venue</option>
                                    @foreach($venues as $vId => $vName)
                                        <option value="{{ $vId }}" {{ old('venue_id', $resource->venue_id) == $vId ? 'selected' : '' }}>
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
                                        <option value="{{ $type->id }}" {{ old('resource_type_id', $resource->resource_type_id) == $type->id ? 'selected' : '' }}>
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
                                    id="name" name="name" value="{{ old('name', $resource->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="code">Kode Unit</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                    id="code" name="code" value="{{ old('code', $resource->code) }}">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="capacity">Kapasitas Pemain (Maksimal)</label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                id="capacity" name="capacity" value="{{ old('capacity', $resource->capacity) }}" min="1" max="100" required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($resource->image)
                            <div class="mb-3">
                                <label class="form-label d-block">Foto Saat Ini</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ minio_url($resource->image) }}" alt="{{ $resource->name }}"
                                        class="rounded object-fit-cover border" width="100" height="70" />
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                                        <label class="form-check-label text-danger" for="remove_image">
                                            Hapus foto saat ini
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="image">Ganti Foto Lapangan / Meja</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                id="image" name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $resource->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Unit Aktif dan Siap Dibooking</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('resources.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Update Unit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
