<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained('venues')->cascadeOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained('resources')->cascadeOnDelete();
            $table->string('name', 150);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->text('reason')->nullable();

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Indexing rentang waktu penutupan
            $table->index(['tenant_id', 'start_at', 'end_at']);
            $table->index(['tenant_id', 'venue_id', 'start_at', 'end_at']);
            $table->index(['tenant_id', 'resource_id', 'start_at', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closures');
    }
};
