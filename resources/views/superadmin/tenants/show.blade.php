@extends('layouts.app')

@section('title', 'Detail Tenant: ' . $tenant->name)

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.tenants.index') }}">Tenants</a></li>
                    <li class="breadcrumb-item active">{{ $tenant->name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">{{ $tenant->name }}</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-light">
                <i class="ti ti-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Informasi Tenant -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0">Informasi Bisnis & Profil</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Status Akun:</span>
                            @if($tenant->status === 'active')
                                <span class="badge bg-success-subtle text-success">Aktif</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Suspended</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Subdomain Akses:</span>
                            <span class="fw-bold font-monospace">{{ $tenant->subdomain }}.app</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Slug Booking Publik:</span>
                            <a href="{{ route('public.booking', $tenant->slug) }}" target="_blank" class="fw-semibold">
                                /b/{{ $tenant->slug }} <i class="ti ti-external-link"></i>
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Email Kontak:</span>
                            <span>{{ $tenant->email ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Nomor WhatsApp:</span>
                            <span>{{ $tenant->phone ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Terdaftar Sejak:</span>
                            <span>{{ $tenant->created_at?->format('d M Y, H:i') }}</span>
                        </li>
                    </ul>

                    <form action="{{ route('superadmin.tenants.toggle-status', $tenant->ulid) }}" method="POST">
                        @csrf
                        <button type="button" class="btn w-100 {{ $tenant->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                data-confirm="Apakah Anda yakin ingin {{ $tenant->status === 'active' ? 'menonaktifkan (suspend)' : 'mengaktifkan kembali' }} akun tenant {{ $tenant->name }}?"
                                data-confirm-title="{{ $tenant->status === 'active' ? 'Suspend Tenant?' : 'Aktifkan Tenant?' }}"
                                data-confirm-btn="{{ $tenant->status === 'active' ? 'Ya, Suspend' : 'Ya, Aktifkan' }}"
                                data-confirm-color="{{ $tenant->status === 'active' ? '#dc3545' : '#198754' }}"
                                data-confirm-icon="{{ $tenant->status === 'active' ? 'warning' : 'question' }}">
                            <i class="ti {{ $tenant->status === 'active' ? 'ti-ban' : 'ti-check' }} me-1"></i>
                            {{ $tenant->status === 'active' ? 'Tangguhkan / Suspend Akun' : 'Aktifkan Akun' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kontrol Langganan Bulanan (SaaS Subscription) -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4 border-top border-primary border-4">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Status Langganan Bulanan (Subscription)</h5>
                    @if($currentSub)
                        <span class="badge bg-primary fs-6">{{ $currentSub->plan_name }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($currentSub)
                        @php
                            $isExpired = $currentSub->ends_at && \Illuminate\Support\Carbon::parse($currentSub->ends_at)->isPast();
                        @endphp
                        <div class="p-3 rounded {{ $isExpired ? 'bg-danger-subtle border border-danger' : 'bg-success-subtle border border-success' }} mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold fs-5 {{ $isExpired ? 'text-danger' : 'text-success' }}">
                                    <i class="ti {{ $isExpired ? 'ti-alert-circle' : 'ti-circle-check' }} me-1"></i>
                                    {{ $isExpired ? 'Langganan Telah Berakhir' : 'Langganan Sedang Aktif' }}
                                </span>
                                <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }}">
                                    {{ strtoupper($currentSub->status) }}
                                </span>
                            </div>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Masa Berlaku Berakhir Pada:</small>
                                    <strong>{{ \Illuminate\Support\Carbon::parse($currentSub->ends_at)->translatedFormat('d F Y, H:i') }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Sisa Waktu:</small>
                                    <strong>{{ \Illuminate\Support\Carbon::parse($currentSub->ends_at)->diffForHumans() }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Batas Maksimal Lapangan</div>
                                    <div class="fs-4 fw-bold text-dark">{{ $currentSub->max_resources }} Unit</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Batas Akun Staf / Kasir</div>
                                    <div class="fs-4 fw-bold text-dark">{{ $currentSub->max_staff }} Akun</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-4">
                            <i class="ti ti-info-circle me-1"></i> Tenant ini belum memiliki paket langganan aktif. Silakan pilih paket di bawah.
                        </div>
                    @endif

                    <h6 class="fw-bold mb-3"><i class="ti ti-calendar-plus me-1 text-primary"></i> Perpanjang / Tetapkan Langganan Manual</h6>
                    <form action="{{ route('superadmin.tenants.extend', $tenant->ulid) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Pilih Paket Langganan</label>
                            <select name="plan_id" class="form-select" required>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ ($currentSub && $currentSub->plan_id == $plan->id) ? 'selected' : '' }}>
                                        {{ $plan->name }} (Rp {{ number_format($plan->price, 0, ',', '.') }}/bln)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durasi Perpanjangan</label>
                            <select name="months" class="form-select" required>
                                <option value="1">1 Bulan</option>
                                <option value="3">3 Bulan (1 Kuartal)</option>
                                <option value="6">6 Bulan (Semester)</option>
                                <option value="12">12 Bulan (1 Tahun)</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Perpanjang Langganan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
