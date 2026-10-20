<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'Lihat Dashboard', 'slug' => 'dashboard.view'],

            // Users
            ['name' => 'Lihat User',   'slug' => 'users.view'],
            ['name' => 'Buat User',    'slug' => 'users.create'],
            ['name' => 'Edit User',    'slug' => 'users.edit'],
            ['name' => 'Hapus User',   'slug' => 'users.delete'],

            // Roles
            ['name' => 'Lihat Role',   'slug' => 'roles.view'],
            ['name' => 'Buat Role',    'slug' => 'roles.create'],
            ['name' => 'Edit Role',    'slug' => 'roles.edit'],
            ['name' => 'Hapus Role',   'slug' => 'roles.delete'],

            // Permissions
            ['name' => 'Lihat Permission', 'slug' => 'permissions.view'],
            ['name' => 'Buat Permission',  'slug' => 'permissions.create'],
            ['name' => 'Edit Permission',  'slug' => 'permissions.edit'],
            ['name' => 'Hapus Permission', 'slug' => 'permissions.delete'],

            // Menus
            ['name' => 'Lihat Menu',   'slug' => 'menus.view'],
            ['name' => 'Buat Menu',    'slug' => 'menus.create'],
            ['name' => 'Edit Menu',    'slug' => 'menus.edit'],
            ['name' => 'Hapus Menu',   'slug' => 'menus.delete'],

            // Venues
            ['name' => 'Lihat Venue',   'slug' => 'venues.view'],
            ['name' => 'Buat Venue',    'slug' => 'venues.create'],
            ['name' => 'Edit Venue',    'slug' => 'venues.edit'],
            ['name' => 'Hapus Venue',   'slug' => 'venues.delete'],

            // Resource Types (Jenis Olahraga)
            ['name' => 'Lihat Jenis Olahraga',  'slug' => 'resource-types.view'],
            ['name' => 'Buat Jenis Olahraga',   'slug' => 'resource-types.create'],
            ['name' => 'Edit Jenis Olahraga',   'slug' => 'resource-types.edit'],
            ['name' => 'Hapus Jenis Olahraga',  'slug' => 'resource-types.delete'],

            // Resources
            ['name' => 'Lihat Lapangan', 'slug' => 'resources.view'],
            ['name' => 'Buat Lapangan',  'slug' => 'resources.create'],
            ['name' => 'Edit Lapangan',  'slug' => 'resources.edit'],
            ['name' => 'Hapus Lapangan', 'slug' => 'resources.delete'],

            // Pricing Rules
            ['name' => 'Lihat Aturan Harga', 'slug' => 'pricing-rules.view'],
            ['name' => 'Buat Aturan Harga',  'slug' => 'pricing-rules.create'],
            ['name' => 'Edit Aturan Harga',  'slug' => 'pricing-rules.edit'],
            ['name' => 'Hapus Aturan Harga', 'slug' => 'pricing-rules.delete'],

            // Bookings & Calendar
            ['name' => 'Lihat Booking & Kalender', 'slug' => 'bookings.view'],
            ['name' => 'Buat Booking',             'slug' => 'bookings.create'],
            ['name' => 'Edit Booking',             'slug' => 'bookings.edit'],
            ['name' => 'Hapus Booking',            'slug' => 'bookings.delete'],

            // Payments
            ['name' => 'Lihat Pembayaran',      'slug' => 'payments.view'],
            ['name' => 'Konfirmasi Pembayaran', 'slug' => 'payments.create'],
            ['name' => 'Edit Pembayaran',       'slug' => 'payments.edit'],
            ['name' => 'Hapus Pembayaran',      'slug' => 'payments.delete'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Assign semua permission ke role SuperAdmin & Tenant Owner
        $admin = Role::where('slug', 'superadmin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::pluck('id')->all());
        }

        $owner = Role::where('slug', 'owner')->first();
        if ($owner) {
            $ownerPermissions = Permission::whereIn('slug', [
                'dashboard.view',
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'venues.view', 'venues.create', 'venues.edit', 'venues.delete',
                'resource-types.view', 'resource-types.create', 'resource-types.edit', 'resource-types.delete',
                'resources.view', 'resources.create', 'resources.edit', 'resources.delete',
                'pricing-rules.view', 'pricing-rules.create', 'pricing-rules.edit', 'pricing-rules.delete',
                'bookings.view', 'bookings.create', 'bookings.edit', 'bookings.delete',
                'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            ])->pluck('id')->all();
            $owner->permissions()->sync($ownerPermissions);
        }

        $staff = Role::where('slug', 'staff')->first();
        if ($staff) {
            $staffPermissions = Permission::whereIn('slug', [
                'dashboard.view',
                'venues.view',
                'resources.view',
                'bookings.view', 'bookings.create', 'bookings.edit',
                'payments.view', 'payments.create',
            ])->pluck('id')->all();
            $staff->permissions()->sync($staffPermissions);
        }
    }
}
