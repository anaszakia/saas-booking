<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Uid\Ulid;

return new class extends Migration
{
    /**
     * Daftar tabel entitas yang akan ditambahkan kolom ulid
     */
    protected array $tables = [
        'tenants',
        'users',
        'roles',
        'permissions',
        'menus',
        'venues',
        'resource_types',
        'resources',
        'operating_hours',
        'closures',
        'pricing_rules',
        'customers',
        'addons',
        'bookings',
        'booking_addons',
        'tenant_payment_settings',
        'payments',
        'cancellation_policies',
        'refunds',
        'plans',
        'subscriptions',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'ulid')) {
                // 1. Tambah kolom ulid nullable setelah id
                Schema::table($tableName, function (Blueprint $table) {
                    $table->char('ulid', 26)->nullable()->after('id');
                });

                // 2. Generate ULID unik untuk data yang sudah ada
                $rows = DB::table($tableName)->select('id')->get();
                foreach ($rows as $row) {
                    DB::table($tableName)
                        ->where('id', $row->id)
                        ->update(['ulid' => (string) new Ulid()]);
                }

                // 3. Ubah kolom menjadi unique dan not null
                Schema::table($tableName, function (Blueprint $table) {
                    $table->char('ulid', 26)->nullable(false)->change();
                    $table->unique('ulid');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'ulid')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropUnique([$tableName . '_ulid_unique']);
                    $table->dropColumn('ulid');
                });
            }
        }
    }
};
