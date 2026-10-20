@extends('layouts.app')

@section('title', 'Manajemen Tenant SaaS')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Manajemen Tenant SaaS</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Tenant SaaS</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.plans.index') }}" class="btn btn-outline-primary">
                <i class="ti ti-packages me-1"></i> Kelola Paket Langganan
            </a>
            <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-primary shadow-xs">
                <i class="ti ti-plus me-1"></i> Tambah Tenant Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-lg shadow-sm border-0">
        <div class="card-body p-0">
            {{-- Filter & Search --}}
            <div class="p-4 border-bottom">
                <form action="{{ route('superadmin.tenants.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-8 col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input type="text"
                                    name="search"
                                    class="form-control border-start-0 ps-0"
                                    value="{{ $search }}"
                                    placeholder="Cari nama venue, subdomain, atau email...">
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">
                                Cari
                            </button>
                            @if($search)
                                <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-white">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Penyewa / Tenant</th>
                            <th>Subdomain & Portal</th>
                            <th>Kapasitas Venue</th>
                            <th>Status Akun</th>
                            <th>Paket Langganan</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            @php
                                $sub = $tenant->subscriptions->first();
                                $isExpired = $sub && $sub->ends_at && \Illuminate\Support\Carbon::parse($sub->ends_at)->isPast();
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md rounded-3 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px; font-size: 15px;">
                                            {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark fs-6">{{ $tenant->name }}</div>
                                            <small class="text-muted font-monospace" style="font-size: 11px;">
                                                ID: {{ substr($tenant->ulid, 0, 12) }}...
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <span class="badge bg-light text-primary border font-monospace">
                                            {{ $tenant->subdomain }}.app
                                        </span>
                                        <a href="{{ route('public.booking', $tenant->slug) }}" target="_blank" class="text-muted small hover-primary" title="Buka Portal Publik">
                                            <i class="ti ti-external-link"></i>
                                        </a>
                                    </div>
                                    <small class="text-muted d-block">{{ $tenant->email ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="small text-dark fw-medium">
                                            <i class="ti ti-building-stadium me-1 text-muted"></i>{{ $tenant->venues_count ?? 0 }} Cabang
                                        </span>
                                        <span class="small text-muted">
                                            <i class="ti ti-ball-football me-1 text-muted"></i>{{ $tenant->resources_count ?? 0 }} Lapangan/Meja
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if($tenant->status === 'active')
                                        <span class="badge bg-success-subtle text-success px-2 py-1">
                                            <i class="ti ti-circle-check me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1">
                                            <i class="ti ti-ban me-1"></i>Suspended
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($sub)
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-primary-subtle text-primary fw-bold">
                                                {{ $sub->plan_name ?? 'Custom' }}
                                            </span>
                                            @if($isExpired)
                                                <span class="badge bg-danger text-white">Kadaluwarsa</span>
                                            @else
                                                <span class="badge bg-success text-white">Aktif</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Belum Langganan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sub && $sub->ends_at)
                                        <div class="small fw-semibold {{ $isExpired ? 'text-danger' : 'text-dark' }}">
                                            {{ \Illuminate\Support\Carbon::parse($sub->ends_at)->translatedFormat('d M Y') }}
                                        </div>
                                        <small class="text-muted font-monospace" style="font-size: 11px;">
                                            {{ \Illuminate\Support\Carbon::parse($sub->ends_at)->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('superadmin.tenants.show', $tenant->ulid) }}" class="btn btn-sm btn-white text-dark shadow-xs" title="Lihat Detail & Perpanjang">
                                            <i class="ti ti-eye me-1"></i> Detail
                                        </a>
                                        <form action="{{ route('superadmin.tenants.toggle-status', $tenant->ulid) }}" method="POST">
                                            @csrf
                                            <button type="button"
                                                class="btn btn-sm {{ $tenant->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                title="{{ $tenant->status === 'active' ? 'Suspend Tenant' : 'Aktifkan Tenant' }}"
                                                data-confirm="Apakah Anda yakin ingin {{ $tenant->status === 'active' ? 'menonaktifkan (suspend)' : 'mengaktifkan kembali' }} akun tenant {{ $tenant->name }}?"
                                                data-confirm-title="{{ $tenant->status === 'active' ? 'Suspend Tenant?' : 'Aktifkan Tenant?' }}"
                                                data-confirm-btn="{{ $tenant->status === 'active' ? 'Ya, Suspend' : 'Ya, Aktifkan' }}"
                                                data-confirm-color="{{ $tenant->status === 'active' ? '#dc3545' : '#198754' }}"
                                                data-confirm-icon="{{ $tenant->status === 'active' ? 'warning' : 'question' }}">
                                                <i class="ti {{ $tenant->status === 'active' ? 'ti-ban' : 'ti-check' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="my-3">
                                        <i class="ti ti-building-store fs-1 text-muted opacity-50 d-block mb-2"></i>
                                        <h6 class="fw-semibold text-dark mb-1">Belum Ada Tenant</h6>
                                        <p class="small text-muted mb-0">Belum ada tenant/penyewa venue yang terdaftar di platform.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tenants->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $tenants->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
