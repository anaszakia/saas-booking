@extends('layouts.app')

@section('title', 'Edit Aturan Tarif')

@section('content')

    <div class="mb-6">
        <h4 class="mb-0">Edit Aturan Tarif: {{ $pricingRule->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pricing-rules.index') }}">Pricing Rules</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-pencil me-2 text-primary"></i>Ubah Konfigurasi Tarif
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pricing-rules.update', $pricingRule->ulid) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="name">Nama Aturan Tarif <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name', $pricingRule->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="venue_id">Cabang / Venue (Opsional)</label>
                                <select class="form-select @error('venue_id') is-invalid @enderror" id="venue_id" name="venue_id">
                                    <option value="">Semua Venue</option>
                                    @foreach($venues as $vId => $vName)
                                        <option value="{{ $vId }}" {{ old('venue_id', $pricingRule->venue_id) == $vId ? 'selected' : '' }}>
                                            {{ $vName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('venue_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="resource_type_id">Kategori Olahraga (Opsional)</label>
                                <select class="form-select @error('resource_type_id') is-invalid @enderror" id="resource_type_id" name="resource_type_id">
                                    <option value="">Semua Kategori</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('resource_type_id', $pricingRule->resource_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('resource_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="resource_id">Lapangan Spesifik (Opsional)</label>
                                <select class="form-select @error('resource_id') is-invalid @enderror" id="resource_id" name="resource_id">
                                    <option value="">Semua Lapangan</option>
                                    @foreach($resources as $res)
                                        <option value="{{ $res->id }}" {{ old('resource_id', $pricingRule->resource_id) == $res->id ? 'selected' : '' }}>
                                            {{ $res->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('resource_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="day_type">Tipe Hari <span class="text-danger">*</span></label>
                                <select class="form-select @error('day_type') is-invalid @enderror" id="day_type" name="day_type" required>
                                    <option value="all" {{ old('day_type', $pricingRule->day_type) == 'all' ? 'selected' : '' }}>Setiap Hari (All Days)</option>
                                    <option value="weekday" {{ old('day_type', $pricingRule->day_type) == 'weekday' ? 'selected' : '' }}>Senin - Jumat (Weekday)</option>
                                    <option value="weekend" {{ old('day_type', $pricingRule->day_type) == 'weekend' ? 'selected' : '' }}>Sabtu & Minggu (Weekend)</option>
                                </select>
                                @error('day_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="start_time">Jam Mulai (Opsional)</label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                    id="start_time" name="start_time" value="{{ old('start_time', $pricingRule->start_time ? substr($pricingRule->start_time, 0, 5) : '') }}">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="end_time">Jam Selesai (Opsional)</label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                    id="end_time" name="end_time" value="{{ old('end_time', $pricingRule->end_time ? substr($pricingRule->end_time, 0, 5) : '') }}">
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="price_per_slot">Harga per Slot (Rupiah) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('price_per_slot') is-invalid @enderror"
                                        id="price_per_slot" name="price_per_slot" value="{{ old('price_per_slot', (int) $pricingRule->price_per_slot) }}" min="0" step="1000" required>
                                </div>
                                @error('price_per_slot')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="priority">Prioritas Rule <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('priority') is-invalid @enderror"
                                    id="priority" name="priority" value="{{ old('priority', $pricingRule->priority) }}" min="0" max="100" required>
                                <small class="text-muted">Prioritas lebih tinggi didahulukan jika ada bentrok jam.</small>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $pricingRule->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aturan Tarif Aktif</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('pricing-rules.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Update Tarif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
