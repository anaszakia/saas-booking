<?php

namespace App\Providers;

use App\Http\Responses\PasskeyLoginResponse;
use App\Services\MinioService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Fortify::ignoreRoutes();

        // Daftarkan MinioService sebagai singleton
        $this->app->singleton(MinioService::class, function ($app) {
            return new MinioService();
        });

        $this->app->singleton(PasskeyLoginResponseContract::class, PasskeyLoginResponse::class);

        // Alias agar bisa dipanggil dengan 'minio'
        $this->app->alias(MinioService::class, 'minio');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        config([
            'passkeys.redirect' => '/dashboard',
            'passkeys.management_middleware' => [],
        ]);

        // Register S3 driver support untuk Flysystem
        // Ini memastikan AWS S3Client tersedia saat filesystem manager diakses
        \Illuminate\Support\Facades\Storage::resolved(function ($storage) {
            // S3 driver otomatis diload oleh League\Flysystem\AwsS3V3
        });

        // Macro auditColumns untuk standarisasi 6 kolom audit
        \Illuminate\Database\Schema\Blueprint::macro('auditColumns', function () {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            $this->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $this->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $this->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $this->timestamps();
            $this->softDeletes();
        });

        \Illuminate\Database\Schema\Blueprint::macro('dropAuditColumns', function () {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            $this->dropForeign(['created_by']);
            $this->dropForeign(['updated_by']);
            $this->dropForeign(['deleted_by']);
            $this->dropColumn(['created_by', 'updated_by', 'deleted_by', 'deleted_at', 'created_at', 'updated_at']);
        });
    }
}
