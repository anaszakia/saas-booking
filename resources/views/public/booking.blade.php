@extends('public.layout')

@section('title', 'Pesan Lapangan & Meja - ' . $tenant->name)

@section('content')

    {{-- Clean Minimalist Dark Hero --}}
    <div class="portal-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="hero-badge">
                        <i class="ti ti-bolt text-warning"></i>
                        <span>Booking Instan Bebas Bentrok</span>
                    </span>
                    <span class="hero-badge">
                        <i class="ti ti-circle-check text-success"></i>
                        <span>Slot Terverifikasi Real-Time</span>
                    </span>
                </div>
                <h1 class="display-6 fw-bold mb-2 text-white">{{ $tenant->name }}</h1>
                <p class="mb-0 text-white-50 d-flex align-items-center gap-2 small">
                    <i class="ti ti-map-pin text-info"></i>
                    <span>{{ $tenant->address ?? 'Pusat Fasilitas Olahraga & Kebugaran Terpadu' }}</span>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="hero-timezone text-start">
                    <div class="p-2 bg-info bg-opacity-25 rounded-3 text-info">
                        <i class="ti ti-clock-hour-4 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-white-50 small text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Zona Waktu Venue</div>
                        <div class="fw-bold fs-6 text-white">{{ $tenant->timezone }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="ti ti-alert-circle me-2 fs-5 align-middle"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('public.book', $tenant->slug) }}" method="POST" id="bookingForm">
        @csrf

        <div class="row g-4">
            {{-- Kolom Kiri: Pilihan Venue, Tanggal, Lapangan & Slot Jam --}}
            <div class="col-lg-7">
                
                {{-- STEP 1: Lokasi & Kalender Cepat --}}
                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span class="step-number">1</span>
                            <span>Pilih Lokasi & Tanggal</span>
                        </h5>
                        @if(count($venues) > 1)
                            <span class="badge bg-light text-muted border">{{ count($venues) }} Cabang</span>
                        @endif
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold text-muted">Cabang / Venue</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="ti ti-building-stadium"></i></span>
                                <select name="venue_id" id="venueSelect" class="form-select fw-medium" onchange="filterLocation()">
                                    @foreach($venues as $vId => $vName)
                                        <option value="{{ $vId }}" {{ $venueId == $vId ? 'selected' : '' }}>
                                            {{ $vName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-muted">Pilih Lewat Kalender</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="ti ti-calendar"></i></span>
                                <input type="date" name="booking_date" id="dateInput" class="form-control fw-medium"
                                       value="{{ $date }}" min="{{ date('Y-m-d') }}" onchange="filterLocation()">
                            </div>
                        </div>
                    </div>

                    {{-- Horizontal Quick Date Picker --}}
                    <div>
                        <label class="form-label small fw-semibold text-muted d-block mb-2">Pilihan Cepat 7 Hari ke Depan:</label>
                        <div class="date-slider">
                            @php
                                $today = \Carbon\Carbon::today();
                            @endphp
                            @for($i = 0; $i < 7; $i++)
                                @php
                                    $currentPillDate = (clone $today)->addDays($i);
                                    $pillDateString  = $currentPillDate->toDateString();
                                    $isSelectedPill  = ($pillDateString === $date);
                                    $dayName = $i === 0 ? 'Hari Ini' : ($i === 1 ? 'Besok' : $currentPillDate->translatedFormat('D'));
                                @endphp
                                <div class="date-card {{ $isSelectedPill ? 'active' : '' }}" onclick="selectDatePill('{{ $pillDateString }}')">
                                    <div class="date-day small fw-medium {{ $isSelectedPill ? 'text-white' : 'text-muted' }}" style="font-size: 11px;">
                                        {{ $dayName }}
                                    </div>
                                    <div class="date-num fw-bold fs-5 my-1 {{ $isSelectedPill ? 'text-white' : 'text-dark' }}">
                                        {{ $currentPillDate->format('d') }}
                                    </div>
                                    <div class="date-month small opacity-75 {{ $isSelectedPill ? 'text-white' : 'text-muted' }}" style="font-size: 10px;">
                                        {{ $currentPillDate->translatedFormat('M') }}
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Pilih Lapangan / Meja --}}
                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span class="step-number">2</span>
                            <span>Pilih Lapangan & Jam Main</span>
                        </h5>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted mb-2">Pilih Unit Lapangan / Meja:</label>
                        <div class="row g-3" id="courtContainer">
                            @forelse($resources as $res)
                                @php
                                    $resPrice = $resourcePrices[$res->id] ?? 0;
                                @endphp
                                <div class="col-md-6">
                                    <div class="field-card {{ $loop->first ? 'selected' : '' }}" 
                                         id="courtCard_{{ $res->id }}"
                                         data-id="{{ $res->id }}"
                                         data-name="{{ $res->name }}"
                                         data-type="{{ $res->type_name }}"
                                         data-capacity="{{ $res->capacity }}"
                                         data-price="{{ $resPrice }}"
                                         onclick="selectCourt('{{ $res->id }}')">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="p-2 rounded-3 bg-light text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                <i class="{{ $res->type_icon ?? 'ti ti-ball-football' }} fs-3"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="fw-bold mb-0 text-dark">{{ $res->name }}</h6>
                                                    <i class="ti ti-circle-check-filled text-primary check-icon {{ $loop->first ? '' : 'd-none' }}"></i>
                                                </div>
                                                <div class="small text-muted mb-1">{{ $res->type_name }} • Maks {{ $res->capacity }} Pemain</div>
                                                @if($resPrice > 0)
                                                    <span class="badge bg-light text-primary border fw-semibold">
                                                        Rp {{ number_format($resPrice, 0, ',', '.') }} / jam
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <input type="radio" name="resource_id" id="radio_{{ $res->id }}" value="{{ $res->id }}" class="d-none" {{ $loop->first ? 'checked' : '' }}>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="ti ti-building-stadium fs-1 d-block mb-2 opacity-50"></i>
                                    Tidak ada unit lapangan/meja yang aktif pada cabang ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Durasi Bermain</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="ti ti-hourglass"></i></span>
                                <select name="duration_hours" id="durationSelect" class="form-select fw-semibold" onchange="updateDuration()">
                                    <option value="1">1 Jam (60 Menit)</option>
                                    <option value="2">2 Jam (120 Menit)</option>
                                    <option value="3">3 Jam (180 Menit)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Jadwal Mulai Terpilih</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary"><i class="ti ti-clock-play"></i></span>
                                <input type="text" id="selectedTimeDisplay" class="form-control bg-white fw-bold text-primary" value="Silakan pilih slot di bawah" readonly>
                                <input type="hidden" name="start_time" id="startTimeInput" value="" required>
                            </div>
                        </div>
                    </div>

                    {{-- Time Slot Grid --}}
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-semibold text-muted mb-0">Pilihan Slot Jam Tersedia:</label>
                            <div class="d-flex gap-3 small text-muted">
                                <span class="d-flex align-items-center gap-1"><span class="badge bg-white border p-1 rounded-circle" style="width:8px;height:8px;"></span> Tersedia</span>
                                <span class="d-flex align-items-center gap-1"><span class="badge bg-primary p-1 rounded-circle" style="width:8px;height:8px;"></span> Terpilih</span>
                                <span class="d-flex align-items-center gap-1"><span class="badge bg-secondary p-1 rounded-circle" style="width:8px;height:8px;"></span> Penuh</span>
                            </div>
                        </div>

                        <div class="row row-cols-3 row-cols-sm-4 row-cols-md-5 g-2">
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
                                    <div class="time-slot {{ $isBooked ? 'disabled' : '' }}"
                                         data-time="{{ $slotTime }}"
                                         onclick="{{ $isBooked ? '' : 'selectSlotTime(\'' . $slotTime . '\', this)' }}">
                                        <div class="fw-bold fs-6 slot-text">{{ $slotTime }}</div>
                                        <div class="small slot-text" style="font-size: 11px;">
                                            @if($isBooked)
                                                <span class="text-muted"><i class="ti ti-x"></i> Penuh</span>
                                            @else
                                                <span class="text-success"><i class="ti ti-check"></i> Siap</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Add-on Sewa Peralatan (Jika Ada) --}}
                @if(!empty($addons) && count($addons) > 0)
                    <div class="section-card">
                        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <span class="step-number">3</span>
                            <span>Sewa Peralatan Tambahan (Opsional)</span>
                        </h5>
                        <div class="row g-3">
                            @foreach($addons as $ad)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-white d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $ad->name }}</div>
                                            <small class="text-muted">Rp {{ number_format($ad->price, 0, ',', '.') }} / item</small>
                                        </div>
                                        <div class="input-group" style="width: 100px;">
                                            <input type="number" 
                                                   name="addons[{{ $ad->id }}]" 
                                                   class="form-control text-center addon-qty" 
                                                   data-price="{{ $ad->price }}"
                                                   data-name="{{ $ad->name }}"
                                                   min="0" 
                                                   max="10" 
                                                   value="0"
                                                   onchange="recalculateSummary()">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan: Identitas Pemesan & Ringkasan Checkout Live Interaktif --}}
            <div class="col-lg-5">
                <div class="summary-card sticky-top" style="top: 85px;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="p-2 bg-light text-primary rounded-3">
                            <i class="ti ti-receipt fs-4"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">Ringkasan & Kontak</h5>
                            <small class="text-muted">Konfirmasi pemesanan dan detail checkout</small>
                        </div>
                    </div>

                    {{-- Form Identitas --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Lengkap Pemesan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="ti ti-user"></i></span>
                            <input type="text" name="customer_name" class="form-control" placeholder="Contoh: Budi Santoso" required oninput="recalculateSummary()">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success"><i class="ti ti-brand-whatsapp fs-5"></i></span>
                            <input type="tel" name="customer_phone" class="form-control" placeholder="081234567890" required>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Invoice pembayaran & kode booking dikirim ke nomor ini.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Email (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="ti ti-mail"></i></span>
                            <input type="email" name="customer_email" class="form-control" placeholder="budi@example.com">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Catatan Khusus</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Butuh wasit / bola sewa..."></textarea>
                    </div>

                    {{-- Live Order Ticket Receipt --}}
                    <div class="p-3 bg-light border rounded-3 mb-4">
                        <div class="small fw-bold text-uppercase tracking-wider text-muted mb-2 border-bottom pb-1">
                            Detail Tiket Pemesanan
                        </div>

                        <div class="d-flex justify-content-between small py-1">
                            <span class="text-muted">Unit Lapangan:</span>
                            <span class="fw-bold text-dark" id="summaryCourtName">-</span>
                        </div>
                        <div class="d-flex justify-content-between small py-1">
                            <span class="text-muted">Tanggal:</span>
                            <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small py-1">
                            <span class="text-muted">Sesi & Durasi:</span>
                            <span class="fw-semibold text-primary" id="summarySessionText">Belum pilih jam</span>
                        </div>
                        <div class="d-flex justify-content-between small py-1">
                            <span class="text-muted">Biaya Sewa Lapangan:</span>
                            <span class="fw-semibold text-dark" id="summaryCourtPrice">Rp 0</span>
                        </div>

                        <div id="summaryAddonWrapper" class="d-none">
                            <div class="d-flex justify-content-between small py-1">
                                <span class="text-muted">Biaya Peralatan:</span>
                                <span class="fw-semibold text-dark" id="summaryAddonPrice">Rp 0</span>
                            </div>
                        </div>

                        <hr class="my-2 border-secondary border-opacity-25">

                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <span class="fw-bold text-dark">Estimasi Total:</span>
                            <span class="fw-bold fs-4 text-primary" id="summaryTotalPrice">Rp 0</span>
                        </div>
                    </div>

                    <div class="p-3 bg-light border border-info border-opacity-25 rounded-3 mb-4 d-flex align-items-center gap-2">
                        <i class="ti ti-shield-lock text-info fs-3 flex-shrink-0"></i>
                        <small class="text-muted" style="font-size: 11px; line-height: 1.4;">
                            <strong>Garansi Booking Instan:</strong> Slot Anda otomatis terkunci selama 15 menit begitu diarahkan ke payment gateway Midtrans.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-brand btn-lg w-100 shadow-sm" id="btnSubmit" disabled>
                        <i class="ti ti-bolt me-1"></i> Lanjut ke Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    let currentSelectedCourtId = null;
    let currentSelectedPrice = 0;
    let currentDuration = 1;
    let currentStartTime = '';

    document.addEventListener('DOMContentLoaded', function () {
        const firstCourt = document.querySelector('.field-card');
        if (firstCourt) {
            selectCourt(firstCourt.getAttribute('data-id'));
        }
    });

    function filterLocation() {
        const venueId = document.getElementById('venueSelect').value;
        const date = document.getElementById('dateInput').value;
        window.location.href = `{{ url('/b/' . $tenant->slug) }}?venue_id=${venueId}&date=${date}`;
    }

    function selectDatePill(selectedDate) {
        document.getElementById('dateInput').value = selectedDate;
        filterLocation();
    }

    function selectCourt(courtId) {
        currentSelectedCourtId = courtId;

        document.querySelectorAll('.field-card').forEach(card => {
            card.classList.remove('selected');
            const check = card.querySelector('.check-icon');
            if (check) check.classList.add('d-none');
        });

        const activeCard = document.getElementById('courtCard_' + courtId);
        if (activeCard) {
            activeCard.classList.add('selected');
            const check = activeCard.querySelector('.check-icon');
            if (check) check.classList.remove('d-none');

            currentSelectedPrice = parseFloat(activeCard.getAttribute('data-price')) || 0;
            document.getElementById('summaryCourtName').innerText = activeCard.getAttribute('data-name');
        }

        const radio = document.getElementById('radio_' + courtId);
        if (radio) radio.checked = true;

        recalculateSummary();
    }

    function updateDuration() {
        currentDuration = parseInt(document.getElementById('durationSelect').value) || 1;
        updateSessionDisplay();
        recalculateSummary();
    }

    function selectSlotTime(timeStr, element) {
        document.querySelectorAll('.time-slot').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');

        currentStartTime = timeStr;
        document.getElementById('startTimeInput').value = timeStr;

        updateSessionDisplay();
        recalculateSummary();

        document.getElementById('btnSubmit').removeAttribute('disabled');
    }

    function updateSessionDisplay() {
        if (!currentStartTime) return;

        const [hours, minutes] = currentStartTime.split(':').map(Number);
        const endHour = hours + currentDuration;
        const endTimeStr = String(endHour).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');

        const sessionStr = `${currentStartTime} - ${endTimeStr} WIB (${currentDuration} Jam)`;
        document.getElementById('selectedTimeDisplay').value = sessionStr;
        document.getElementById('summarySessionText').innerText = sessionStr;
    }

    function recalculateSummary() {
        const courtTotal = currentSelectedPrice * currentDuration;
        document.getElementById('summaryCourtPrice').innerText = 'Rp ' + courtTotal.toLocaleString('id-ID');

        let addonTotal = 0;
        document.querySelectorAll('.addon-qty').forEach(input => {
            const qty = parseInt(input.value) || 0;
            const price = parseFloat(input.getAttribute('data-price')) || 0;
            addonTotal += (qty * price);
        });

        const addonWrapper = document.getElementById('summaryAddonWrapper');
        if (addonTotal > 0) {
            addonWrapper.classList.remove('d-none');
            document.getElementById('summaryAddonPrice').innerText = 'Rp ' + addonTotal.toLocaleString('id-ID');
        } else {
            addonWrapper.classList.add('d-none');
        }

        const grandTotal = courtTotal + addonTotal;
        document.getElementById('summaryTotalPrice').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }
</script>
@endpush
