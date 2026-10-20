@extends('layouts.app')

@section('title', 'Kalender Ketersediaan Lapangan')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Kalender Ketersediaan Lapangan</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kalender</li>
                </ol>
            </nav>
        </div>
        @if(can('bookings.create'))
            <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Booking Walk-in
            </a>
        @endif
    </div>

    {{-- Filter Bar --}}
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('bookings.calendar') }}" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Pilih Cabang / Venue</label>
                        <select name="venue_id" class="form-select" onchange="this.form.submit()">
                            @foreach($venues as $vId => $vName)
                                <option value="{{ $vId }}" {{ $venueId == $vId ? 'selected' : '' }}>
                                    {{ $vName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2 pt-4">
                        <a href="{{ route('bookings.calendar') }}?venue_id={{ $venueId }}&date={{ \Carbon\Carbon::parse($date)->subDay()->toDateString() }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-chevron-left me-1"></i> Hari Sebelumnya
                        </a>
                        <a href="{{ route('bookings.calendar') }}?venue_id={{ $venueId }}&date={{ \Carbon\Carbon::today()->toDateString() }}"
                           class="btn btn-light btn-sm">
                            Hari Ini
                        </a>
                        <a href="{{ route('bookings.calendar') }}?venue_id={{ $venueId }}&date={{ \Carbon\Carbon::parse($date)->addDay()->toDateString() }}"
                           class="btn btn-outline-secondary btn-sm">
                            Hari Berikutnya <i class="ti ti-chevron-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Legend --}}
    <div class="d-flex align-items-center gap-4 mb-3 px-2">
        <span class="d-flex align-items-center gap-1 small">
            <span class="badge bg-success rounded-circle p-1" style="width: 12px; height: 12px;"> </span>
            Tersedia (Kosong)
        </span>
        <span class="d-flex align-items-center gap-1 small">
            <span class="badge bg-danger rounded-circle p-1" style="width: 12px; height: 12px;"> </span>
            Terisi (Confirmed)
        </span>
        <span class="d-flex align-items-center gap-1 small">
            <span class="badge bg-warning rounded-circle p-1" style="width: 12px; height: 12px;"> </span>
            Pending / Hold (15 Mnt)
        </span>
    </div>

    {{-- Interactive Calendar Grid --}}
    <div class="card card-lg overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center" style="min-width: 800px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Jam</th>
                        @forelse($matrix['resources'] as $res)
                            <th>
                                <div class="fw-bold">{{ $res->name }}</div>
                                <small class="text-muted font-monospace">{{ $res->code ?? '-' }}</small>
                            </th>
                        @empty
                            <th>Tidak ada data lapangan di cabang ini</th>
                        @endforelse
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $slotTime)
                        @php
                            $slotStart = \Carbon\Carbon::parse($date . ' ' . $slotTime . ':00');
                            $slotEnd   = (clone $slotStart)->addHour();
                        @endphp
                        <tr>
                            <td class="fw-semibold bg-light text-muted align-middle py-3">
                                {{ $slotTime }}
                            </td>

                            @foreach($matrix['resources'] as $res)
                                @php
                                    // Cek apakah ada booking yang overlapping di slot ini
                                    $matchedBooking = $matrix['bookings']->first(function($b) use ($res, $slotStart, $slotEnd) {
                                        return $b->resource_id == $res->id
                                            && \Carbon\Carbon::parse($b->start_at)->lt($slotEnd)
                                            && \Carbon\Carbon::parse($b->end_at)->gt($slotStart);
                                    });
                                @endphp

                                <td class="p-2 align-middle" style="height: 65px;">
                                    @if($matchedBooking)
                                        @if($matchedBooking->status === 'confirmed')
                                            <a href="{{ route('bookings.show', $matchedBooking->id) }}"
                                               class="d-block p-2 rounded text-decoration-none text-white bg-danger shadow-sm h-100 d-flex flex-column justify-content-center">
                                                <div class="fw-bold small text-truncate">{{ $matchedBooking->customer_name }}</div>
                                                <small class="opacity-75 font-monospace" style="font-size: 10px;">{{ $matchedBooking->booking_code }}</small>
                                            </a>
                                        @else
                                            <a href="{{ route('bookings.show', $matchedBooking->id) }}"
                                               class="d-block p-2 rounded text-decoration-none text-dark bg-warning shadow-sm h-100 d-flex flex-column justify-content-center">
                                                <div class="fw-bold small text-truncate">Hold: {{ $matchedBooking->customer_name }}</div>
                                                <small class="opacity-75 font-monospace" style="font-size: 10px;">Menunggu Bayar</small>
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('bookings.create') }}?venue_id={{ $venueId }}&resource_id={{ $res->id }}&date={{ $date }}&time={{ $slotTime }}"
                                           class="d-block rounded p-2 text-decoration-none text-success bg-success-subtle h-100 d-flex align-items-center justify-content-center hover-slot"
                                           title="Klik untuk booking">
                                            <span class="small fw-semibold">+ Booking</span>
                                        </a>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .hover-slot:hover {
            background-color: #198754 !important;
            color: #fff !important;
            transition: all 0.2s ease-in-out;
        }
    </style>

@endsection
