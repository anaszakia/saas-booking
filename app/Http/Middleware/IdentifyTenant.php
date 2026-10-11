<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // 1. Deteksi lewat subdomain (misal: aero-padel.localhost atau aero-padel.app.com)
        $host = $request->getHost();
        $parts = explode('.', $host);

        // Jika subdomain ada dan bukan 'www' atau 'admin'
        if (count($parts) >= 2) {
            $subdomain = $parts[0];
            if (!in_array($subdomain, ['www', 'admin', 'localhost', '127'])) {
                $tenant = Tenant::findBySubdomain($subdomain);
            }
        }

        // 2. Fallback ke session jika sudah login / berpindah tenant di dashboard
        if (!$tenant && session()->has('tenant_id')) {
            $tenant = Tenant::findOptimized(session('tenant_id'));
        }

        // 3. Fallback ke tenant_id milik user yang sedang login
        if (!$tenant && auth()->check() && auth()->user()->tenant_id) {
            $tenant = Tenant::findOptimized(auth()->user()->tenant_id);
        }

        // Set context jika tenant ditemukan
        if ($tenant) {
            app()->instance('current_tenant', $tenant);
            app()->instance('current_tenant_id', $tenant->id);
            session(['tenant_id' => $tenant->id]);
        }

        return $next($request);
    }
}
