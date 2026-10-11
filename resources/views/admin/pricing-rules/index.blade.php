@extends('layouts.app')

@section('title', 'Aturan Tarif & Harga')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Aturan Tarif & Harga Lapangan</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Pricing Rules</li>
                </ol>
            </nav>
        </div>
        @if(can('pricing-rules.create'))
            <a href="{{ route('pricing-rules.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Aturan Tarif
            </a>
        @endif
    </div>

    <div class="card card-lg">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <form action="{{ route('pricing-rules.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="Cari nama aturan tarif...">
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">
                                Cari
                            </button>
                            @if(request('search'))
                                <a href="{{ route('pricing-rules.index') }}" class="btn btn-white">
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
                            <th>Nama Aturan</th>
                            <th>Target Penerapan</th>
                            <th>Hari</th>
                            <th>Jam Berlaku</th>
                            <th>Harga / Slot</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rules as $rule)
                            <tr>
                                <td>{{ $rules->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $rule->name }}</td>
                                <td>
                                    @if($rule->resource)
                                        <span class="badge bg-primary-subtle text-primary-emphasis">
                                            Lapangan: {{ $rule->resource->name }}
                                        </span>
                                    @elseif($rule->venue)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                            Cabang: {{ $rule->venue->name }}
                                        </span>
                                    @elseif($rule->resourceType)
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            Jenis: {{ $rule->resourceType->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">Semua Lapangan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rule->day_type === 'weekday')
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Senin - Jumat</span>
                                    @elseif($rule->day_type === 'weekend')
                                        <span class="badge bg-danger-subtle text-danger-emphasis">Sabtu & Minggu</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success-emphasis">Setiap Hari</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rule->start_time && $rule->end_time)
                                        {{ substr($rule->start_time, 0, 5) }} - {{ substr($rule->end_time, 0, 5) }}
                                    @else
                                        <span class="text-muted">Seharian (Full Day)</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-success">
                                    Rp {{ number_format($rule->price_per_slot, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge bg-dark">{{ $rule->priority }}</span>
                                </td>
                                <td>
                                    @if($rule->is_active)
                                        <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(can('pricing-rules.edit'))
                                            <a href="{{ route('pricing-rules.edit', $rule->ulid) }}" class="btn btn-sm btn-light" title="Edit">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                        @endif

                                        @if(can('pricing-rules.delete'))
                                            <form action="{{ route('pricing-rules.destroy', $rule->ulid) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus aturan tarif ini?')">
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
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="ti ti-currency-dollar-off fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada data aturan tarif harga.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($rules->hasPages())
                <div class="p-4 border-top">
                    {{ $rules->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
