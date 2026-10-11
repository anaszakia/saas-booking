@extends('layouts.app')

@section('title', 'Data Venue / Cabang')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Data Venue / Cabang</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Venues</li>
                </ol>
            </nav>
        </div>
        @if(can('venues.create'))
            <a href="{{ route('venues.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Venue
            </a>
        @endif
    </div>

    <div class="card card-lg">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <form action="{{ route('venues.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-8 col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="Cari nama venue atau kota...">
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">
                                Cari
                            </button>
                            @if(request('search'))
                                <a href="{{ route('venues.index') }}" class="btn btn-white">
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
                            <th>Venue</th>
                            <th>Kota</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($venues as $venue)
                            <tr>
                                <td>{{ $venues->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($venue->image)
                                            <img src="{{ minio_url($venue->image) }}"
                                                alt="{{ $venue->name }}"
                                                class="rounded object-fit-cover"
                                                width="44" height="44" />
                                        @else
                                            <div class="rounded bg-light text-primary d-flex align-items-center justify-content-center fw-bold"
                                                style="width: 44px; height: 44px;">
                                                <i class="ti ti-building fs-4"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $venue->name }}</div>
                                            <small class="text-muted">{{ Str::limit($venue->address, 40) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $venue->city ?? '-' }}</td>
                                <td>{{ $venue->phone ?? '-' }}</td>
                                <td>
                                    @if($venue->is_active)
                                        <span class="badge bg-success-subtle text-success-emphasis">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $venue->created_at?->format('d M Y') ?? '-' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('venues.show', $venue->ulid) }}" class="btn btn-sm btn-light" title="Detail">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        @if(can('venues.edit'))
                                            <a href="{{ route('venues.edit', $venue->ulid) }}" class="btn btn-sm btn-light" title="Edit">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                        @endif

                                        @if(can('venues.delete'))
                                            <form action="{{ route('venues.destroy', $venue->ulid) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus venue ini?')">
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
                                    <i class="ti ti-building-off fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada data venue.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($venues->hasPages())
                <div class="p-4 border-top">
                    {{ $venues->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
