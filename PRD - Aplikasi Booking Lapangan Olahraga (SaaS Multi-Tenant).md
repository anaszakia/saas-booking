# PRD: Aplikasi Booking Lapangan Olahraga (SaaS Multi-Tenant)

Draf v0.1, 9 Oktober 2026

## 1. Ringkasan

Aplikasi web berbasis SaaS yang memungkinkan pengelola tempat olahraga (padel, mini soccer, billiard, dan sejenisnya) menerima dan mengelola booking secara online. Setiap pengelola menjadi satu **tenant** dengan data terpisah, branding sendiri, dan akun payment gateway sendiri. Pelanggan akhir dapat melihat ketersediaan, memesan, membayar, dan membatalkan booking tanpa perlu menghubungi pengelola secara manual.

### Tujuan produk

- Menggantikan pencatatan booking manual (WhatsApp, buku, spreadsheet) dengan sistem yang bebas bentrok jadwal.
- Memberi tenant dashboard sederhana untuk jadwal, pendapatan, dan pelanggan.
- Menyediakan satu platform yang bisa melayani berbagai jenis olahraga tanpa mengubah kode.
- Menghasilkan pendapatan berulang melalui paket langganan tenant.

### Di luar tujuan (tahap awal)

- Aplikasi mobile native (cukup web responsif).
- Marketplace publik lintas tenant untuk mencari lapangan.
- Pembagian dana atau pencairan oleh platform (dana langsung masuk ke akun gateway tenant).
- Sistem keanggotaan, turnamen, dan matchmaking.

## 2. Latar Belakang dan Masalah

Pengelola tempat olahraga skala kecil dan menengah umumnya menerima booking lewat chat dan telepon. Masalah yang sering muncul:

- Booking ganda karena pencatatan tidak sinkron.
- Pelanggan harus bertanya jadwal kosong berulang kali.
- Konfirmasi pembayaran dilakukan manual lewat bukti transfer.
- Tidak ada rekap pendapatan dan okupansi yang rapi.
- Pembatalan dan pengembalian dana tidak punya aturan yang jelas.

## 3. Pengguna Sasaran

| Peran | Deskripsi | Kebutuhan utama |
| --- | --- | --- |
| Super Admin (platform) | Tim pengelola SaaS | Mengelola tenant, paket langganan, dan pemantauan sistem |
| Owner tenant | Pemilik tempat olahraga | Laporan pendapatan, pengaturan harga, kebijakan, pembayaran |
| Staf tenant | Kasir atau penjaga | Input booking walk-in, cek jadwal, konfirmasi pembayaran manual |
| Pelanggan | Pemain atau penyewa | Cek slot, booking cepat, bayar online, batalkan atau jadwal ulang |

### Contoh tenant awal

- **Aero Padel**: 3 lapangan padel.
- **Amedsport Center**: 5 lapangan mini soccer.
- **Didik Billiard**: 10 meja billiard.

Ketiga contoh ini menjadi acuan bahwa sistem harus mendukung jenis resource, jumlah resource, durasi slot, dan harga yang berbeda-beda.

## 4. Konsep Multi-Tenant

- **Model data:** satu database bersama dengan kolom `tenant_id` pada semua tabel milik tenant (shared schema).
- **Isolasi:** global scope otomatis memfilter semua query berdasarkan tenant aktif. Tidak boleh ada query tenant tanpa scope.
- **Identifikasi tenant:** subdomain per tenant, misalnya `aero-padel.namaapp.com`. Custom domain menjadi fitur lanjutan.
- **Resource generik:** lapangan, meja, atau ruangan dimodelkan sebagai `resource` dengan `resource_type`, sehingga satu mesin booking melayani semua jenis olahraga.
- **Konfigurasi per tenant:** jam operasional, durasi slot, harga, kebijakan pembatalan, dan pengaturan pembayaran disimpan sebagai data, bukan hardcode.

## 5. Kebutuhan Fungsional

### 5.1 Manajemen tenant (Super Admin)

- F-01: Membuat, mengaktifkan, menonaktifkan, dan menangguhkan tenant.
- F-02: Mengelola paket langganan dan batasan per paket (jumlah resource, jumlah staf, fitur).
- F-03: Melihat ringkasan penggunaan dan status langganan tiap tenant.
- F-04: Impersonasi akun owner untuk bantuan dukungan, dengan pencatatan audit.

### 5.2 Pengaturan tenant (Owner)

- F-10: Profil tempat: nama, logo, alamat, kontak, zona waktu.
- F-11: Mengelola venue atau cabang (opsional).
- F-12: Mengelola tipe resource dan resource: nama, tipe, kapasitas, status aktif, atribut tambahan (JSON).
- F-13: Jam operasional per venue atau resource, termasuk hari libur dan penutupan khusus.
- F-14: Aturan durasi per tipe resource: durasi slot, durasi minimum, dan durasi maksimum.
- F-15: Aturan harga: tarif per resource atau tipe, dengan variasi weekday, weekend, dan jam prime.
- F-16: Add-on opsional (sewa raket, bola, rompi, wasit) dengan harga sendiri.
- F-17: Mengelola akun staf dan hak akses.

### 5.3 Booking

- F-20: Kalender ketersediaan per resource (tampilan harian dan mingguan).
- F-21: Booking oleh pelanggan melalui halaman publik tenant: pilih tipe, tanggal, resource, dan slot.
- F-22: Booking walk-in atau manual oleh staf dari dashboard.
- F-23: Pencegahan booking ganda dengan penguncian transaksi dan batasan unik pada database.
- F-24: Penahanan slot sementara (hold) selama proses pembayaran, default 15 menit, lalu dilepas otomatis jika tidak dibayar.
- F-25: Status booking: `pending`, `confirmed`, `completed`, `cancelled`, `no_show`.
- F-26: Booking berulang (misalnya setiap Selasa malam) sebagai fitur tahap lanjutan.
- F-27: Jadwal ulang booking sesuai kebijakan tenant.
- F-28: Kode booking dan halaman detail yang bisa dibuka pelanggan tanpa login lewat tautan unik.

### 5.4 Pelanggan

- F-30: Pendaftaran ringan: nama, nomor WhatsApp, dan email opsional. Login lewat OTP atau tautan (tanpa kata sandi) lebih diutamakan.
- F-31: Riwayat booking pelanggan.
- F-32: Data pelanggan disimpan per tenant. Satu orang yang sama di dua tenant dianggap dua pelanggan terpisah.

### 5.5 Notifikasi

- F-40: Notifikasi WhatsApp dan email untuk: booking dibuat, pembayaran berhasil, pengingat sebelum main, pembatalan, dan refund.
- F-41: Notifikasi untuk owner atau staf saat ada booking baru atau pembatalan.
- F-42: Template pesan yang dapat disesuaikan tenant (tahap lanjutan).

### 5.6 Laporan

- F-50: Ringkasan pendapatan harian, mingguan, dan bulanan.
- F-51: Tingkat okupansi per resource dan jam.
- F-52: Daftar booking dengan filter dan ekspor CSV.
- F-53: Laporan pembatalan dan refund.

## 6. Pembayaran

### 6.1 Model

Setiap tenant memakai **akun payment gateway miliknya sendiri**. Dana masuk langsung ke akun tenant. Platform tidak memegang dana pelanggan.

### 6.2 Kebutuhan

- F-60: Tenant memilih provider (misalnya Midtrans atau Xendit) dan mengisi kunci API di pengaturan.
- F-61: Kunci API disimpan terenkripsi dan ditampilkan tersamar di antarmuka.
- F-62: Tes koneksi ke gateway sebelum pembayaran online diaktifkan, dengan penanda `verified_at`.
- F-63: URL webhook per tenant dan provider ditampilkan di dashboard untuk didaftarkan oleh tenant di panel gateway.
- F-64: Mode sandbox dan production per tenant.
- F-65: Abstraksi `PaymentGateway` agar penambahan provider tidak mengubah logika booking.
- F-66: Mode pembayaran manual (transfer dan unggah bukti, konfirmasi oleh admin) untuk tenant tanpa akun gateway.
- F-67: Opsi pembayaran DP atau lunas, diatur per tenant.

### 6.3 Keamanan pembayaran

- Verifikasi signature pada setiap webhook sebelum mengubah status.
- Idempotensi: event yang sama tidak diproses dua kali (batasan unik pada `external_id` dan jenis event).
- Nominal webhook dicocokkan dengan total booking.
- Status lunas hanya berasal dari webhook atau query ke API gateway, tidak dari redirect di sisi klien.
- Kunci API tidak boleh muncul di log, error tracker, atau respons API.
- Pembayaran memakai halaman hosted atau metode QRIS dan virtual account, sehingga data kartu tidak melewati server platform.
- Seluruh event pembayaran dicatat dalam audit log.
- Rate limit pada endpoint webhook dan tes koneksi.

## 7. Pembatalan dan Refund

### 7.1 Kebijakan per tenant

Tenant mengatur kebijakan pembatalan berupa tahapan waktu dan persentase refund, misalnya:

| Waktu pembatalan sebelum main | Refund |
| --- | --- |
| 24 jam atau lebih | 100% |
| 6 sampai 24 jam | 50% |
| Kurang dari 6 jam | 0% |

Ketentuan tambahan:

- Dibatalkan oleh tenant (hujan, perawatan, gangguan listrik): refund penuh atau tawaran jadwal ulang.
- No-show: tidak ada refund.
- Pembayaran DP: refund dihitung dari nominal yang sudah dibayar.
- Booking yang belum dibayar cukup dibatalkan tanpa refund.

### 7.2 Kebutuhan

- F-70: Sistem menghitung nominal refund otomatis dan menampilkannya kepada pelanggan sebelum konfirmasi pembatalan.
- F-71: Saat pembatalan dikonfirmasi, status booking menjadi `cancelled` dan slot dilepas dalam satu transaksi database.
- F-72: Record refund dibuat terpisah dari pembayaran (mendukung refund parsial dan riwayat).
- F-73: Refund via API gateway dijalankan oleh queue job dengan percobaan ulang, memakai kunci API tenant.
- F-74: Untuk metode pembayaran yang tidak mendukung refund otomatis (umumnya virtual account dan transfer bank), refund masuk antrean manual di dashboard tenant. Pelanggan mengisi rekening tujuan, admin mentransfer dan mengunggah bukti.
- F-75: Setiap implementasi gateway menyediakan `supportsRefund(metode)` agar sistem memilih jalur otomatis atau manual.
- F-76: Alternatif refund: jadwal ulang tanpa biaya atau kredit/voucher di tenant yang sama.
- F-77: Status refund: `pending`, `processing`, `succeeded`, `failed`, dengan notifikasi ke pelanggan.
- F-78: Total refund tidak boleh melebihi nominal yang dibayar. Refund besar atau manual membutuhkan persetujuan peran tertentu dan dicatat di audit log.
- F-79: Kebijakan pembatalan ditampilkan di halaman booking sebelum pembayaran.

### 7.3 Catatan

Dukungan refund per metode pembayaran berbeda antar gateway dan dapat berubah. Aturan rinci harus diverifikasi pada dokumentasi provider yang didukung sebelum implementasi.

## 8. Peran dan Hak Akses

| Fitur | Super Admin | Owner | Staf | Pelanggan |
| --- | --- | --- | --- | --- |
| Kelola tenant dan paket | Ya | Tidak | Tidak | Tidak |
| Pengaturan tenant dan pembayaran | Tidak | Ya | Tidak | Tidak |
| Kelola resource dan harga | Tidak | Ya | Terbatas | Tidak |
| Input booking walk-in | Tidak | Ya | Ya | Tidak |
| Konfirmasi pembayaran manual | Tidak | Ya | Ya | Tidak |
| Setujui refund | Tidak | Ya | Opsional | Tidak |
| Laporan pendapatan | Tidak | Ya | Terbatas | Tidak |
| Booking dan batalkan sendiri | Tidak | Tidak | Tidak | Ya |

## 9. Kebutuhan Non-Fungsional

- **Keamanan data tenant:** tidak ada kebocoran data lintas tenant. Dibutuhkan pengujian otomatis untuk isolasi tenant.
- **Konsistensi booking:** tidak boleh terjadi booking ganda pada resource dan rentang waktu yang sama, termasuk saat banyak pengguna memesan bersamaan.
- **Kinerja:** halaman ketersediaan dimuat kurang dari 2 detik pada kondisi normal. Proses booking selesai kurang dari 3 detik di luar waktu tunggu gateway.
- **Ketersediaan:** target 99,5% pada tahap awal, dengan cadangan database harian.
- **Zona waktu:** waktu disimpan dalam UTC dan ditampilkan sesuai zona waktu tenant (WIB, WITA, WIT).
- **Responsif:** semua halaman pelanggan dapat dipakai nyaman di ponsel.
- **Observabilitas:** pencatatan log terstruktur, pemantauan error, dan peringatan untuk kegagalan webhook dan job refund.
- **Privasi:** data pribadi pelanggan (nama, nomor telepon) dilindungi dan disimpan sesuai ketentuan perlindungan data pribadi yang berlaku di Indonesia.
- **Audit:** aksi sensitif (perubahan kunci pembayaran, refund, perubahan harga, impersonasi) dicatat dengan pelaku dan waktu.
- **Lokalisasi:** antarmuka berbahasa Indonesia, mata uang Rupiah.

## 10. Arsitektur dan Teknologi

| Lapisan | Pilihan |
| --- | --- |
| Backend | Laravel (versi stabil terbaru) |
| Frontend | Blade dengan Livewire dan Alpine.js (alternatif: Inertia dengan Vue) |
| Database | MySQL, satu database dengan `tenant_id` |
| Cache dan antrean | Redis |
| Penjadwalan | Laravel Scheduler (pelepasan hold, pengingat, rekonsiliasi) |
| Pembayaran | Midtrans atau Xendit, dengan abstraksi gateway |
| Notifikasi | Penyedia WhatsApp (Fonnte, Wablas, atau WhatsApp Cloud API) dan email |
| Multi-tenancy | Trait `BelongsToTenant` dengan global scope, atau paket `spatie/laravel-multitenancy` atau `stancl/tenancy` |
| Deploy | Docker Compose pada satu VPS, dengan CI/CD |

## 11. Model Data Inti

- `tenants`: identitas, subdomain, paket, status, zona waktu.
- `users`: akun owner dan staf, terkait tenant dan peran.
- `venues`: cabang atau lokasi tenant.
- `resource_types`: tipe bawaan sistem atau buatan tenant, beserta aturan durasi.
- `resources`: lapangan, meja, atau ruangan, dengan atribut JSON.
- `operating_hours` dan `closures`: jam buka dan penutupan khusus.
- `pricing_rules`: tarif berdasarkan resource atau tipe, hari, dan jam.
- `customers`: pelanggan per tenant.
- `bookings`: resource, pelanggan, `start_at`, `end_at`, status, total harga.
- `booking_addons`: add-on yang dipilih pada booking.
- `payments` dan `tenant_payment_settings`: transaksi dan konfigurasi gateway terenkripsi per tenant.
- `cancellation_policies`: aturan tahapan refund per tenant atau tipe resource.
- `refunds`: nominal, alasan, metode, status, `external_id`, bukti, penyetuju.
- `audit_logs`: catatan aksi sensitif.
- `subscriptions` dan `plans`: langganan tenant ke platform.

## 12. Alur Pengguna Utama

### 12.1 Booking oleh pelanggan

1. Pelanggan membuka halaman publik tenant dan memilih tipe resource serta tanggal.
2. Sistem menampilkan slot kosong dan harga.
3. Pelanggan memilih slot dan add-on, lalu mengisi data kontak.
4. Sistem menahan slot selama 15 menit dan membuat tagihan di gateway tenant.
5. Pelanggan membayar. Webhook diverifikasi, booking menjadi `confirmed`, dan notifikasi dikirim.
6. Jika tidak dibayar sampai batas waktu, hold dilepas dan booking menjadi `cancelled`.

### 12.2 Pembatalan dan refund

1. Pelanggan membuka detail booking dan memilih batalkan.
2. Sistem menampilkan nominal refund sesuai kebijakan dan meminta konfirmasi.
3. Booking dibatalkan dan slot dilepas.
4. Refund diproses otomatis lewat gateway atau masuk antrean manual.
5. Pelanggan menerima notifikasi pada setiap perubahan status refund.

### 12.3 Onboarding tenant

1. Super Admin atau pendaftaran mandiri membuat tenant dan akun owner.
2. Owner mengisi profil, resource, jam operasional, dan harga.
3. Owner memilih provider pembayaran, mengisi kunci, lalu menjalankan tes koneksi.
4. Owner mendaftarkan URL webhook di panel gateway dan mencoba transaksi sandbox.
5. Pembayaran online diaktifkan. Sebelum itu tenant hanya menerima booking manual.

## 13. Model Bisnis

- Langganan bulanan atau tahunan per tenant, dengan paket bertingkat berdasarkan jumlah resource, staf, dan fitur.
- Masa uji coba gratis untuk tenant baru.
- Biaya transaksi gateway ditanggung sesuai pengaturan tenant (dibebankan ke tenant atau ke pelanggan sebagai biaya layanan).
- Penagihan langganan tenant pada tahap awal dapat dilakukan manual atau lewat gateway milik platform.

## 14. Metrik Keberhasilan

- Jumlah tenant aktif dan tingkat retensi bulanan.
- Jumlah booking per tenant per bulan.
- Persentase booking yang diselesaikan dengan pembayaran online.
- Jumlah insiden booking ganda (target: nol).
- Waktu rata-rata onboarding tenant sampai booking pertama.
- Persentase refund yang selesai tanpa intervensi manual.
- Tingkat kegagalan webhook dan job refund.

## 15. Rencana Rilis

### Fase 1: MVP

- Tenant, autentikasi, dan peran.
- Pengelolaan venue, resource, jam operasional, dan harga.
- Kalender ketersediaan dan booking manual oleh staf.
- Halaman booking publik per subdomain.
- Pembayaran manual (transfer dan konfirmasi admin).

### Fase 2: Pembayaran online

- Abstraksi gateway dan integrasi satu provider.
- Pengaturan pembayaran per tenant, tes koneksi, dan webhook aman.
- Hold slot dan pelepasan otomatis.
- Notifikasi WhatsApp dan email.

### Fase 3: Pembatalan dan laporan

- Kebijakan pembatalan, refund otomatis dan manual, jadwal ulang, dan voucher.
- Laporan pendapatan, okupansi, dan ekspor.
- Audit log.

### Fase 4: Skala dan monetisasi

- Paket langganan dan batasan per paket.
- Provider gateway tambahan.
- Custom domain, booking berulang, template notifikasi, dan mode sesi (open table) untuk billiard.

## 16. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Kebocoran data antar tenant | Sangat tinggi | Global scope wajib, pengujian isolasi otomatis, tinjauan kode pada query mentah |
| Booking ganda | Tinggi | Penguncian transaksi, batasan unik, uji beban konkurensi |
| Kunci API tenant salah atau kedaluwarsa | Sedang | Tes koneksi, status pembayaran bermasalah di dashboard, notifikasi ke owner |
| Webhook gagal atau terlambat | Tinggi | Idempotensi, polling status sebagai cadangan, rekonsiliasi harian |
| Tenant kesulitan membuat akun gateway | Sedang | Mode pembayaran manual sebagai alternatif |
| Refund tidak bisa otomatis untuk metode tertentu | Sedang | Antrean refund manual dan deteksi dukungan per metode |
| Perbedaan aturan bisnis antar jenis olahraga | Sedang | Semua aturan sebagai konfigurasi, bukan hardcode |
| Ketergantungan pada penyedia WhatsApp pihak ketiga | Sedang | Abstraksi notifikasi dan cadangan email |

## 17. Pertanyaan Terbuka

- Nama produk dan domain utama.
- Provider gateway mana yang didukung lebih dulu (Midtrans, Xendit, atau lainnya).
- Penyedia WhatsApp yang dipakai dan model biayanya.
- Apakah billiard membutuhkan mode sesi (bayar sesuai durasi aktual) pada fase awal.
- Harga dan struktur paket langganan.
- Apakah pendaftaran tenant dilakukan mandiri atau melalui persetujuan Super Admin.
- Kebutuhan kepatuhan hukum terkait perlindungan data pribadi dan pembayaran yang perlu ditinjau oleh konsultan hukum.
