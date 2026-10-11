@extends('layouts.app')

@section('title', 'Manajemen Tenant & Langganan SaaS')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-1 text-gray-800">Manajemen Tenant SaaS</h1>
            <p class="text-muted small mb-0">Pantau dan kelola seluruh penyewa venue, status paket, dan masa aktif langganan bulanan.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.plans.index') }}" class="btn btn-outline-primary">
                <i class="ti ti-packages me-1"></i> Kelola Paket Langganan (Plans)
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('superadmin.tenants.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari nama venue, subdomain, atau email..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    @if($search)
                        <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-light">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Penyewa / Tenant</th>
                        <th>Subdomain & Kontak</th>
                        <th>Kapasitas</th>
                        <th>Status Akun</th>
                        <th>Status Langganan</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        @php
                            $sub = $tenant->subscriptions->first();
                            $isExpired = $sub && $sub->ends_at && \Illuminate\Support\Carbon::parse($sub->ends_at)->isPast();
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md me-3 rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $tenant->name }}</div>
                                        <small class="text-muted font-monospace">ULID: {{ substr($tenant->ulid, 0, 10) }}...</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><span class="badge bg-light text-primary font-monospace">{{ $tenant->subdomain }}.app</span></div>
                                <small class="text-muted">{{ $tenant->email ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $tenant->venues_count ?? 0 }} Cabang</div>
                                <div class="small fw-semibold text-dark">{{ $tenant->resources_count ?? 0 }} Lapangan/Meja</div>
                            </td>
                            <td>
                                @if($tenant->status === 'active')
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Suspended</span>
                                @endif
                            </td>
                            <td>
                                @if($sub)
                                    <span class="badge bg-info-subtle text-info fw-bold">{{ $sub->plan_name ?? 'Custom' }}</span>
                                    @if($isExpired)
                                        <span class="badge bg-danger text-white ms-1">Kadaluwarsa</span>
                                    @else
                                        <span class="badge bg-success text-white ms-1">Aktif</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Belum ada paket</span>
                                @endif
                            </td>
                            <td>
                                @if($sub && $sub->ends_at)
                                    <div class="small {{ $isExpired ? 'text-danger fw-bold' : 'text-dark' }}">
                                        {{ \Illuminate\Support\Carbon::parse($sub->ends_at)->translatedFormat('d M Y') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ \Illuminate\Support\Carbon::parse($sub->ends_at)->diffForHumans() }}
                                    </small>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('superadmin.tenants.show', $tenant->ulid) }}" class="btn btn-sm btn-light" title="Detail & Perpanjang">
                                        <i class="ti ti-eye"></i> Detail
                                    </a>
                                    <form action="{{ route('superadmin.tenants.toggle-status', $tenant->ulid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status tenant ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $tenant->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $tenant->status === 'active' ? 'Suspend Tenant' : 'Aktifkan Tenant' }}">
                                            <i class="ti {{ $tenant->status === 'active' ? 'ti-ban' : 'ti-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-building-store fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada penyewa (tenant) yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tenants->hasPages())
            <div class="card-footer bg-transparent">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
