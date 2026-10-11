<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Superadmin selalu memiliki akses penuh tanpa batas subscription
        if ($user && $user->roles()->where('slug', 'superadmin')->exists()) {
            return $next($request);
        }

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : ($user?->tenant_id ?? session('tenant_id'));

        if (!$tenantId) {
            return $next($request);
        }

        // Cek status subscription aktif
        if (!Subscription::hasActiveAccess($tenantId)) {
            // Jika request adalah JSON/AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Masa langganan aplikasi telah berakhir. Silakan lakukan perpanjangan.',
                ], 403);
            }

            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
