@extends('layouts.app')

@section('title', 'Manajemen Lapangan & Meja')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Manajemen Lapangan & Meja</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Resources</li>
                </ol>
            </nav>
        </div>
        @if(can('resources.create'))
            <a href="{{ route('resources.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Lapangan / Meja
            </a>
        @endif
    </div>

    <div class="card card-lg">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <form action="{{ route('resources.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="Cari nama atau kode unit...">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select name="venue_id" class="form-select">
                                <option value="">Semua Cabang / Venue</option>
                                @foreach($venues as $vId => $vName)
                                    <option value="{{ $vId }}" {{ request('venue_id') == $vId ? 'selected' : '' }}>
                                        {{ $vName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="resource_type_id" class="form-select">
                                <option value="">Semua Jenis</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" {{ request('resource_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'venue_id', 'resource_type_id']))
                                <a href="{{ route('resources.index') }}" class="btn btn-white">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Unit / Lapangan</th>
                            <th>Venue / Cabang</th>
                            <th>Kategori Olahraga</th>
                            <th>Kapasitas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resources as $res)
                            <tr>
                                <td>{{ $resources->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($res->image)
                                            <img src="{{ minio_url($res->image) }}"
                                                alt="{{ $res->name }}"
                                                class="rounded object-fit-cover"
                                                width="44" height="44" />
                                        @else
                                            <div class="rounded bg-light text-primary d-flex align-items-center justify-content-center fw-bold font-monospace"
                                                style="width: 44px; height: 44px;">
                                                <i class="ti ti-ball-football fs-4"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $res->name }}</div>
                                            <small class="text-muted font-monospace">{{ $res->code ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $res->venue?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info-emphasis">
                                        {{ $res->resourceType?->name ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $res->capacity }} Orang</td>
                                <td>
                                    @if($res->is_active)
                                        <span class="badge bg-success-subtle text-success-emphasis">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('resources.show', $res->ulid) }}" class="btn btn-sm btn-light" title="Detail">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        @if(can('resources.edit'))
                                            <a href="{{ route('resources.edit', $res->ulid) }}" class="btn btn-sm btn-light" title="Edit">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                        @endif

                                        @if(can('resources.delete'))
                                            <form action="{{ route('resources.destroy', $res->ulid) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit lapangan/meja ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ti ti-ball-football-off fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada data lapangan / meja.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($resources->hasPages())
                <div class="p-4 border-top">
                    {{ $resources->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
