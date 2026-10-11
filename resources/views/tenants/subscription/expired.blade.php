@extends('layouts.app')

@section('title', 'Langganan Aplikasi Berakhir')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow-lg border-0 rounded-4 p-4 p-md-5">
                <div class="mb-4">
                    <div class="avatar avatar-xxl rounded-circle bg-danger-subtle text-danger mx-auto d-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
                        <i class="ti ti-lock-access fs-1"></i>
                    </div>
                </div>

                <h2 class="fw-bold text-gray-900 mb-2">Masa Langganan SaaS Berakhir</h2>
                <p class="text-muted mb-4">
                    Akses operasional dashboard Anda ditangguhkan sementara karena periode paket langganan bulanan telah melewati batas waktu jatuh tempo.
                </p>

                @if($currentSub)
                    <div class="p-3 bg-light rounded-3 mb-4 text-start">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Paket Terakhir:</span>
                            <span class="fw-bold text-dark">{{ $currentSub->plan_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Batas Jatuh Tempo:</span>
                            <span class="fw-bold text-danger">{{ \Illuminate\Support\Carbon::parse($currentSub->ends_at)->translatedFormat('d F Y, H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Status:</span>
                            <span class="badge bg-danger">{{ strtoupper($currentSub->status) }}</span>
                        </div>
                    </div>
                @endif

                <div class="alert alert-info text-start small mb-4">
                    <i class="ti ti-info-circle me-1"></i> <strong>Cara Mengaktifkan Kembali:</strong><br>
                    Silakan hubungi Super Administrator Platform atau lakukan pembayaran perpanjangan ke rekening platform untuk melanjutkan aktivasi instan.
                </div>

                <div class="d-flex flex-column gap-2">
                    <a href="https://wa.me/?text=Halo%20Admin,%20saya%20ingin%20memperpanjang%20langganan%20aplikasi%20venue%20saya" target="_blank" class="btn btn-success btn-lg">
                        <i class="ti ti-brand-whatsapp me-2"></i> Hubungi Super Admin via WhatsApp
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-light w-100">
                            <i class="ti ti-logout me-1"></i> Keluar (Logout)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
