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
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->decimal('price', 12, 2);
            $table->enum('price_type', ['per_booking', 'per_hour', 'per_item'])->default('per_booking');
            $table->integer('stock')->nullable();
            $table->boolean('is_active')->default(true);

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Indexing
            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};
