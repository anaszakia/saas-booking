@extends('layouts.app')

@section('title', 'Detail Booking: ' . $booking->booking_code)

@section('content')

    <div class="mb-6 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Invoice Booking #{{ $booking->booking_code }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active">{{ $booking->booking_code }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bookings.index') }}" class="btn btn-light">
                <i class="ti ti-arrow-left me-1"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="ti ti-printer me-1"></i> Cetak Bukti
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center pb-4 border-bottom mb-4">
                        <div>
                            <h5 class="fw-bold text-primary mb-1">{{ tenant('name') ?? 'Sport Booking' }}</h5>
                            <p class="text-muted small mb-0">{{ $booking->venue?->name }} • {{ $booking->venue?->address }}</p>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $booking->status === 'confirmed' ? 'bg-success' : ($booking->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} fs-6">
                                {{ strtoupper($booking->status) }}
                            </span>
                            <div class="text-muted small mt-1">Dibuat: {{ $booking->created_at?->format('d M Y H:i') }}</div>
                        </div>
                    </div>

                    {{-- Customer & Venue Info --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small fw-bold">Data Pemesan:</h6>
                            <h5 class="fw-semibold mb-1">{{ $booking->customer?->name }}</h5>
                            <p class="text-muted mb-0"><i class="ti ti-brand-whatsapp me-1"></i>{{ $booking->customer?->phone }}</p>
                            @if($booking->customer?->email)
                                <p class="text-muted mb-0"><i class="ti ti-mail me-1"></i>{{ $booking->customer?->email }}</p>
                            @endif
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted text-uppercase small fw-bold">Jadwal Reservasi:</h6>
                            <h5 class="fw-semibold mb-1">{{ $booking->resource?->name }}</h5>
                            <p class="mb-0 fw-medium text-primary">
                                <i class="ti ti-calendar me-1"></i>{{ $booking->start_at?->format('l, d F Y') }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="ti ti-clock me-1"></i>{{ $booking->start_at?->format('H:i') }} - {{ $booking->end_at?->format('H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    {{-- Rincian Biaya --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Deskripsi Item</th>
                                    <th class="text-center">Durasi / Qty</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">Sewa Lapangan: {{ $booking->resource?->name }}</div>
                                        <small class="text-muted">{{ $booking->start_at?->format('H:i') }} s/d {{ $booking->end_at?->format('H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        {{ $booking->start_at?->diffInHours($booking->end_at) }} Jam
                                    </td>
                                    <td class="text-end">
                                        Rp {{ number_format($booking->subtotal / max(1, $booking->start_at?->diffInHours($booking->end_at)), 0, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($booking->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>

                                @foreach($booking->addons as $addon)
                                    <tr>
                                        <td>Sewa Tambahan: {{ $addon->addon_name }}</td>
                                        <td class="text-center">{{ $addon->quantity }}x</td>
                                        <td class="text-end">Rp {{ number_format($addon->price, 0, ',', '.') }}</td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($addon->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Subtotal Sewa Lapangan:</th>
                                    <td class="text-end">Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if($booking->addon_total > 0)
                                    <tr>
                                        <th colspan="3" class="text-end">Total Add-ons:</th>
                                        <td class="text-end">Rp {{ number_format($booking->addon_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr class="table-active fs-5">
                                    <th colspan="3" class="text-end fw-bold">TOTAL TAGIHAN:</th>
                                    <td class="text-end fw-bold text-success">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($booking->notes)
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted fw-bold d-block mb-1">Catatan Pelanggan:</small>
                            <p class="mb-0 text-dark">{{ $booking->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment Status Card --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-cash me-2 text-primary"></i>Status Pembayaran Kasir
                    </h5>
                </div>
                <div class="card-body">
                    @php $payment = $booking->payments->first(); @endphp

                    @if($payment)
                        <div class="mb-3">
                            <label class="text-muted small d-block">Nomor Transaksi:</label>
                            <span class="font-monospace fw-semibold">{{ $payment->payment_number }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small d-block">Metode:</label>
                            <span class="text-capitalize fw-semibold">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small d-block">Status Pembayaran:</label>
                            @if($payment->status === 'success')
                                <span class="badge bg-success fs-6"><i class="ti ti-check me-1"></i>LUNAS</span>
                                <small class="text-muted d-block mt-1">Dikonfirmasi: {{ $payment->confirmed_at?->format('d M Y H:i') }}</small>
                            @else
                                <span class="badge bg-warning text-dark fs-6"><i class="ti ti-clock me-1"></i>MENUNGGU PEMBAYARAN</span>
                            @endif
                        </div>

                        @if($payment->status !== 'success')
                            <div class="p-3 bg-warning-subtle rounded border border-warning mb-3">
                                <small class="text-warning-emphasis d-block fw-semibold mb-2">Konfirmasi Pelunasan Manual:</small>
                                <form action="{{ route('payments.confirm', $payment->ulid) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label small">Bukti Pembayaran / Struk (Opsional):</label>
                                        <input type="file" name="payment_proof" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="ti ti-check me-1"></i> Konfirmasi Lunas Sekarang
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
