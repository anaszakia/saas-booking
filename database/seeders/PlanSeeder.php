<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'price'         => 199000.00,
                'max_resources' => 3,
                'max_staff'     => 2,
                'features'      => [
                    'online_booking'    => true,
                    'calendar_schedule' => true,
                    'export_reports'    => false,
                    'whatsapp_reminder' => false,
                    'custom_domain'     => false,
                ],
                'is_active'     => true,
            ],
            [
                'name'          => 'Pro',
                'slug'          => 'pro',
                'price'         => 399000.00,
                'max_resources' => 10,
                'max_staff'     => 5,
                'features'      => [
                    'online_booking'    => true,
                    'calendar_schedule' => true,
                    'export_reports'    => true,
                    'whatsapp_reminder' => true,
                    'custom_domain'     => false,
                ],
                'is_active'     => true,
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'price'         => 799000.00,
                'max_resources' => 50,
                'max_staff'     => 20,
                'features'      => [
                    'online_booking'    => true,
                    'calendar_schedule' => true,
                    'export_reports'    => true,
                    'whatsapp_reminder' => true,
                    'custom_domain'     => true,
                ],
                'is_active'     => true,
            ],
        ];

        foreach ($plans as $p) {
            Plan::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Assign plan Starter/Pro untuk setiap tenant yang sudah ada agar tidak terkunci
        $proPlan = Plan::where('slug', 'pro')->first();
        if ($proPlan) {
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                $hasSub = Subscription::where('tenant_id', $tenant->id)->exists();
                if (!$hasSub) {
                    Subscription::create([
                        'tenant_id' => $tenant->id,
                        'plan_id'   => $proPlan->id,
                        'status'    => 'active',
                        'starts_at' => now(),
                        'ends_at'   => now()->addMonths(3), // 3 bulan aktif
                    ]);
                }
            }
        }
    }
}
