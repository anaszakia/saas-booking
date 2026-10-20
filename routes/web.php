<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use Laravel\Passkeys\Http\Controllers\PasskeyConfirmationController;
use Laravel\Passkeys\Http\Controllers\PasskeyLoginController;
use Laravel\Passkeys\Http\Controllers\PasskeyRegistrationController;


// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
Route::post('/forgot-password', fn() => back()->with('status', 'Link dikirim!'))->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token?}', fn($token = '') => view('auth.reset-password', compact('token')))->name('password.reset');
Route::post('/reset-password', fn() => redirect()->route('login'))->middleware('throttle:5,1')->name('password.update');
Route::get('/otp-verification', fn() => view('auth.otp-verification'))->name('otp.verification');
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Passkeys
Route::get('/passkeys/login/options', [PasskeyLoginController::class, 'index'])
    ->middleware('guest:web')
    ->name('passkey.login-options');
Route::post('/passkeys/login', [PasskeyLoginController::class, 'store'])
    ->middleware('guest:web')
    ->name('passkey.login');

Route::middleware(['auth:web', 'auth.custom'])->group(function () {
    Route::get('/passkeys/confirm/options', [PasskeyConfirmationController::class, 'index'])
        ->name('passkey.confirm-options');
    Route::post('/passkeys/confirm', [PasskeyConfirmationController::class, 'store'])
        ->name('passkey.confirm');
    Route::get('/user/passkeys/options', [PasskeyRegistrationController::class, 'index'])
        ->name('passkey.registration-options');
    Route::post('/user/passkeys', [PasskeyRegistrationController::class, 'store'])
        ->name('passkey.store');
    Route::delete('/user/passkeys/{passkey}', [PasskeyRegistrationController::class, 'destroy'])
        ->name('passkey.destroy');
});

// Protected routes
Route::middleware(['auth.custom', 'auto.logout'])->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Menu management - hanya superadmin
    Route::middleware(['role:superadmin'])->group(function () {
        Route::resource('/menus', MenuController::class);
        Route::resource('/roles', RoleController::class);
        Route::post('/permissions/sync-routes', [PermissionController::class, 'syncRoutes'])->name('permissions.sync-routes');
        Route::resource('/permissions', PermissionController::class);

        // SuperAdmin SaaS Management
        Route::prefix('superadmin')->name('superadmin.')->group(function () {
            Route::get('/tenants', [\App\Http\Controllers\SuperAdmin\SuperAdminTenantController::class, 'index'])->name('tenants.index');
            Route::get('/tenants/create', [\App\Http\Controllers\SuperAdmin\SuperAdminTenantController::class, 'create'])->name('tenants.create');
            Route::post('/tenants', [\App\Http\Controllers\SuperAdmin\SuperAdminTenantController::class, 'store'])->name('tenants.store');
            Route::get('/tenants/{tenant}', [\App\Http\Controllers\SuperAdmin\SuperAdminTenantController::class, 'show'])->name('tenants.show');
            Route::post('/tenants/{tenant}/extend', [\App\Http\Controllers\SuperAdmin\SuperAdminTenantController::class, 'extend'])->name('tenants.extend');
            Route::post('/tenants/{tenant}/toggle-status', [\App\Http\Controllers\SuperAdmin\SuperAdminTenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

            Route::resource('/plans', \App\Http\Controllers\SuperAdmin\SuperAdminPlanController::class);

            // Pengaturan Gateway Resmi Milik Super Admin Platform (SaaS Subscription)
            Route::get('/payment-settings', [\App\Http\Controllers\SuperAdmin\SuperAdminPaymentSettingController::class, 'index'])->name('payment-settings.index');
            Route::post('/payment-settings', [\App\Http\Controllers\SuperAdmin\SuperAdminPaymentSettingController::class, 'update'])->name('payment-settings.update');
        });
    });

    // Halaman informasi langganan kedaluwarsa untuk tenant & checkout
    Route::get('/subscription/expired', function () {
        $tenantId = current_tenant_id();
        $currentSub = $tenantId ? \App\Models\Subscription::getCurrentTenantSubscription($tenantId) : null;
        return view('tenants.subscription.expired', compact('currentSub'));
    })->name('subscription.expired');

    // Checkout & Perpanjangan Langganan SaaS oleh Pemilik Venue
    Route::get('/subscription/checkout', [\App\Http\Controllers\Tenant\SubscriptionCheckoutController::class, 'index'])->name('tenant.subscription.checkout');
    Route::post('/subscription/checkout', [\App\Http\Controllers\Tenant\SubscriptionCheckoutController::class, 'store'])->name('tenant.subscription.checkout.store');
    Route::get('/subscription/pay/{invoice}', [\App\Http\Controllers\Tenant\SubscriptionCheckoutController::class, 'pay'])->name('tenant.subscription.pay');
    Route::post('/subscription/pay/{invoice}/simulate', [\App\Http\Controllers\Tenant\SubscriptionCheckoutController::class, 'simulatePay'])->name('tenant.subscription.simulate-pay');

// Keep-alive untuk reset session timeout
Route::post('/keep-alive', function () {
    session(['last_activity' => now()->timestamp]);
    return response()->json(['status' => 'ok']);
})->middleware(['auth.custom'])->name('keep.alive');

    // Modul Operasional Tenant (Dilindungi oleh Subscription Enforcement)
    Route::middleware(['tenant.identify', 'tenant.subscription'])->group(function () {
        // Modul Users
        Route::resource('/users', UserController::class);

        // Modul Master Fasilitas & Tempat Olahraga
        Route::resource('/venues', \App\Http\Controllers\VenueController::class);
        Route::resource('/resource-types', \App\Http\Controllers\ResourceTypeController::class);
        Route::resource('/resources', \App\Http\Controllers\ResourceController::class);
        Route::resource('/pricing-rules', \App\Http\Controllers\PricingRuleController::class);

        // Pengaturan Payment Gateway Milik Tenant Venue
        Route::get('/payment-settings', [\App\Http\Controllers\Tenant\TenantPaymentSettingController::class, 'index'])->name('tenant.payment-settings.index');
        Route::post('/payment-settings', [\App\Http\Controllers\Tenant\TenantPaymentSettingController::class, 'update'])->name('tenant.payment-settings.update');

        // Modul Operasional Booking & Pembayaran Kasir
        Route::get('/bookings/calendar', [\App\Http\Controllers\BookingCalendarController::class, 'index'])->name('bookings.calendar');
        Route::resource('/bookings', \App\Http\Controllers\BookingController::class);
        Route::post('/payments/{payment}/confirm', [\App\Http\Controllers\PaymentController::class, 'confirm'])->name('payments.confirm');
    });
});

// ==========================================
// WEBHOOK CALLBACKS (Bypass CSRF & Auth)
// ==========================================
Route::prefix('webhook')->name('webhook.')->group(function () {
    // 1. Webhook Pembayaran Langganan SaaS Platform (B2B)
    Route::post('/subscription/midtrans', [\App\Http\Controllers\Webhook\SubscriptionWebhookController::class, 'handleMidtrans'])->name('subscription.midtrans');
    Route::post('/subscription/xendit', [\App\Http\Controllers\Webhook\SubscriptionWebhookController::class, 'handleXendit'])->name('subscription.xendit');

    // 2. Webhook Pembayaran Sewa Booking Lapangan Tenant (B2C)
    Route::post('/payment/midtrans', [\App\Http\Controllers\Webhook\BookingWebhookController::class, 'handleMidtrans'])->name('payment.midtrans');
    Route::post('/payment/xendit', [\App\Http\Controllers\Webhook\BookingWebhookController::class, 'handleXendit'])->name('payment.xendit');
});

// ==========================================
// PUBLIC TENANT PORTAL (Tanpa Auth Dashboard)
// ==========================================
Route::get('/b/{slug}', [\App\Http\Controllers\PublicBookingController::class, 'index'])->name('public.booking');
Route::post('/b/{slug}/book', [\App\Http\Controllers\PublicBookingController::class, 'store'])->name('public.book');
Route::get('/invoice/{booking_code}', [\App\Http\Controllers\PublicBookingController::class, 'invoice'])->name('public.invoice');
Route::get('/invoice/{booking_code}/download-pdf', [\App\Http\Controllers\PublicBookingController::class, 'downloadPdf'])->name('public.invoice.download-pdf');
Route::post('/invoice/{booking_code}/snap-token', [\App\Http\Controllers\PublicBookingController::class, 'requestSnapToken'])->name('public.invoice.snap-token');
Route::post('/invoice/{booking_code}/upload-proof', [\App\Http\Controllers\PublicBookingController::class, 'uploadProof'])->name('public.upload-proof');

