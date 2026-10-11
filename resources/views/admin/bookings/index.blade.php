@extends('layouts.app')

@section('title', 'Daftar Booking Lapangan')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Daftar & Riwayat Booking</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bookings</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bookings.calendar') }}" class="btn btn-outline-primary">
                <i class="ti ti-calendar me-1"></i> Buka Kalender
            </a>
            @if(can('bookings.create'))
                <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Input Walk-in
                </a>
            @endif
        </div>
    </div>

    <div class="card card-lg">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <form action="{{ route('bookings.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="Cari kode booking, nama pemesan, no WA...">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed (Lunas/Aktif)</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu Bayar)</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                        </div>

                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'status', 'date']))
                                <a href="{{ route('bookings.index') }}" class="btn btn-white">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode Booking</th>
                            <th>Pemesan</th>
                            <th>Cabang & Lapangan</th>
                            <th>Jadwal Main</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $b)
                            <tr>
                                <td>{{ $bookings->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace">{{ $b->booking_code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $b->customer?->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $b->customer?->phone ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $b->resource?->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $b->venue?->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <div>{{ $b->start_at?->format('d M Y') }}</div>
                                    <small class="text-muted fw-semibold">
                                        {{ $b->start_at?->format('H:i') }} - {{ $b->end_at?->format('H:i') }}
                                    </small>
                                </td>
                                <td class="fw-bold">
                                    Rp {{ number_format($b->total_amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if($b->status === 'confirmed')
                                        <span class="badge bg-success-subtle text-success-emphasis">Confirmed</span>
                                    @elseif($b->status === 'pending')
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Pending</span>
                                    @elseif($b->status === 'cancelled')
                                        <span class="badge bg-danger-subtle text-danger-emphasis">Cancelled</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $b->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('bookings.show', $b->ulid) }}" class="btn btn-sm btn-light" title="Detail Invoice">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        @if($b->status !== 'cancelled' && can('bookings.delete'))
                                            <form action="{{ route('bookings.destroy', $b->ulid) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Batalkan">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ti ti-calendar-off fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada transaksi booking.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
                <div class="p-4 border-top">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
