@extends('layouts.app')

@section('title', 'Detail Venue')

@section('content')

    <div class="mb-6">
        <h4 class="mb-0">Detail Venue: {{ $venue->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('venues.index') }}">Venues</a></li>
                <li class="breadcrumb-item active">{{ $venue->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                @if($venue->image)
                    <img src="{{ minio_url($venue->image) }}" class="card-img-top object-fit-cover" height="200" alt="{{ $venue->name }}">
                @else
                    <div class="bg-light text-primary d-flex align-items-center justify-content-center fw-bold" style="height: 200px;">
                        <i class="ti ti-building fs-1"></i>
                    </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-2">{{ $venue->name }}</h5>
                    <p class="text-muted mb-3"><i class="ti ti-map-pin me-1"></i>{{ $venue->address ?? '-' }} ({{ $venue->city ?? '-' }})</p>
                    
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Telepon / WA</span>
                            <span class="fw-semibold">{{ $venue->phone ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Status</span>
                            <span>
                                @if($venue->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger-emphasis">Nonaktif</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Total Lapangan</span>
                            <span class="fw-bold text-primary">{{ $venue->resources->count() }} Unit</span>
                        </li>
                    </ul>

                    <div class="d-flex gap-2">
                        @if(can('venues.edit'))
                            <a href="{{ route('venues.edit', $venue->ulid) }}" class="btn btn-primary flex-grow-1">
                                <i class="ti ti-pencil me-1"></i> Edit Venue
                            </a>
                        @endif
                        <a href="{{ route('venues.index') }}" class="btn btn-light">Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-ball-football me-2 text-primary"></i>Lapangan & Meja di Cabang Ini
                    </h5>
                    @if(can('resources.create'))
                        <a href="{{ route('resources.create') }}?venue_id={{ $venue->ulid }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Tambah Lapangan
                        </a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Unit</th>
                                <th>Kapasitas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($venue->resources as $res)
                                <tr>
                                    <td><span class="badge bg-light text-dark font-monospace">{{ $res->code ?? '-' }}</span></td>
                                    <td class="fw-semibold">{{ $res->name }}</td>
                                    <td>{{ $res->capacity }} Orang</td>
                                    <td>
                                        @if($res->is_active)
                                            <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger-emphasis">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('resources.show', $res->ulid) }}" class="btn btn-sm btn-light">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Belum ada lapangan/meja yang terdaftar di cabang ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
