@extends('public.layout')

@section('title', 'Pesan Lapangan - ' . $tenant->name)

@section('content')

    {{-- Hero Banner --}}
    <div class="hero-banner p-4 p-md-5 mb-5 shadow">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill mb-3">
                    <i class="ti ti-bolt me-1"></i> Booking Instan Bebas Bentrok
                </span>
                <h1 class="display-6 fw-bold mb-2">{{ $tenant->name }}</h1>
                <p class="lead opacity-75 mb-0">
                    <i class="ti ti-map-pin me-1"></i>{{ $tenant->address ?? 'Pusat Olahraga Modern & Berkualitas' }}
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="p-3 bg-white bg-opacity-10 rounded-3 d-inline-block text-start">
                    <div class="small opacity-75">Zona Waktu:</div>
                    <div class="fw-bold fs-6"><i class="ti ti-clock me-1"></i>{{ $tenant->timezone }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('public.book', $tenant->slug) }}" method="POST" id="bookingForm">
        @csrf

        <div class="row g-4">
            {{-- Kolom Kiri: Pilihan Venue, Lapangan & Slot Waktu --}}
            <div class="col-lg-7">
                {{-- Card 1: Pilihan Venue & Tanggal --}}
                <div class="card card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-circle">1</span> Pilih Lokasi & Tanggal
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Cabang / Lokasi</label>
                            <select name="venue_id" id="venueSelect" class="form-select form-select-lg" onchange="filterLocation()">
                                @foreach($venues as $vId => $vName)
                                    <option value="{{ $vId }}" {{ $venueId == $vId ? 'selected' : '' }}>
                                        {{ $vName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Tanggal Main</label>
                            <input type="date" name="booking_date" id="dateInput" class="form-control form-control-lg"
                                   value="{{ $date }}" min="{{ date('Y-m-d') }}" onchange="filterLocation()">
                        </div>
                    </div>
                </div>

                {{-- Card 2: Pilihan Lapangan & Waktu --}}
                <div class="card card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-circle">2</span> Pilih Lapangan & Jam Main
                    </h5>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Pilih Lapangan / Unit:</label>
                        <div class="row g-2">
                            @forelse($resources as $res)
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="resource_id" id="res_{{ $res->id }}"
                                           value="{{ $res->id }}" {{ $loop->first ? 'checked' : '' }} onchange="updateSelectedSlot()">
                                    <label class="btn btn-outline-primary text-start w-100 p-3 rounded-3" for="res_{{ $res->id }}">
                                        <div class="fw-bold fs-6">{{ $res->name }}</div>
                                        <div class="small opacity-75">{{ $res->type_name }} • Kapasitas {{ $res->capacity }} Org</div>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4 text-muted">
                                    Tidak ada unit lapangan aktif di cabang ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Durasi Main</label>
                            <select name="duration_hours" id="durationSelect" class="form-select" onchange="updateSelectedSlot()">
                                <option value="1">1 Jam</option>
                                <option value="2">2 Jam</option>
                                <option value="3">3 Jam</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Jam Mulai Terpilih</label>
                            <input type="text" id="selectedTimeDisplay" class="form-control bg-light fw-bold text-primary" value="Pilih jam di bawah" readonly>
                            <input type="hidden" name="start_time" id="startTimeInput" value="" required>
                        </div>
                    </div>

                    <label class="form-label small text-muted d-block">Pilihan Slot Waktu Tersedia:</label>
                    <div class="row row-cols-3 row-cols-sm-4 g-2">
                        @foreach($timeSlots as $slotTime)
                            @php
                                $slotStart = \Carbon\Carbon::parse($date . ' ' . $slotTime . ':00');
                                $slotEnd   = (clone $slotStart)->addHour();

                                // Cek status apakah bentrok
                                $isBooked = $matrix['bookings']->contains(function($b) use ($slotStart, $slotEnd) {
                                    return \Carbon\Carbon::parse($b->start_at)->lt($slotEnd)
                                        && \Carbon\Carbon::parse($b->end_at)->gt($slotStart);
                                });
                            @endphp

                            <div class="col">
                                <div class="slot-btn {{ $isBooked ? 'disabled' : '' }}"
                                     data-time="{{ $slotTime }}"
                                     onclick="{{ $isBooked ? '' : 'selectSlotTime(\'' . $slotTime . '\', this)' }}">
                                    <div class="fw-bold fs-6">{{ $slotTime }}</div>
                                    <div class="small" style="font-size: 11px;">
                                        {{ $isBooked ? 'Penuh' : 'Tersedia' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Card 3: Add-ons --}}
                @if(!empty($addons))
                    <div class="card card-custom p-4">
                        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-circle">3</span> Tambahan Sewa Peralatan
                        </h5>
                        <div class="row g-3">
                            @foreach($addons as $ad)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-white d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $ad->name }}</div>
                                            <small class="text-muted">Rp {{ number_format($ad->price, 0, ',', '.') }} / item</small>
                                        </div>
                                        <input type="number" name="addons[{{ $ad->id }}]" class="form-control" style="width: 70px;" min="0" max="10" value="0">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan: Identitas Pemesan & Ringkasan Checkout --}}
            <div class="col-lg-5">
                <div class="card card-custom p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3">Informasi Kontak Pemesan</h5>
                    <p class="text-muted small mb-4">Data digunakan untuk pengiriman konfirmasi booking via WhatsApp.</p>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control" placeholder="Contoh: Andi Pratama" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Nomor WhatsApp <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="ti ti-brand-whatsapp text-success fs-5"></i></span>
                            <input type="tel" name="customer_phone" class="form-control" placeholder="081234567890" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Email (Opsional)</label>
                        <input type="email" name="customer_email" class="form-control" placeholder="andi@gmail.com">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted">Catatan Tambahan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Request khusus..."></textarea>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Sistem Penahanan Slot:</span>
                            <span class="text-success fw-semibold"><i class="ti ti-shield-check me-1"></i>Garansi 15 Mnt</span>
                        </div>
                        <small class="text-muted d-block" style="font-size: 11px;">
                            Slot Anda otomatis ditahan selama 15 menit setelah tombol pesan diklik.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow" id="btnSubmit" disabled>
                        <i class="ti ti-bolt me-1"></i> Lanjut ke Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    function filterLocation() {
        const venueId = document.getElementById('venueSelect').value;
        const date = document.getElementById('dateInput').value;
        window.location.href = `{{ url('/b/' . $tenant->slug) }}?venue_id=${venueId}&date=${date}`;
    }

    function selectSlotTime(timeStr, element) {
        document.querySelectorAll('.slot-btn').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');

        document.getElementById('startTimeInput').value = timeStr;
        document.getElementById('selectedTimeDisplay').value = timeStr + ' WIB';
        document.getElementById('btnSubmit').removeAttribute('disabled');
    }

    function updateSelectedSlot() {
        // Trigger jika durasi atau unit berubah
    }
</script>
@endpush
