@extends('layouts.app')

@section('title', 'Master Paket Langganan SaaS (Plans)')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.tenants.index') }}">Tenants</a></li>
                    <li class="breadcrumb-item active">Plans</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Paket Langganan SaaS (Pricing Plans)</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.plans.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Paket Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Paket</th>
                        <th>Harga / Bulan</th>
                        <th>Maks. Lapangan</th>
                        <th>Maks. Staf</th>
                        <th>Penyewa Aktif</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $plan->name }}</div>
                                <span class="badge bg-light text-secondary font-monospace">{{ $plan->slug }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success fs-6">Rp {{ number_format($plan->price, 0, ',', '.') }}</span>
                                <small class="text-muted">/bln</small>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $plan->max_resources }} Lapangan</span></td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $plan->max_staff }} Akun</span></td>
                            <td>
                                <span class="fw-bold text-primary">{{ $plan->subscriptions_count ?? 0 }}</span> Tenant
                            </td>
                            <td>
                                @if($plan->is_active)
                                    <span class="badge bg-success-subtle text-success">Aktif Dijual</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('superadmin.plans.edit', $plan->ulid) }}" class="btn btn-sm btn-light" title="Edit">
                                        <i class="ti ti-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('superadmin.plans.destroy', $plan->ulid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan paket ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Belum ada paket langganan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
