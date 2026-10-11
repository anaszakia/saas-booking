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
        Schema::create('tenant_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->enum('provider', ['midtrans', 'xendit', 'manual'])->default('manual');
            $table->text('server_key')->nullable(); // Enkripsi
            $table->string('client_key')->nullable();
            $table->string('merchant_id')->nullable();
            $table->text('webhook_secret')->nullable(); // Enkripsi
            $table->boolean('is_sandbox')->default(true);
            $table->boolean('is_active')->default(false);
            $table->dateTime('verified_at')->nullable();
            $table->text('bank_transfer_instructions')->nullable();

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Indexing
            $table->index(['tenant_id', 'provider']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_payment_settings');
    }
};
