# Golden Strict Rules for Laravel 13 SaaS Booking System

1. **ZERO QUERY DI CONTROLLER (STRICT RULE):**
   - Controller HANYA bertugas sebagai HTTP Traffic Orchestrator (validasi input, panggil static model/service, return redirect/view).
   - DILARANG KERAS menulis query builder (`Model::where`, `Model::find`, `DB::table`, `DB::raw`, dsb) di dalam Controller.
   - Semua operasi database, query logic, dan transaksi WAJIB dienkapsulasi di dalam Model (atau Service).

2. **MODEL LAYER (RAW QUERY FIRST & ANTI N+1 QUERY):**
   - Utamakan penggunaan **Raw Query / DB Query Builder direct query** (`\Illuminate\Support\Facades\DB`) untuk operasi data, joins, pengecekan ketersediaan slot (availability check), dan permission.
   - Prioritas kecepatan: Hindari query Eloquent yang berat dan lazy-loading.
   - Dilarang `SELECT *`. Selalu pilih kolom spesifik (`select('id', 'name', ...)`).
   - Relasi wajib eager loading dengan kolom spesifik (`with(['role:id,name,slug'])`) untuk mencegah N+1 problem.
   - Paginasi wajib dibuatkan method static di Model dengan indexed search.
   - Gunakan `DB::transaction()` dan pessimistic locking (`lockForUpdate()`) untuk transaksi kritis (booking slot, pembayaran).

3. **STORAGE & MEDIA UPLOAD (HYBRID LOCAL & MINIO):**
   - Sistem wajib mendukung dual storage (MinIO atau Local) sesuai konfigurasi `.env` (`MINIO_ACTIVE`, `FILESYSTEM_DISK`).
   - Wajib gunakan helper resmi:
     - `minio_upload($file, $folder)` / `storage_upload($file, $folder)`
     - `minio_replace($oldPath, $newFile, $folder)`
     - `minio_delete($path)` / `storage_delete($path)`
     - `minio_url($path)` / `storage_url($path)`
     - `minio_avatar($path, $name)`
   - Jangan pernah hardcode `asset('storage/...')` atau manipulasi filesystem terpisah di controller/view.

4. **SECURITY & MULTI-TENANCY:**
   - Setiap entitas data tenant wajib memiliki `tenant_id`.
   - Pastikan isolasi data tenant terjaga ketat tanpa kebocoran data antar tenant (zero cross-tenant leakage).
   - Controller wajib mengimplementasikan `implements HasMiddleware` dan `use HasAutoPermissions;`.
