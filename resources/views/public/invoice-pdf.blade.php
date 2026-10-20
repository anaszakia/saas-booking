<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $booking->booking_code }}</title>
    <style>
        @page {
            margin: 24px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .tenant-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .tenant-subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 22px;
            color: #0284c7;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-pending {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .meta-table td {
            vertical-align: top;
            width: 50%;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-right: 8px;
        }
        .meta-box.right {
            margin-right: 0;
            margin-left: 8px;
        }
        .meta-heading {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .total-table {
            width: 100%;
            margin-top: 10px;
        }
        .total-table td {
            vertical-align: top;
        }
        .total-box {
            width: 260px;
            float: right;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            padding: 12px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .total-label {
            display: table-cell;
            color: #64748b;
            font-size: 11px;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
            font-size: 11px;
            color: #0f172a;
        }
        .grand-total {
            border-top: 2px solid #0284c7;
            padding-top: 8px;
            margin-top: 8px;
        }
        .grand-total .total-label {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .grand-total .total-value {
            font-size: 15px;
            font-weight: bold;
            color: #0284c7;
        }

        .barcode-box {
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
            background: #ffffff;
            width: 250px;
        }
        .barcode-code {
            font-family: monospace;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #0f172a;
            margin-top: 4px;
        }

        .footer-note {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="tenant-title">{{ $tenant->name ?? 'Venue Partner' }}</h1>
                <p class="tenant-subtitle">{{ $tenant->address ?? 'Pusat Olahraga & Kebugaran' }}</p>
                @if($tenant->phone)
                    <p class="tenant-subtitle">WhatsApp / Telp: {{ $tenant->phone }}</p>
                @endif
            </td>
            <td class="invoice-title" style="vertical-align: middle;">
                <h2>INVOICE</h2>
                <div style="margin-bottom: 6px;">
                    <span style="color: #64748b; font-size: 11px;">No. Reservasi:</span>
                    <strong style="font-size: 12px; font-family: monospace;">#{{ $booking->booking_code }}</strong>
                </div>
                <div>
                    @if($booking->status === 'confirmed' || ($payment && $payment->status === 'paid'))
                        <span class="badge-status badge-paid">LUNAS / TERKONFIRMASI</span>
                    @else
                        <span class="badge-status badge-pending">MENUNGGU PEMBAYARAN</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Data Pemesan & Rincian Jadwal -->
    <table class="meta-table">
        <tr>
            <td>
                <div class="meta-box">
                    <div class="meta-heading">Informasi Pemesan</div>
                    <div style="font-size: 13px; font-weight: bold; color: #0f172a; margin-bottom: 2px;">
                        {{ $booking->customer->name ?? $booking->customer_name }}
                    </div>
                    <div style="color: #475569; font-size: 11px;">
                        No. HP/WA: <strong>{{ $booking->customer->phone ?? $booking->customer_phone ?? '-' }}</strong>
                    </div>
                    @if(!empty($booking->customer->email ?? $booking->customer_email))
                        <div style="color: #475569; font-size: 11px;">
                            Email: {{ $booking->customer->email ?? $booking->customer_email }}
                        </div>
                    @endif
                    <div style="color: #94a3b8; font-size: 10px; margin-top: 6px;">
                        Waktu Pemesanan: {{ $booking->created_at->format('d/m/Y H:i') }} WIB
                    </div>
                </div>
            </td>
            <td>
                <div class="meta-box right">
                    <div class="meta-heading">Detail Lapangan & Jadwal Main</div>
                    <div style="font-size: 13px; font-weight: bold; color: #0284c7; margin-bottom: 2px;">
                        {{ $booking->resource->name ?? 'Lapangan Olahraga' }}
                    </div>
                    <div style="color: #334155; font-size: 11px;">
                        Cabang: <strong>{{ $booking->resource->venue->name ?? $tenant->name }}</strong>
                    </div>
                    <div style="color: #0f172a; font-weight: bold; font-size: 11px; margin-top: 4px;">
                        Tanggal: {{ \Carbon\Carbon::parse($booking->start_at)->translatedFormat('l, d F Y') }}
                    </div>
                    <div style="color: #0284c7; font-weight: bold; font-size: 11px;">
                        Jam Main: {{ \Carbon\Carbon::parse($booking->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_at)->format('H:i') }} WIB
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Biaya -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Deskripsi Layanan</th>
                <th style="width: 15%; text-align: center;">Durasi / Qty</th>
                <th style="width: 15%; text-align: right;">Tarif Satuan</th>
                <th style="width: 20%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $durationHours = max(1, \Carbon\Carbon::parse($booking->start_at)->diffInHours(\Carbon\Carbon::parse($booking->end_at)));
                $courtRate = $durationHours > 0 ? ($booking->subtotal / $durationHours) : $booking->subtotal;
            @endphp
            <tr>
                <td>
                    <strong>Sewa {{ $booking->resource->name ?? 'Lapangan' }}</strong>
                    <div style="color: #64748b; font-size: 10px;">
                        {{ \Carbon\Carbon::parse($booking->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_at)->format('H:i') }} WIB
                    </div>
                </td>
                <td style="text-align: center;">{{ $durationHours }} Jam</td>
                <td style="text-align: right;">Rp {{ number_format($courtRate, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</td>
            </tr>

            @if(!empty($booking->addons) && is_array($booking->addons))
                @foreach($booking->addons as $addonItem)
                    <tr>
                        <td>
                            Sewa Peralatan: <strong>{{ $addonItem['name'] ?? 'Addon' }}</strong>
                        </td>
                        <td style="text-align: center;">{{ $addonItem['qty'] ?? 1 }}x</td>
                        <td style="text-align: right;">Rp {{ number_format($addonItem['price'] ?? 0, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format(($addonItem['price'] ?? 0) * ($addonItem['qty'] ?? 1), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- Total & Kode Check-in -->
    <table class="total-table">
        <tr>
            <td style="width: 45%;">
                <div class="barcode-box">
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: bold;">Kode Tiket Masuk Lapangan</div>
                    <div class="barcode-code">{{ $booking->booking_code }}</div>
                    <div style="font-size: 9px; color: #94a3b8; margin-top: 4px;">Tunjukkan bukti invoice atau sebutkan kode ini kepada petugas lapangan saat tiba di lokasi.</div>
                </div>
            </td>
            <td style="width: 55%;">
                <div class="total-box">
                    <div class="total-row">
                        <span class="total-label">Subtotal Sewa</span>
                        <span class="total-value">Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if($booking->discount_amount > 0)
                        <div class="total-row">
                            <span class="total-label">Diskon Promo</span>
                            <span class="total-value" style="color: #dc2626;">- Rp {{ number_format($booking->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if($booking->addon_total > 0)
                        <div class="total-row">
                            <span class="total-label">Tambahan Sewa Alat</span>
                            <span class="total-value">Rp {{ number_format($booking->addon_total, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="total-row grand-total">
                        <span class="total-label">TOTAL AKHIR</span>
                        <span class="total-value">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($payment)
                        <div style="margin-top: 10px; font-size: 10px; color: #64748b; text-align: right;">
                            Metode: <strong style="text-transform: uppercase;">{{ $payment->payment_method ?? 'MIDTRANS ONLINE' }}</strong><br>
                            Status: <strong style="color: {{ $payment->status === 'paid' ? '#16a34a' : '#d97706' }};">{{ strtoupper($payment->status) }}</strong>
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="footer-note">
        Dokumen ini adalah bukti transaksi resmi yang diterbitkan secara elektronik oleh <strong>{{ $tenant->name ?? 'Venue Partner' }}</strong>.<br>
        Harap hadir 10 menit sebelum jadwal sesi dimulai. Terima kasih atas kepercayaan Anda berolahraga bersama kami!
    </div>

</body>
</html>
