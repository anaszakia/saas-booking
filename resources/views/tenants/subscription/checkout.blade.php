@extends('layouts.app')

@section('title', 'Perpanjang Langganan SaaS')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-1 text-gray-800">Paket & Langganan Venue</h1>
            <p class="text-muted small mb-0">Pilih durasi paket langganan dan bayar secara instan via QRIS, Virtual Account, atau E-Wallet.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Status Saat Ini -->
    @if($currentSub)
        @php
            $isExpired = $currentSub->ends_at && \Illuminate\Support\Carbon::parse($currentSub->ends_at)->isPast();
        @endphp
        <div class="card shadow-sm border-0 mb-4 {{ $isExpired ? 'border-start border-danger border-4' : 'border-start border-success border-4' }}">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }} mb-2">
                            {{ $isExpired ? 'Masa Aktif Berakhir' : 'Langganan Aktif' }}
                        </span>
                        <h4 class="fw-bold mb-1">Paket {{ $currentSub->plan_name }}</h4>
                        <p class="text-muted small mb-0">
                            Berlaku sampai: <strong>{{ \Illuminate\Support\Carbon::parse($currentSub->ends_at)->translatedFormat('d F Y, H:i') }}</strong>
                            ({{ \Illuminate\Support\Carbon::parse($currentSub->ends_at)->diffForHumans() }})
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="fs-4 fw-bold text-primary">Rp {{ number_format($currentSub->plan_price, 0, ',', '.') }}</span>
                        <span class="text-muted small">/bulan</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Pilih Paket & Durasi -->
    <div class="row g-4 mb-4">
        @foreach($plans as $plan)
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 position-relative {{ $currentSub && $currentSub->plan_id == $plan->id ? 'border border-primary' : '' }}">
                    @if($currentSub && $currentSub->plan_id == $plan->id)
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-primary">Paket Anda</span>
                        </div>
                    @endif
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="fw-bold text-dark mb-1">{{ $plan->name }}</h4>
                        <div class="my-3">
                            <span class="fs-2 fw-bold text-primary">Rp {{ number_format($plan->price, 0, ',', '.') }}</span>
                            <span class="text-muted small">/bln</span>
                        </div>
                        <ul class="list-unstyled mb-4 flex-grow-1">
                            <li class="mb-2"><i class="ti ti-check text-success me-2"></i> Hingga <strong>{{ $plan->max_resources }}</strong> Unit Lapangan/Meja</li>
                            <li class="mb-2"><i class="ti ti-check text-success me-2"></i> Hingga <strong>{{ $plan->max_staff }}</strong> Akun Staf / Kasir</li>
                            <li class="mb-2"><i class="ti ti-check text-success me-2"></i> Booking Online & Kalender Jadwal</li>
                            <li class="mb-2"><i class="ti ti-check text-success me-2"></i> Laporan Keuangan Real-time</li>
                        </ul>

                        <form action="{{ route('tenant.subscription.checkout.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            @if(isset($tenantId))
                                <input type="hidden" name="tenant_id" value="{{ $tenantId }}">
                            @endif
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pilih Durasi:</label>
                                <select name="months" class="form-select form-select-sm" required>
                                    <option value="1">1 Bulan - Rp {{ number_format($plan->price * 1, 0, ',', '.') }}</option>
                                    <option value="3">3 Bulan - Rp {{ number_format($plan->price * 3, 0, ',', '.') }}</option>
                                    <option value="6">6 Bulan - Rp {{ number_format($plan->price * 6, 0, ',', '.') }}</option>
                                    <option value="12">12 Bulan (1 Tahun) - Rp {{ number_format($plan->price * 12, 0, ',', '.') }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Metode Pembayaran:</label>
                                <select name="provider" class="form-select form-select-sm" required>
                                    <option value="midtrans">Midtrans (QRIS, VA BCA/Mandiri/BRI, GoPay)</option>
                                    <option value="xendit">Xendit (QRIS, OVO, ShopeePay, VA)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-credit-card me-1"></i> Lanjut ke Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Riwayat Invoice Langganan -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent py-3">
            <h5 class="card-title mb-0">Riwayat Tagihan Langganan</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nomor Invoice</th>
                        <th>Paket & Durasi</th>
                        <th>Total Tagihan</th>
                        <th>Gateway</th>
                        <th>Status</th>
                        <th>Tanggal Bayar</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="fw-bold font-monospace">{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->plan_name }} ({{ $inv->months }} Bulan)</td>
                            <td class="fw-bold text-dark">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                            <td><span class="badge bg-light text-secondary text-uppercase">{{ $inv->provider }}</span></td>
                            <td>
                                @if($inv->status === 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                @elseif($inv->status === 'pending')
                                    <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                                @else
                                    <span class="badge bg-danger">{{ $inv->status }}</span>
                                @endif
                            </td>
                            <td>{{ $inv->paid_at ? \Illuminate\Support\Carbon::parse($inv->paid_at)->format('d M Y, H:i') : '-' }}</td>
                            <td class="text-end">
                                @if($inv->status === 'pending')
                                    <a href="{{ route('tenant.subscription.pay', $inv->ulid) }}" class="btn btn-sm btn-primary">
                                        Bayar Sekarang
                                    </a>
                                @else
                                    <span class="text-muted small"><i class="ti ti-check text-success"></i> Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat tagihan langganan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
