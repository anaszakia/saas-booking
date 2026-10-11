@extends('layouts.app')

@section('title', 'Konfigurasi Payment Gateway Platform Super Admin')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.tenants.index') }}">SaaS Platform</a></li>
                    <li class="breadcrumb-item active">Gateway Pembayaran</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1 text-gray-800">Gateway Pembayaran SaaS Platform</h1>
            <p class="text-muted small mb-0">
                Atur akun Midtrans atau Xendit milik <strong>Platform Anda</strong>. Seluruh uang pembayaran langganan bulanan dari para pemilik venue akan langsung masuk ke rekening ini.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Midtrans Platform Configuration -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 border-top border-primary border-4">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-brand-mastercard me-2 text-primary"></i> Midtrans Snap (Penerima Langganan SaaS)
                    </h5>
                    @if(isset($settings['midtrans']) && $settings['midtrans']->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{ route('superadmin.payment-settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="provider" value="midtrans">

                        <div class="mb-3">
                            <label class="form-label">Server Key Platform <span class="text-danger">*</span></label>
                            <input type="password" name="server_key" class="form-control font-monospace" placeholder="SB-Mid-server-xxxx">
                            <small class="text-muted">Tersimpan terenkripsi aman di database. Biarkan kosong jika tidak ingin mengganti.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Client Key Platform</label>
                            <input type="text" name="client_key" class="form-control font-monospace" value="{{ $settings['midtrans']->client_key ?? '' }}" placeholder="SB-Mid-client-xxxx">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Merchant ID Platform</label>
                            <input type="text" name="merchant_id" class="form-control" value="{{ $settings['midtrans']->merchant_id ?? '' }}" placeholder="Gxxxxxx">
                        </div>

                        <div class="mb-3 p-3 bg-light rounded border">
                            <small class="d-block fw-bold mb-1 text-muted">URL Webhook Notifikasi Langganan SaaS:</small>
                            <code class="user-select-all text-primary fw-bold">{{ url('/webhook/subscription/midtrans') }}</code>
                            <small class="d-block text-muted mt-1">Salin URL ini ke Dashboard Midtrans Anda > Settings > Configuration > Payment Notification URL agar status langganan venue otomatis diperpanjang setelah dibayar.</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_sandbox" id="mid_sandbox" value="1" {{ (!isset($settings['midtrans']) || $settings['midtrans']->is_sandbox) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mid_sandbox">Mode Sandbox (Uji Coba)</label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="mid_active" value="1" {{ (isset($settings['midtrans']) && $settings['midtrans']->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="mid_active">Aktifkan Midtrans sebagai penerima bayaran langganan SaaS</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Gateway Midtrans Platform
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Xendit Platform Configuration -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 border-top border-info border-4">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-currency-dollar me-2 text-info"></i> Xendit Invoices (Penerima Langganan SaaS)
                    </h5>
                    @if(isset($settings['xendit']) && $settings['xendit']->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{ route('superadmin.payment-settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="provider" value="xendit">

                        <div class="mb-3">
                            <label class="form-label">Secret API Key Platform <span class="text-danger">*</span></label>
                            <input type="password" name="server_key" class="form-control font-monospace" placeholder="xnd_development_xxxx">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah kunci tersimpan.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Verification Token Platform</label>
                            <input type="password" name="webhook_secret" class="form-control font-monospace" placeholder="Token verifikasi callback Xendit">
                        </div>

                        <div class="mb-3 p-3 bg-light rounded border">
                            <small class="d-block fw-bold mb-1 text-muted">URL Webhook Notifikasi Langganan SaaS:</small>
                            <code class="user-select-all text-info fw-bold">{{ url('/webhook/subscription/xendit') }}</code>
                            <small class="d-block text-muted mt-1">Salin URL ini ke Dashboard Xendit Anda > Settings > Callbacks > Invoices Paid.</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_sandbox" id="xen_sandbox" value="1" {{ (!isset($settings['xendit']) || $settings['xendit']->is_sandbox) ? 'checked' : '' }}>
                            <label class="form-check-label" for="xen_sandbox">Mode Sandbox (Uji Coba)</label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="xen_active" value="1" {{ (isset($settings['xendit']) && $settings['xendit']->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="xen_active">Aktifkan Xendit sebagai penerima bayaran langganan SaaS</label>
                        </div>

                        <button type="submit" class="btn btn-info text-white w-100">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Gateway Xendit Platform
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
