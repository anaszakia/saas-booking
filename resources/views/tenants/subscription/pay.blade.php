@extends('layouts.app')

@section('title', 'Bayar Tagihan: ' . $invoice->invoice_number)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold font-monospace text-muted">{{ $invoice->invoice_number }}</span>
                    @if($invoice->status === 'paid')
                        <span class="badge bg-success">Lunas</span>
                    @else
                        <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                    @endif
                </div>
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Tagihan Langganan:</small>
                        <h2 class="fw-bold text-primary mb-0">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</h2>
                    </div>

                    <div class="p-3 bg-light rounded-3 text-start mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Paket Langganan:</span>
                            <span class="fw-semibold">{{ $invoice->plan->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Durasi:</span>
                            <span class="fw-semibold">{{ $invoice->months }} Bulan</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Gateway Pembayaran:</span>
                            <span class="fw-bold text-uppercase">{{ $invoice->provider }}</span>
                        </div>
                    </div>

                    @if($invoice->status === 'paid')
                        <div class="alert alert-success">
                            <i class="ti ti-check fs-4 d-block mb-1"></i>
                            Pembayaran telah diterima! Langganan venue Anda telah otomatis diperpanjang.
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary w-100">Kembali ke Dashboard</a>
                    @else
                        @if($invoice->provider === 'midtrans' && $invoice->snap_token)
                            <button id="pay-button" class="btn btn-success btn-lg w-100 py-3 shadow-sm">
                                <i class="ti ti-qrcode me-2"></i> Bayar Sekarang via Midtrans (QRIS / VA)
                            </button>
                        @elseif($invoice->checkout_url)
                            <a href="{{ $invoice->checkout_url }}" target="_blank" class="btn btn-primary btn-lg w-100 py-3 shadow-sm">
                                <i class="ti ti-external-link me-2"></i> Buka Halaman Pembayaran Xendit
                            </a>
                        @else
                            <div class="alert alert-warning">
                                Token transaksi belum siap. Silakan refresh halaman atau hubungi Super Admin.
                            </div>
                        @endif
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('tenant.subscription.checkout') }}" class="text-muted small">
                            <i class="ti ti-arrow-left me-1"></i> Pilih paket lain / Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($invoice->provider === 'midtrans' && $invoice->snap_token)
    @php
        $snapJsUrl = ($platformCreds['is_sandbox'] ?? true)
            ? 'https://app.sandbox.midtrans.com/snap/snap.js'
            : 'https://app.midtrans.com/snap/snap.js';
        $clientKey = $platformCreds['client_key'] ?? '';
    @endphp
    <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script>
        document.getElementById('pay-button').onclick = function () {
            snap.pay('{{ $invoice->snap_token }}', {
                onSuccess: function (result) {
                    window.location.reload();
                },
                onPending: function (result) {
                    window.location.reload();
                },
                onError: function (result) {
                    alert('Pembayaran gagal atau dibatalkan.');
                },
                onClose: function () {
                    console.log('Popup ditutup tanpa menyelesaikan pembayaran');
                }
            });
        };
    </script>
@endif
@endsection
