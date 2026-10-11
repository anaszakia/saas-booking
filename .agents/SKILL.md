---
name: laravel-13-saas-golden-standards
description: Strict architecture, security, and performance standards for Laravel 13 SaaS applications. Enforces zero-queries-in-controller, raw query/query builder optimization in models, absolute N+1 prevention, hybrid storage (Local/MinIO), multi-tenant isolation, and high-performance patterns.
---

# 🏛️ Core Architecture & Foundation Guidelines (Laravel 13 SaaS)

File panduan ini adalah **standar pondasi mutlak (Strict Golden Standards)** untuk arsitektur project ini dan seluruh project turunan. Setiap AI Agent maupun developer **DILARANG KERAS** melanggar aturan ini.

---

## 🛑 1. ZERO QUERY DI CONTROLLER (STRICT RULE)

**Controller HANYA bertugas sebagai HTTP Traffic Orchestrator:**
- Menerima HTTP Request.
- Memvalidasi input (`$request->validate(...)` atau `FormRequest`).
- Memanggil method statis / service layer di Model atau Service.
- Menangani flash message dan HTTP Response / Redirect / View.

### ❌ DILARANG KERAS di Controller:
- `Model::where(...)`, `Model::find(...)`, `Model::select(...)`, `Model::join(...)`.
- `DB::table(...)`, `DB::raw(...)`, `DB::select(...)`.
- Looping query atau kalkulasi bisnis berat.
- Melakukan chaining query query builder di dalam method controller.

### ✅ HANYA DIPERBOLEHKAN di Controller:
- Memanggil method representatif yang sudah dienkapsulasi rapi di Model, contoh:
  - `User::getPaginatedUsers($request->query('search'), 10)`
  - `Booking::checkAvailability($tenantId, $resourceId, $startAt, $endAt)`
  - `Resource::createResource($validatedData)`
  - `Booking::cancelBookingWithRefund($bookingId, $reason)`

---

## ⚡ 2. MODEL LAYER: RAW QUERY FIRST & ANTI N+1 QUERY (KECEPATAN PRIORITAS #1)

Kecepatan respon sistem di bawah beban jutaan data adalah **prioritas tertinggi**. Eloquent lazy-loading dan relasi bersarang yang tidak terkontrol adalah penyebab utama query lemot (N+1 Problem).

### Aturan Query Database:
1. **Raw Query / DB Query Builder First (`\Illuminate\Support\Facades\DB`):**
   - Gunakan `DB::table(...)` dengan `join` eksplisit untuk operasi lookup cepat, agregasi, availability checking, dan permission checking.
   - Gunakan prepared statements / parameterized bindings (`?` atau named bindings) untuk mencegah SQL Injection.
2. **Anti N+1 & Lean Column Selection:**
   - **DILARANG** melakukan `SELECT *` pada tabel besar. Selalu tentukan kolom secara eksplisit: `select('id', 'name', 'status', ...)` guna meminimalkan alokasi memori dan I/O database.
   - **DILARANG** mengakses properti relasi di loop Blade/PHP tanpa eager load atau explicit join. Jika menggunakan method relasi Eloquent, wajib menyertakan kolom spesifik:
     ```php
     // CONTOH BENAR:
     static::select('id', 'name', 'email', 'avatar', 'role_id')
         ->with(['role:id,name,slug'])
     ```
3. **Paginasi Statis Terindeks:**
   - Semua listing data paginasi wajib memiliki method statis di Model (seperti `getPaginatedList(...)`), memilih kolom spesifik, dan mencari kolom berindeks (`name`, `email`, `code`, dsb).
4. **Pessimistic Locking untuk Transaksi Kritis:**
   - Operasi yang rawan *race condition* (booking slot, pembayaran, hold tiket) wajib dibungkus `DB::transaction()` dan menggunakan `lockForUpdate()` agar tidak pernah terjadi double booking.

---

## 🛡️ 3. KEAMANAN & MULTI-TENANCY TINGKAT TINGGI

1. **Isolasi Tenant Ketat (Zero Cross-Tenant Leakage):**
   - Setiap tabel tenant wajib memiliki kolom `tenant_id` terindeks.
   - Setiap query model wajib menyertakan filter `tenant_id` (atau melalui trait `BelongsToTenant` / Tenant Global Scope).
   - Jangan pernah mempercayai `tenant_id` dari input form publik (`$request->tenant_id`). Wajib diambil dari session/subdomain resolver yang tervalidasi di backend.
2. **Otorisasi Otomatis Laravel 13 (`HasMiddleware`):**
   - Setiap Controller wajib mengimplementasikan `Illuminate\Routing\Controllers\HasMiddleware` dan menggunakan trait `App\Traits\HasAutoPermissions`.
   - Middleware memetakan rute aksi secara otomatis ke slug permission:
     - `index`, `show` ➔ `{slug}.view`
     - `create`, `store` ➔ `{slug}.create`
     - `edit`, `update` ➔ `{slug}.edit`
     - `destroy` ➔ `{slug}.delete`
3. **Idempotensi & Sanitasi:**
   - Webhook pembayaran dan API callback wajib memverifikasi signature digital dan menerapkan pengecekan idempotensi (`external_id` / `transaction_id`) sebelum memproses payload.
   - Kunci API (Midtrans, Xendit, WhatsApp Gateway) wajib dienkripsi (`Crypt::encryptString`) di database dan tidak boleh tampil polos di UI/API response.

---

## 🗄️ 4. MULTI-STORAGE COMPATIBLE (LOCAL & MINIO DUAL SYSTEM)

Sistem wajib berjalan mulus baik saat storage lokal digunakan untuk development maupun MinIO / S3 untuk production, cukup dengan mengubah `.env`:
- `MINIO_ACTIVE=false` & `FILESYSTEM_DISK=public` ➔ File disimpan di disk lokal `storage/app/public`.
- `MINIO_ACTIVE=true` & `FILESYSTEM_DISK=minio` ➔ File disimpan di MinIO / S3 bucket.

### Aturan Manipulasi File:
**DILARANG KERAS** memanggil `asset('storage/...')` atau `Storage::disk('public')->...` secara mentah di Controller/View.
Wajib selalu menggunakan helper serbaguna dari `app/Helpers/helpers.php`:
- **Upload File:** `minio_upload($file, $folder)` / `storage_upload($file, $folder)`
- **Replace File (Upload baru + hapus lama):** `minio_replace($oldPath, $newFile, $folder)`
- **Hapus File:** `minio_delete($path)` / `storage_delete($path)`
- **Ambil URL File Publik:** `minio_url($path)` / `storage_url($path)`
- **Avatar Inisial Fallback:** `minio_avatar($path, $name)` / `storage_avatar($path, $name)`

---

## 📐 5. CONTOH GOLDEN PATTERN LENGKAP

### A. Golden Controller Pattern (`app/Http/Controllers/UserController.php`)
```php
<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\MinioService;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class UserController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public function __construct(protected MinioService $minio) {}

    public function index(Request $request)
    {
        // NO RAW/ELOQUENT QUERY HERE! Delegasikan ke Model
        $users = User::getPaginatedUsers($request->query('search'), 10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::getAllOrdered();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role_id'  => 'nullable|exists:roles,id',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = minio_upload($request->file('avatar'), 'avatars');
        }

        User::createUserWithRole($request->only('name', 'email', 'password', 'phone', 'address') + [
            'avatar'  => $avatarPath,
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $roles = Role::getAllOrdered();
        $user = User::findOptimized($user->id);

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'role_id'  => 'nullable|exists:roles,id',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $request->only('name', 'email', 'phone', 'address', 'role_id');

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = minio_replace($user->avatar, $request->file('avatar'), 'avatars');
        }

        if ($request->has('remove_avatar') && $user->avatar) {
            minio_delete($user->avatar);
            $data['avatar'] = null;
        }

        User::updateUserWithRole($user, $data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroy(User $user)
    {
        if ($user->id === session('user_id')) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak bisa menghapus akun yang sedang login!');
        }

        if ($user->avatar) {
            minio_delete($user->avatar);
        }

        User::deleteUserWithRelations($user);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }
}
```

---

### B. Golden Model Pattern (`app/Models/User.php`)
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'avatar', 'phone', 'address', 'role_id'];
    protected $hidden = ['password', 'remember_token'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Direct Raw JOIN Query untuk verifikasi permission (ultra fast)
     */
    public function hasPermission(string $slug): bool
    {
        return DB::table('user_role')
            ->join('role_permission', 'role_permission.role_id', '=', 'user_role.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
            ->where('user_role.user_id', $this->id)
            ->where('permissions.slug', $slug)
            ->exists();
    }

    /**
     * Find single record dengan lean eager load spesifik kolom
     */
    public static function findOptimized(int|string $id): ?self
    {
        return static::select('id', 'name', 'email', 'phone', 'address', 'avatar', 'role_id', 'created_at')
            ->with(['role:id,name,slug', 'roles:id,name,slug'])
            ->find($id);
    }

    /**
     * Listing terpaginasi: Anti N+1 & lean memory footprint
     */
    public static function getPaginatedUsers(?string $search = null, int $perPage = 10)
    {
        $query = static::select('users.id', 'users.name', 'users.email', 'users.phone', 'users.avatar', 'users.role_id', 'users.created_at')
            ->with(['role:id,name,slug', 'roles:id,name,slug']);

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('users.name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Enkapsulasi penyimpanan user & sinkronisasi role di Model/Transaction
     */
    public static function createUserWithRole(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $user = static::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id'  => $data['role_id'] ?? null,
                'phone'    => $data['phone'] ?? null,
                'address'  => $data['address'] ?? null,
                'avatar'   => $data['avatar'] ?? null,
            ]);

            if (!empty($data['role_id'])) {
                $user->roles()->sync([$data['role_id']]);
            }

            return $user;
        });
    }

    public static function updateUserWithRole(self $user, array $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            if (!empty($data['role_id'])) {
                $user->roles()->sync([$data['role_id']]);
            } else {
                $user->roles()->detach();
            }

            return true;
        });
    }

    public static function deleteUserWithRelations(self $user): bool
    {
        return DB::transaction(function () use ($user) {
            $user->roles()->detach();
            return $user->delete();
        });
    }

    public function getAvatarUrlAttribute(): string
    {
        return minio_avatar($this->avatar, $this->name);
    }
}
```

---

### C. Golden Blade Views Pattern (`resources/views/admin/users/`)
1. **`index.blade.php`**:
   - Header halaman lengkap dengan Breadcrumb navigasi.
   - Tombol tambah dibungkus permission check: `@if(can('users.create'))`.
   - Filter bar pencarian GET `form` dengan auto-reset jika terdapat query string.
   - Tabel responsive dengan pagination bawaan `$users->links()`.
   - Action dropdown dengan modal konfirmasi hapus (sweetalert atau modal bootstrap).
2. **`create.blade.php` & `edit.blade.php`**:
   - Menampilkan preview avatar/gambar saat ini jika ada.
   - Opsi `remove_avatar` / `remove_image` checkbox pada form update.
   - Validasi error input form standar `@error('field') <div class="invalid-feedback">{{ $message }}</div> @enderror`.

---

## 📋 CHECKLIST SEBELUM MENYELESAIKAN FITUR APAPUN
- [ ] Apakah ada query database di Controller? **Jika ADA, REFACTOR SEGERA ke Model!**
- [ ] Apakah ada potensi N+1 query pada looping view/data? **Wajib eager load kolom spesifik atau gunakan direct JOIN!**
- [ ] Apakah query menggunakan `SELECT *`? **Ganti dengan `SELECT('id', ...)` kolom yang hanya dibutuhkan!**
- [ ] Apakah upload file menggunakan helper `minio_upload` / `minio_replace` / `minio_delete`?
- [ ] Apakah Controller menggunakan `HasMiddleware` dan `HasAutoPermissions`?
- [ ] Apakah ada filter `tenant_id` untuk menjaga keamanan data tenant?
- [ ] Apakah transaksi multi-tabel dibungkus `DB::transaction()`?
