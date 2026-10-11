<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('slug', 'superadmin')->first();

        $dashboard = $this->menu([
            'name'  => 'Dashboard',
            'url'   => '/dashboard',
            'icon'  => 'ti ti-layout-dashboard',
            'order' => 1,
        ]);

        $menuManagement = $this->menu([
            'name'  => 'Menu Management',
            'url'   => null,
            'icon'  => 'ti ti-menu-deep',
            'order' => 2,
        ]);

        $children = [
            [
                'name'      => 'User',
                'url'       => '/users',
                'icon'      => 'ti ti-users',
                'parent_id' => $menuManagement->id,
                'order'     => 1,
            ],
            [
                'name'      => 'Roles',
                'url'       => '/roles',
                'icon'      => 'ti ti-shield',
                'parent_id' => $menuManagement->id,
                'order'     => 2,
            ],
            [
                'name'      => 'Permission',
                'url'       => '/permissions',
                'icon'      => 'ti ti-key',
                'parent_id' => $menuManagement->id,
                'order'     => 3,
            ],
            [
                'name'      => 'Menus',
                'url'       => '/menus',
                'icon'      => 'ti ti-menu-2',
                'parent_id' => $menuManagement->id,
                'order'     => 4,
            ],
        ];

        // Group Fasilitas & Tempat
        $facilityManagement = $this->menu([
            'name'  => 'Tempat & Fasilitas',
            'url'   => null,
            'icon'  => 'ti ti-building-stadium',
            'order' => 3,
        ]);

        $facilityChildren = [
            [
                'name'      => 'Data Venue / Cabang',
                'url'       => '/venues',
                'icon'      => 'ti ti-map-pin',
                'parent_id' => $facilityManagement->id,
                'order'     => 1,
            ],
            [
                'name'      => 'Lapangan & Meja',
                'url'       => '/resources',
                'icon'      => 'ti ti-ball-football',
                'parent_id' => $facilityManagement->id,
                'order'     => 2,
            ],
            [
                'name'      => 'Aturan Tarif & Harga',
                'url'       => '/pricing-rules',
                'icon'      => 'ti ti-currency-dollar',
                'parent_id' => $facilityManagement->id,
                'order'     => 3,
            ],
        ];

        // Group Operasional Booking
        $bookingManagement = $this->menu([
            'name'  => 'Operasional Booking',
            'url'   => null,
            'icon'  => 'ti ti-calendar-event',
            'order' => 4,
        ]);

        $bookingChildren = [
            [
                'name'      => 'Kalender Lapangan',
                'url'       => '/bookings/calendar',
                'icon'      => 'ti ti-calendar',
                'parent_id' => $bookingManagement->id,
                'order'     => 1,
            ],
            [
                'name'      => 'Input Booking Walk-in',
                'url'       => '/bookings/create',
                'icon'      => 'ti ti-calendar-plus',
                'parent_id' => $bookingManagement->id,
                'order'     => 2,
            ],
            [
                'name'      => 'Daftar & Riwayat Booking',
                'url'       => '/bookings',
                'icon'      => 'ti ti-list-details',
                'parent_id' => $bookingManagement->id,
                'order'     => 3,
            ],
            [
                'name'      => 'Gateway Pembayaran Venue',
                'url'       => '/payment-settings',
                'icon'      => 'ti ti-credit-card',
                'parent_id' => $bookingManagement->id,
                'order'     => 4,
            ],
            [
                'name'      => 'Perpanjang Langganan SaaS',
                'url'       => '/subscription/checkout',
                'icon'      => 'ti ti-crown',
                'parent_id' => $bookingManagement->id,
                'order'     => 5,
            ],
        ];

        // Group SuperAdmin SaaS Platform
        $saasManagement = $this->menu([
            'name'  => 'Manajemen SaaS Platform',
            'url'   => null,
            'icon'  => 'ti ti-building-store',
            'order' => 5,
        ]);

        $saasChildren = [
            [
                'name'      => 'Daftar Tenant SaaS',
                'url'       => '/superadmin/tenants',
                'icon'      => 'ti ti-users-group',
                'parent_id' => $saasManagement->id,
                'order'     => 1,
            ],
            [
                'name'      => 'Paket Langganan (Plans)',
                'url'       => '/superadmin/plans',
                'icon'      => 'ti ti-packages',
                'parent_id' => $saasManagement->id,
                'order'     => 2,
            ],
            [
                'name'      => 'Gateway Platform SaaS',
                'url'       => '/superadmin/payment-settings',
                'icon'      => 'ti ti-wallet',
                'parent_id' => $saasManagement->id,
                'order'     => 3,
            ],
        ];

        $menus = collect([$dashboard, $menuManagement, $facilityManagement, $bookingManagement, $saasManagement]);

        foreach ($children as $child) {
            $menus->push($this->menu($child));
        }

        foreach ($facilityChildren as $child) {
            $menus->push($this->menu($child));
        }

        foreach ($bookingChildren as $child) {
            $menus->push($this->menu($child));
        }

        foreach ($saasChildren as $child) {
            $menus->push($this->menu($child));
        }

        $allMenuIds = $menus->pluck('id')->all();

        if ($admin) {
            $admin->menus()->syncWithoutDetaching($allMenuIds);
        }

        $owner = Role::where('slug', 'owner')->first();
        if ($owner) {
            $owner->menus()->syncWithoutDetaching($allMenuIds);
        }

        $staff = Role::where('slug', 'staff')->first();
        if ($staff) {
            $staffMenuIds = $menus->filter(function ($m) {
                return in_array($m->url, ['/dashboard', '/bookings/calendar', '/bookings/create', '/bookings', '/venues', '/resources'])
                    || in_array($m->name, ['Operasional Booking', 'Tempat & Fasilitas']);
            })->pluck('id')->all();
            $staff->menus()->syncWithoutDetaching($staffMenuIds);
        }
    }

    private function menu(array $data): Menu
    {
        $lookup = $data['url'] === null
            ? ['name' => $data['name'], 'url' => null]
            : ['url' => $data['url']];

        return Menu::updateOrCreate(
            $lookup,
            [
                'name'      => $data['name'],
                'icon'      => $data['icon'],
                'parent_id' => $data['parent_id'] ?? null,
                'order'     => $data['order'],
                'is_active' => true,
            ]
        );
    }
}
