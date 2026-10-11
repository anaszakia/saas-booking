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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Petugas / Staf jika walk-in
            $table->string('booking_code', 50)->unique();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('addon_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->dateTime('hold_expires_at')->nullable(); // Slot hold 15 menit
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            // 6 Kolom Audit Wajib
            $table->auditColumns();

            // Composite Indexes Khusus Performa Ekstrem (Availability Check & Scheduler Anti-Bentrok)
            $table->index(['tenant_id', 'resource_id', 'start_at', 'end_at', 'status'], 'idx_bookings_availability');
            $table->index(['tenant_id', 'status', 'hold_expires_at'], 'idx_bookings_hold_cleanup');
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'start_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
