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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('refund_number', 50)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->enum('method', ['auto_gateway', 'manual_transfer', 'store_credit'])->default('manual_transfer');
            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed'])->default('pending');
            $table->string('external_id', 100)->nullable(); // Refund ID dari gateway
            $table->json('destination_account')->nullable(); // Nomor rekening & nama bank tujuan
            $table->string('proof_file')->nullable(); // Bukti transfer manual refund
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Indexing
            $table->index(['tenant_id', 'booking_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
