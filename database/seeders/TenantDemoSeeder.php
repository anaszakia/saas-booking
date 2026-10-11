<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\OperatingHour;
use App\Models\PricingRule;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantDemoSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('slug', 'owner')->first();

        // 1. TENANT 1: Aero Padel (3 Lapangan Padel)
        $aero = Tenant::updateOrCreate(
            ['slug' => 'aero-padel'],
            [
                'name'      => 'Aero Padel',
                'subdomain' => 'aero-padel',
                'email'     => 'contact@aeropadel.id',
                'phone'     => '081234567890',
                'address'   => 'Jl. Padel Boulevard No. 10, Jakarta Selatan',
                'timezone'  => 'Asia/Jakarta',
                'status'    => 'active',
            ]
        );

        // Owner Aero Padel
        $aeroOwner = User::updateOrCreate(
            ['email' => 'owner@aeropadel.id'],
            [
                'tenant_id' => $aero->id,
                'name'      => 'Owner Aero Padel',
                'password'  => Hash::make('password'),
                'role_id'   => $ownerRole?->id,
            ]
        );
        $aeroOwner->roles()->syncWithoutDetaching([$ownerRole?->id]);

        $venuePadel = Venue::updateOrCreate(
            ['tenant_id' => $aero->id, 'slug' => 'aero-padel-cilandak'],
            [
                'name'      => 'Aero Padel Cilandak',
                'city'      => 'Jakarta Selatan',
                'address'   => 'Jl. TB Simatupang No. 12',
                'is_active' => true,
            ]
        );

        $typePadel = ResourceType::updateOrCreate(
            ['slug' => 'padel'],
            [
                'tenant_id'            => $aero->id,
                'name'                 => 'Padel Tennis',
                'default_slot_minutes' => 60,
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 180,
                'buffer_minutes'       => 0,
                'is_active'            => true,
            ]
        );

        for ($i = 1; $i <= 3; $i++) {
            Resource::updateOrCreate(
                ['tenant_id' => $aero->id, 'venue_id' => $venuePadel->id, 'code' => 'COURT-0' . $i],
                [
                    'resource_type_id' => $typePadel->id,
                    'name'             => 'Court Padel ' . $i,
                    'capacity'         => 4,
                    'attributes'       => ['court_type' => $i === 1 ? 'Indoor Panoramic' : 'Outdoor Standard'],
                    'is_active'        => true,
                ]
            );
        }

        // Addons Padel
        Addon::updateOrCreate(
            ['tenant_id' => $aero->id, 'name' => 'Sewa Raket Padel'],
            ['price' => 50000, 'price_type' => 'per_item', 'stock' => 12, 'is_active' => true]
        );
        Addon::updateOrCreate(
            ['tenant_id' => $aero->id, 'name' => 'Bola Padel (Can isi 3)'],
            ['price' => 75000, 'price_type' => 'per_booking', 'stock' => 20, 'is_active' => true]
        );

        // Pricing Rules Aero Padel (Weekday Siang vs Malam vs Weekend)
        PricingRule::updateOrCreate(
            ['tenant_id' => $aero->id, 'name' => 'Weekday Siang'],
            [
                'venue_id'       => $venuePadel->id,
                'day_type'       => 'weekday',
                'start_time'     => '08:00:00',
                'end_time'       => '16:00:00',
                'price_per_slot' => 250000,
                'priority'       => 10,
                'is_active'      => true,
            ]
        );
        PricingRule::updateOrCreate(
            ['tenant_id' => $aero->id, 'name' => 'Weekday Prime Malam'],
            [
                'venue_id'       => $venuePadel->id,
                'day_type'       => 'weekday',
                'start_time'     => '16:00:00',
                'end_time'       => '23:00:00',
                'price_per_slot' => 350000,
                'priority'       => 20,
                'is_active'      => true,
            ]
        );
        PricingRule::updateOrCreate(
            ['tenant_id' => $aero->id, 'name' => 'Weekend All Day'],
            [
                'venue_id'       => $venuePadel->id,
                'day_type'       => 'weekend',
                'price_per_slot' => 400000,
                'priority'       => 15,
                'is_active'      => true,
            ]
        );

        // Operating Hours Aero Padel (08:00 - 23:00 setiap hari)
        for ($day = 0; $day <= 6; $day++) {
            OperatingHour::updateOrCreate(
                ['tenant_id' => $aero->id, 'venue_id' => $venuePadel->id, 'day_of_week' => $day],
                ['open_time' => '08:00:00', 'close_time' => '23:00:00', 'is_closed' => false]
            );
        }

        // 2. TENANT 2: Amedsport Center (5 Lapangan Mini Soccer)
        $amedsport = Tenant::updateOrCreate(
            ['slug' => 'amedsport-center'],
            [
                'name'      => 'Amedsport Center',
                'subdomain' => 'amedsport',
                'email'     => 'info@amedsport.com',
                'phone'     => '081298765432',
                'address'   => 'Jl. Lapangan Hijau No. 8, Tangerang',
                'timezone'  => 'Asia/Jakarta',
                'status'    => 'active',
            ]
        );

        $typeSoccer = ResourceType::updateOrCreate(
            ['slug' => 'mini-soccer'],
            [
                'tenant_id'            => $amedsport->id,
                'name'                 => 'Mini Soccer',
                'default_slot_minutes' => 120, // 2 jam per slot
                'min_duration_minutes' => 120,
                'max_duration_minutes' => 240,
                'is_active'            => true,
            ]
        );

        $venueSoccer = Venue::updateOrCreate(
            ['tenant_id' => $amedsport->id, 'slug' => 'amedsport-arena'],
            [
                'name'      => 'Amedsport Arena',
                'city'      => 'Tangerang',
                'is_active' => true,
            ]
        );

        for ($i = 1; $i <= 5; $i++) {
            Resource::updateOrCreate(
                ['tenant_id' => $amedsport->id, 'venue_id' => $venueSoccer->id, 'code' => 'SOCCER-0' . $i],
                [
                    'resource_type_id' => $typeSoccer->id,
                    'name'             => 'Lapangan Soccer ' . $i,
                    'capacity'         => 14,
                    'attributes'       => ['grass' => 'Sintetis Monofilament Grade A'],
                    'is_active'        => true,
                ]
            );
        }

        // 3. TENANT 3: Didik Billiard (10 Meja Billiard)
        $didik = Tenant::updateOrCreate(
            ['slug' => 'didik-billiard'],
            [
                'name'      => 'Didik Billiard',
                'subdomain' => 'didik-billiard',
                'email'     => 'halo@didikbilliard.com',
                'phone'     => '081311223344',
                'address'   => 'Mall Central lt. 3, Bandung',
                'timezone'  => 'Asia/Jakarta',
                'status'    => 'active',
            ]
        );

        $typeBilliard = ResourceType::updateOrCreate(
            ['slug' => 'billiard'],
            [
                'tenant_id'            => $didik->id,
                'name'                 => 'Billiard Pool',
                'default_slot_minutes' => 60,
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 300,
                'is_active'            => true,
            ]
        );

        $venueBilliard = Venue::updateOrCreate(
            ['tenant_id' => $didik->id, 'slug' => 'didik-billiard-bandung'],
            [
                'name'      => 'Didik Billiard Bandung',
                'city'      => 'Bandung',
                'is_active' => true,
            ]
        );

        for ($i = 1; $i <= 10; $i++) {
            Resource::updateOrCreate(
                ['tenant_id' => $didik->id, 'venue_id' => $venueBilliard->id, 'code' => 'TABLE-' . sprintf('%02d', $i)],
                [
                    'resource_type_id' => $typeBilliard->id,
                    'name'             => 'Meja Billiard ' . $i,
                    'capacity'         => 4,
                    'attributes'       => ['table_brand' => 'Rasson 9ft Tournament Table'],
                    'is_active'        => true,
                ]
            );
        }
    }
}
