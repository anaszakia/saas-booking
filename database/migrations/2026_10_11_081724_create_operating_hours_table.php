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
        Schema::create('operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained('venues')->cascadeOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained('resources')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 = Minggu, 1 = Senin, ... 6 = Sabtu
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_closed')->default(false);

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Indexing
            $table->index(['tenant_id', 'day_of_week']);
            $table->index(['tenant_id', 'venue_id', 'day_of_week']);
            $table->index(['tenant_id', 'resource_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_hours');
    }
};
