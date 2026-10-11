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
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->unsignedInteger('months')->default(1);
            $table->decimal('amount', 12, 2);
            $table->string('provider', 30)->default('midtrans'); // midtrans, xendit, manual
            $table->string('payment_method', 50)->nullable();    // qris, bca_va, gopay, etc
            $table->string('external_id', 150)->nullable()->index();
            $table->string('snap_token', 255)->nullable();
            $table->string('checkout_url', 500)->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->json('payload')->nullable();

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Indexing
            $table->index(['tenant_id', 'status']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
