@extends('layouts.app')

@section('title', 'Konfigurasi Payment Gateway Venue')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-1 text-gray-800">Pengaturan Pembayaran & Gateway</h1>
            <p class="text-muted small mb-0">Hubungkan akun Midtrans atau Xendit venue Anda agar pelanggan dapat membayar sewa lapangan secara online.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Midtrans Configuration -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-brand-mastercard me-2 text-primary"></i> Midtrans Snap (QRIS, VA, E-Wallet)
                    </h5>
                    @if(isset($settings['midtrans']) && $settings['midtrans']->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.payment-settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="provider" value="midtrans">

                        <div class="mb-3">
                            <label class="form-label">Server Key <span class="text-danger">*</span></label>
                            <input type="password" name="server_key" class="form-control" placeholder="SB-Mid-server-xxxx (Akan dienkripsi aman)">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah kunci tersimpan.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Client Key</label>
                            <input type="text" name="client_key" class="form-control" value="{{ $settings['midtrans']->client_key ?? '' }}" placeholder="SB-Mid-client-xxxx">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Merchant ID</label>
                            <input type="text" name="merchant_id" class="form-control" value="{{ $settings['midtrans']->merchant_id ?? '' }}" placeholder="Gxxxxxx">
                        </div>

                        <div class="mb-3 p-3 bg-light rounded border">
                            <small class="d-block fw-bold mb-1 text-muted">URL Webhook Notifikasi Midtrans:</small>
                            <code class="user-select-all text-primary">{{ url('/webhook/payment/midtrans') }}</code>
                            <small class="d-block text-muted mt-1">Salin URL ini ke Dashboard Midtrans > Settings > Configuration > Payment Notification URL.</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_sandbox" id="mid_sandbox" value="1" {{ (!isset($settings['midtrans']) || $settings['midtrans']->is_sandbox) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mid_sandbox">Mode Sandbox (Uji Coba Testing)</label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="mid_active" value="1" {{ (isset($settings['midtrans']) && $settings['midtrans']->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="mid_active">Gunakan Midtrans untuk pembayaran sewa lapangan</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Simpan Pengaturan Midtrans</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Xendit Configuration -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-currency-dollar me-2 text-primary"></i> Xendit Invoices (VA, QRIS, Retail)
                    </h5>
                    @if(isset($settings['xendit']) && $settings['xendit']->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.payment-settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="provider" value="xendit">

                        <div class="mb-3">
                            <label class="form-label">Secret API Key <span class="text-danger">*</span></label>
                            <input type="password" name="server_key" class="form-control" placeholder="xnd_development_xxxx (Akan dienkripsi)">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah kunci tersimpan.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Verification Token</label>
                            <input type="password" name="webhook_secret" class="form-control" placeholder="Token verifikasi callback Xendit">
                        </div>

                        <div class="mb-3 p-3 bg-light rounded border">
                            <small class="d-block fw-bold mb-1 text-muted">URL Webhook Notifikasi Xendit:</small>
                            <code class="user-select-all text-primary">{{ url('/webhook/payment/xendit') }}</code>
                            <small class="d-block text-muted mt-1">Salin URL ini ke Dashboard Xendit > Settings > Callbacks > Invoices Paid.</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_sandbox" id="xen_sandbox" value="1" {{ (!isset($settings['xendit']) || $settings['xendit']->is_sandbox) ? 'checked' : '' }}>
                            <label class="form-check-label" for="xen_sandbox">Mode Sandbox (Uji Coba Testing)</label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="xen_active" value="1" {{ (isset($settings['xendit']) && $settings['xendit']->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="xen_active">Gunakan Xendit untuk pembayaran sewa lapangan</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Simpan Pengaturan Xendit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
