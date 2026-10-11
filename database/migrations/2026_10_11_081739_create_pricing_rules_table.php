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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained('venues')->cascadeOnDelete();
            $table->foreignId('resource_type_id')->nullable()->constrained('resource_types')->cascadeOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained('resources')->cascadeOnDelete();
            $table->string('name', 150);
            $table->enum('day_type', ['all', 'weekday', 'weekend'])->default('all');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('price_per_slot', 12, 2);
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Indexing rule matching
            $table->index(['tenant_id', 'is_active', 'day_type']);
            $table->index(['tenant_id', 'resource_id', 'is_active']);
            $table->index(['tenant_id', 'resource_type_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
