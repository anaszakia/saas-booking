@extends('public.layout')

@section('title', 'Tagihan & Pembayaran #' . $booking->booking_code)

@section('content')

    <div class="row justify-content-center py-3">
        <div class="col-lg-8">

            {{-- Alert Flash --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Timer Alert jika pending --}}
            @if($booking->status === 'pending' && $booking->hold_expires_at)
                <div class="card bg-warning-subtle border-warning p-4 mb-4 text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                        <i class="ti ti-clock-hour-4 text-warning fs-3"></i>
                        <h5 class="fw-bold mb-0 text-warning-emphasis">Slot Lapangan Sedang Ditahan!</h5>
                    </div>
                    <p class="text-muted small mb-3">Selesaikan transfer dan unggah bukti pembayaran sebelum waktu habis:</p>
                    <div class="display-6 fw-bold font-monospace text-danger" id="countdownTimer">
                        15:00
                    </div>
                </div>
            @endif

            {{-- Card Invoice --}}
            <div class="card card-custom p-4 p-md-5 mb-4 bg-white">
                <div class="d-flex justify-content-between align-items-start pb-4 border-bottom mb-4">
                    <div>
                        <span class="badge bg-light text-dark font-monospace mb-2 fs-6">#{{ $booking->booking_code }}</span>
                        <h4 class="fw-bold mb-1">{{ $booking->tenant?->name }}</h4>
                        <p class="text-muted small mb-0">{{ $booking->venue?->name }} • {{ $booking->venue?->address }}</p>
                    </div>
                    <div class="text-end">
                        @if($booking->status === 'confirmed')
                            <span class="badge bg-success fs-6 px-3 py-2">
                                <i class="ti ti-check me-1"></i> RESERVASI AKTIF / LUNAS
                            </span>
                        @elseif($booking->status === 'pending')
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                <i class="ti ti-clock me-1"></i> MENUNGGU PEMBAYARAN
                            </span>
                        @else
                            <span class="badge bg-danger fs-6 px-3 py-2">
                                {{ strtoupper($booking->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Detail Pemesan & Lapangan --}}
                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <span class="text-muted small text-uppercase fw-bold d-block mb-1">Penyewa</span>
                        <div class="fw-bold fs-6">{{ $booking->customer?->name }}</div>
                        <div class="text-muted small">{{ $booking->customer?->phone }}</div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <span class="text-muted small text-uppercase fw-bold d-block mb-1">Unit Lapangan</span>
                        <div class="fw-bold fs-6">{{ $booking->resource?->name }}</div>
                        <div class="text-primary fw-medium small">
                            {{ $booking->start_at?->format('l, d F Y') }}
                        </div>
                        <div class="text-muted small">
                            {{ $booking->start_at?->format('H:i') }} - {{ $booking->end_at?->format('H:i') }} WIB
                        </div>
                    </div>
                </div>

                {{-- Tabel Rincian Biaya --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Deskripsi</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sewa Lapangan ({{ $booking->start_at?->diffInHours($booking->end_at) }} Jam)</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @foreach($booking->addons as $ad)
                                <tr>
                                    <td>{{ $ad->addon_name }} ({{ $ad->quantity }}x)</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($ad->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-active">
                                <th class="fs-5">TOTAL PEMBAYARAN</th>
                                <th class="fs-5 text-end text-success fw-bold">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Tampilan Sukses Pembayaran --}}
                @if($booking->status === 'confirmed')
                    <div class="p-4 bg-success-subtle rounded-3 border border-success mb-4 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center p-3 bg-white text-success rounded-circle shadow-sm mb-3">
                            <i class="ti ti-circle-check fs-1"></i>
                        </div>
                        <h4 class="fw-bold text-success mb-1">Pembayaran Berhasil!</h4>
                        <p class="text-muted small mb-3">
                            Reservasi jadwal sewa lapangan Anda telah aktif dan terkonfirmasi otomatis. Terima kasih!
                        </p>
                        
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="{{ route('public.invoice.download-pdf', $booking->booking_code) }}" class="btn btn-success px-4 py-2 fw-semibold shadow-xs">
                                <i class="ti ti-download me-2"></i> Download File PDF Invoice
                            </a>
                            <a href="{{ route('public.booking', $booking->tenant?->slug ?? 'venue') }}" class="btn btn-outline-primary px-4 py-2 fw-semibold">
                                <i class="ti ti-plus me-2"></i> Buat Booking Baru
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Opsi Pembayaran Midtrans Otomatis (Hanya muncul jika status pending) --}}
                @if($booking->status === 'pending')
                    <div class="p-4 bg-primary-subtle rounded-3 border border-primary mb-4 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center p-3 bg-white rounded-circle shadow-sm mb-3 text-primary">
                            <i class="ti ti-qrcode fs-1"></i>
                        </div>
                        <h5 class="fw-bold mb-1 text-primary">
                            Pembayaran Otomatis Midtrans
                        </h5>
                        <p class="small text-muted mb-3">
                            Tersedia QRIS (GoPay, OVO, ShopeePay, Dana, BCA Mobile) dan Virtual Account semua Bank.<br>
                            Status reservasi otomatis terkonfirmasi instan setelah pembayaran selesai.
                        </p>
                        
                        <button id="btn-pay-online" class="btn btn-primary btn-lg w-100 py-3 shadow-sm fw-bold fs-5">
                            <i class="ti ti-wallet me-2"></i> Bayar Sekarang via Midtrans
                        </button>
                    </div>
                @endif

                {{-- WhatsApp Support & Contact --}}
                <div class="text-center pt-3 border-top">
                    <p class="text-muted small mb-2">Butuh bantuan pengelola venue?</p>
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $booking->tenant?->phone ?? '081234567890')) }}?text=Halo%20saya%20sudah%20melakukan%20pembayaran%20untuk%20booking%20{{ $booking->booking_code }}"
                       target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-4">
                        <i class="ti ti-brand-whatsapp me-1"></i> Hubungi WhatsApp Pengelola
                    </a>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
@if(!empty($tenantCredentials) && $tenantCredentials['provider'] === 'midtrans')
    @php
        $snapJsUrl = ($tenantCredentials['is_sandbox'] ?? true)
            ? 'https://app.sandbox.midtrans.com/snap/snap.js'
            : 'https://app.midtrans.com/snap/snap.js';
        $clientKey = $tenantCredentials['client_key'] ?? '';
    @endphp
    <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script>
        document.getElementById('btn-pay-online')?.addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan Pembayaran...';

            fetch("{{ route('public.invoice.snap-token', $booking->booking_code) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-credit-card me-2"></i> Bayar Sekarang via MIDTRANS';

                if (data.success && data.snap_token) {
                    snap.pay(data.snap_token, {
                        onSuccess: function (result) {
                            window.location.reload();
                        },
                        onPending: function (result) {
                            window.location.reload();
                        },
                        onError: function (result) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Pembayaran Gagal',
                                text: 'Pembayaran tidak berhasil atau telah dibatalkan.',
                                confirmButtonColor: '#0284c7'
                            });
                        }
                    });
                } else if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kendala Gateway Pembayaran',
                        text: data.error || 'Terjadi kesalahan saat memproses pembayaran online.',
                        confirmButtonColor: '#0284c7'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-wallet me-2"></i> Bayar Sekarang via Midtrans';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menghubungi Server',
                    text: 'Tidak dapat memproses transaksi ke server gateway saat ini.',
                    confirmButtonColor: '#0284c7'
                });
            });
        });

        // Buka popup pembayaran otomatis saat halaman invoice selesai dimuat
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function() {
                const payBtn = document.getElementById('btn-pay-online');
                if (payBtn) {
                    payBtn.click();
                }
            }, 500);
        });
    </script>
@elseif(!empty($tenantCredentials) && $tenantCredentials['provider'] === 'xendit')
    <script>
        document.getElementById('btn-pay-online')?.addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan Pembayaran...';

            fetch("{{ route('public.invoice.snap-token', $booking->booking_code) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-credit-card me-2"></i> Bayar Sekarang via XENDIT';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kendala Gateway Pembayaran',
                        text: data.error || 'Terjadi kesalahan saat memproses pembayaran online.',
                        confirmButtonColor: '#0284c7'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-credit-card me-2"></i> Bayar Sekarang via XENDIT';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menghubungi Server',
                    text: 'Tidak dapat memproses transaksi ke server gateway saat ini.',
                    confirmButtonColor: '#0284c7'
                });
            });
        });
    </script>
@endif

@if($booking->status === 'pending' && $booking->hold_expires_at)
<script>
    const expireTime = new Date("{{ $booking->hold_expires_at->toIso8601String() }}").getTime();

    const timerInterval = setInterval(function() {
        const now = new Date().getTime();
        const distance = expireTime - now;

        if (distance < 0) {
            clearInterval(timerInterval);
            document.getElementById("countdownTimer").innerHTML = "WAKTU HABIS";
            return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdownTimer").innerHTML =
            (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
    }, 1000);
</script>
@endif
@endpush
