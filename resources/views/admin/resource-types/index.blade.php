@extends('layouts.app')

@section('title', 'Kelola Jenis Olahraga')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Kelola Jenis Olahraga</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Jenis Olahraga</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('resource-types.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Tambah Jenis Olahraga
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-lg shadow-sm border-0">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <form action="{{ route('resource-types.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-8 col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    value="{{ $search }}"
                                    placeholder="Cari jenis cabang olahraga...">
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">Cari</button>
                            @if($search)
                                <a href="{{ route('resource-types.index') }}" class="btn btn-white">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Cabang Olahraga</th>
                            <th>Default Slot</th>
                            <th>Min / Maks Durasi</th>
                            <th>Buffer Waktu</th>
                            <th>Total Lapangan</th>
                            <th>Tipe Akses</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($types as $type)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-md rounded-3 bg-light text-primary d-flex align-items-center justify-content-center">
                                            <i class="{{ $type->icon ?? 'ti ti-ball-football' }} fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $type->name }}</h6>
                                            <small class="text-muted font-monospace">slug: {{ $type->slug }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-medium px-2 py-1">
                                        <i class="ti ti-clock me-1"></i>{{ $type->default_slot_minutes }} Menit
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $type->min_duration_minutes }} - {{ $type->max_duration_minutes }} Menit
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $type->buffer_minutes > 0 ? $type->buffer_minutes . ' Menit' : 'Tidak Ada' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        <i class="ti ti-building-stadium me-1"></i>{{ $type->resources_count ?? 0 }} Unit
                                    </span>
                                </td>
                                <td>
                                    @if($type->tenant_id === null)
                                        <span class="badge bg-info-subtle text-info">
                                            <i class="ti ti-world me-1"></i>Global Platform
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="ti ti-building me-1"></i>Khusus Tenant
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($type->is_active)
                                        <span class="badge bg-success-subtle text-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($type->tenant_id !== null || authUser()?->role?->slug === 'superadmin')
                                            <a href="{{ route('resource-types.edit', $type->ulid) }}" class="btn btn-sm btn-white text-dark shadow-xs" title="Edit">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                            <form action="{{ route('resource-types.destroy', $type->ulid) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Hapus"
                                                    data-confirm="Apakah Anda yakin ingin menghapus jenis olahraga '{{ $type->name }}'?"
                                                    data-confirm-title="Hapus Jenis Olahraga?"
                                                    data-confirm-btn="Ya, Hapus"
                                                    data-confirm-color="#dc3545"
                                                    data-confirm-icon="warning">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-light text-muted" title="Template bawaan platform tidak dapat diubah">Terkunci</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ti ti-ball-football fs-1 d-block mb-2 opacity-50"></i>
                                    Belum ada data jenis olahraga. Silakan klik tombol <strong>"Tambah Jenis Olahraga"</strong> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($types->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $types->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
