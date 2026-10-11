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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->foreignId('resource_type_id')->constrained('resource_types')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->unsignedSmallInteger('capacity')->default(4);
            $table->string('image')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('is_active')->default(true);

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Indexing performa tinggi
            $table->index(['tenant_id', 'venue_id', 'is_active']);
            $table->index(['tenant_id', 'resource_type_id', 'is_active']);
            $table->index(['tenant_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
