@extends('layouts.app')

@section('title', 'Detail Lapangan / Meja')

@section('content')

    <div class="mb-6">
        <h4 class="mb-0">Detail: {{ $resource->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('resources.index') }}">Resources</a></li>
                <li class="breadcrumb-item active">{{ $resource->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card">
                @if($resource->image)
                    <img src="{{ minio_url($resource->image) }}" class="card-img-top object-fit-cover" height="240" alt="{{ $resource->name }}">
                @else
                    <div class="bg-light text-primary d-flex align-items-center justify-content-center fw-bold" style="height: 240px;">
                        <i class="ti ti-ball-football fs-1"></i>
                    </div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title fw-bold mb-0">{{ $resource->name }}</h5>
                        <span class="badge bg-light text-dark font-monospace">{{ $resource->code ?? '-' }}</span>
                    </div>
                    <p class="text-muted mb-3">
                        <i class="ti ti-map-pin me-1"></i>{{ $resource->venue?->name ?? '-' }}
                    </p>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Kategori Olahraga</span>
                            <span class="fw-semibold">{{ $resource->resourceType?->name ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Durasi Slot Dasar</span>
                            <span class="fw-semibold">{{ $resource->resourceType?->default_slot_minutes ?? 60 }} Menit</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Kapasitas Maksimal</span>
                            <span class="fw-semibold">{{ $resource->capacity }} Orang</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Status</span>
                            <span>
                                @if($resource->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger-emphasis">Nonaktif</span>
                                @endif
                            </span>
                        </li>
                    </ul>

                    <div class="d-flex gap-2">
                        @if(can('resources.edit'))
                            <a href="{{ route('resources.edit', $resource->ulid) }}" class="btn btn-primary flex-grow-1">
                                <i class="ti ti-pencil me-1"></i> Edit Unit
                            </a>
                        @endif
                        <a href="{{ route('resources.index') }}" class="btn btn-light">Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-calendar me-2 text-primary"></i>Aksi Cepat & Jadwal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="ti ti-info-circle fs-4 me-2"></i>
                        <div>Unit ini siap dijadwalkan dan dibuka untuk pemesanan online maupun walk-in.</div>
                    </div>

                    <h6>Atribut Tambahan Spesifikasi:</h6>
                    @if(!empty($resource->attributes))
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    @foreach($resource->attributes as $key => $val)
                                        <tr>
                                            <th class="bg-light text-capitalize" style="width: 35%;">{{ str_replace('_', ' ', $key) }}</th>
                                            <td>{{ is_array($val) ? json_encode($val) : $val }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Tidak ada atribut spesifikasi khusus.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
