@extends('layouts.app')

@section('title', 'Input Booking Walk-in')

@section('content')

    <div class="mb-6">
        <h4 class="mb-0">Input Booking Walk-in / Kasir</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
                <li class="breadcrumb-item active">Walk-in</li>
            </ol>
        </nav>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-calendar-plus me-2 text-primary"></i>Formulir Reservasi Lapangan
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('bookings.store') }}" method="POST">
                        @csrf

                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">1. Informasi Lapangan & Jadwal</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="venue_id">Cabang / Venue <span class="text-danger">*</span></label>
                                <select class="form-select @error('venue_id') is-invalid @enderror" id="venue_id" name="venue_id" onchange="window.location.href='{{ route('bookings.create') }}?venue_id=' + this.value" required>
                                    @foreach($venues as $vId => $vName)
                                        <option value="{{ $vId }}" {{ old('venue_id', request('venue_id', $selectedVenueId)) == $vId ? 'selected' : '' }}>
                                            {{ $vName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="resource_id">Unit Lapangan / Meja <span class="text-danger">*</span></label>
                                <select class="form-select @error('resource_id') is-invalid @enderror" id="resource_id" name="resource_id" required>
                                    <option value="">Pilih Lapangan</option>
                                    @foreach($resources as $res)
                                        <option value="{{ $res->id }}" {{ old('resource_id', request('resource_id')) == $res->id ? 'selected' : '' }}>
                                            {{ $res->name }} ({{ $res->type_name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('resource_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="booking_date">Tanggal Main <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('booking_date') is-invalid @enderror"
                                    id="booking_date" name="booking_date" value="{{ old('booking_date', request('date', date('Y-m-d'))) }}" required>
                                @error('booking_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="start_time">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                    id="start_time" name="start_time" value="{{ old('start_time', request('time', '10:00')) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="duration_hours">Durasi (Jam) <span class="text-danger">*</span></label>
                                <select class="form-select @error('duration_hours') is-invalid @enderror" id="duration_hours" name="duration_hours" required>
                                    <option value="1" {{ old('duration_hours') == 1 ? 'selected' : '' }}>1 Jam</option>
                                    <option value="2" {{ old('duration_hours') == 2 ? 'selected' : '' }}>2 Jam</option>
                                    <option value="3" {{ old('duration_hours') == 3 ? 'selected' : '' }}>3 Jam</option>
                                    <option value="4" {{ old('duration_hours') == 4 ? 'selected' : '' }}>4 Jam</option>
                                </select>
                                @error('duration_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">2. Data Pelanggan (Penyewa)</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="customer_name">Nama Pemesan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                    id="customer_name" name="customer_name" value="{{ old('customer_name') }}" placeholder="Contoh: Budi Santoso" required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="customer_phone">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('customer_phone') is-invalid @enderror"
                                    id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="081234567890" required>
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="notes">Catatan Tambahan (Opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Catatan khusus request pelanggan...">{{ old('notes') }}</textarea>
                        </div>

                        @if(!empty($addons))
                            <hr class="my-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 small">3. Perlengkapan / Add-on Sewa</h6>
                            <div class="row">
                                @foreach($addons as $addon)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center p-3 border rounded bg-light">
                                            <div>
                                                <div class="fw-semibold">{{ $addon->name }}</div>
                                                <small class="text-muted">Rp {{ number_format($addon->price, 0, ',', '.') }} / {{ str_replace('_', ' ', $addon->price_type) }}</small>
                                            </div>
                                            <input type="number" class="form-control" style="width: 80px;"
                                                name="addons[{{ $addon->id }}]" min="0" max="20" value="0">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <hr class="my-4">

                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">4. Metode Pembayaran di Kasir</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="payment_method">Metode Pembayaran</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="cash">Tunai di Kasir (Cash)</option>
                                    <option value="manual_transfer">Transfer Bank Manual</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="payment_status">Status Pembayaran</label>
                                <select class="form-select" id="payment_status" name="payment_status">
                                    <option value="paid">Langsung Lunas (Confirmed)</option>
                                    <option value="unpaid">Belum Lunas (Hold 15 Menit)</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('bookings.calendar') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Simpan & Buat Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
