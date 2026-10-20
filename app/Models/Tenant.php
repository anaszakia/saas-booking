<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    use SoftDeletes, HasAuditColumns, HasUlid;

    protected $fillable = [
        'ulid',
        'name',
        'slug',
        'subdomain',
        'logo',
        'email',
        'phone',
        'address',
        'timezone',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function venues()
    {
        return $this->hasMany(Venue::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Daftarkan tenant baru beserta akun pemilik (Owner) dan trial paket SaaS
     */
    public static function registerNewTenantWithOwner(array $tenantData, array $userData): array
    {
        return DB::transaction(function () use ($tenantData, $userData) {
            $slug = \Illuminate\Support\Str::slug($tenantData['name']);
            $baseSlug = $slug;
            $counter = 1;
            while (static::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $subdomain = str_replace('-', '', $slug);

            $tenant = static::create([
                'name'      => $tenantData['name'],
                'slug'      => $slug,
                'subdomain' => $subdomain,
                'email'     => $userData['email'],
                'phone'     => $userData['phone'] ?? null,
                'address'   => $tenantData['address'] ?? null,
                'status'    => 'active',
            ]);

            $ownerRole = Role::where('slug', 'owner')->first() ?? Role::where('slug', 'superadmin')->first();

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $userData['name'],
                'email'     => $userData['email'],
                'password'  => \Illuminate\Support\Facades\Hash::make($userData['password']),
                'role_id'   => $ownerRole?->id,
                'phone'     => $userData['phone'] ?? null,
            ]);

            if ($ownerRole) {
                $user->roles()->syncWithoutDetaching([$ownerRole->id]);
            }

            // Berikan paket langganan (default trial starter 14 hari, atau paket pilihan Super Admin)
            $planId = $tenantData['plan_id'] ?? null;
            $durationMonths = (int) ($tenantData['duration_months'] ?? 0);

            if ($planId) {
                $chosenPlan = Plan::where('id', $planId)->first();
            } else {
                $chosenPlan = Plan::where('slug', 'starter')->first() ?? Plan::first();
            }

            if ($chosenPlan) {
                $startsAt = now();
                if ($durationMonths > 0) {
                    $endsAt = now()->addMonths($durationMonths);
                    $status = 'active';
                    $trialEndsAt = null;
                } else {
                    $endsAt = now()->addDays(14);
                    $status = 'trialing';
                    $trialEndsAt = now()->addDays(14);
                }

                Subscription::create([
                    'tenant_id'     => $tenant->id,
                    'plan_id'       => $chosenPlan->id,
                    'status'        => $status,
                    'starts_at'     => $startsAt,
                    'ends_at'       => $endsAt,
                    'trial_ends_at' => $trialEndsAt,
                ]);
            }

            return ['tenant' => $tenant, 'user' => $user];
        });
    }

    /**
     * Cari tenant berdasarkan subdomain (ultra fast raw lookup)
     */
    public static function findBySubdomain(string $subdomain): ?self
    {
        return static::select('id', 'ulid', 'name', 'slug', 'subdomain', 'logo', 'timezone', 'status', 'settings')
            ->where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Cari tenant berdasarkan slug (untuk akses /b/{slug})
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::select('id', 'ulid', 'name', 'slug', 'subdomain', 'logo', 'email', 'phone', 'address', 'timezone', 'status', 'settings')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Find optimized dengan lean columns
     */
    public static function findOptimized(int|string $idOrUlid): ?self
    {
        $column = is_numeric($idOrUlid) ? 'id' : 'ulid';
        return static::select('id', 'ulid', 'name', 'slug', 'subdomain', 'logo', 'email', 'phone', 'address', 'timezone', 'status', 'settings', 'created_at')
            ->where($column, $idOrUlid)
            ->first();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get paginated tenants untuk SuperAdmin platform
     */
    public static function getPaginatedTenants(?string $search = null, int $perPage = 10)
    {
        $query = static::select('id', 'ulid', 'name', 'slug', 'subdomain', 'phone', 'email', 'status', 'created_at')
            ->withCount(['venues', 'resources'])
            ->with(['subscriptions' => function ($q) {
                $q->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                  ->select('subscriptions.id', 'subscriptions.tenant_id', 'subscriptions.plan_id', 'subscriptions.status', 'subscriptions.ends_at', 'plans.name as plan_name')
                  ->orderByDesc('subscriptions.id')
                  ->limit(1);
            }]);

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Logo URL helper
     */
    public function getLogoUrlAttribute(): string
    {
        return minio_avatar($this->logo, $this->name);
    }
}
