<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // A booking is for a single dog; dog deletion is restricted if bookings exist
            $table->foreignId('dog_id')
                ->constrained('dogs')
                ->restrictOnDelete();

            $table->date('check_in_date');
            $table->date('check_out_date');

            // Status: pending | approved | rejected | cancelled | completed
            $table->string('status', 20)->default('pending');

            // Owner / staff notes
            $table->text('notes')->nullable();
            $table->text('special_requirements')->nullable();

            // Status-change reasons
            $table->string('rejection_reason', 1000)->nullable();
            $table->string('cancellation_reason', 1000)->nullable();

            // Payment summary (mirrors payments table, denormalised for quick reads)
            $table->unsignedInteger('amount_pence')->nullable();
            $table->string('payment_status', 30)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ─────────────────────────────────────────────────────────
            // Capacity query: find all bookings overlapping a given date range
            $table->index(['check_in_date', 'check_out_date', 'status'], 'bookings_capacity_idx');
            // Fast lookup by dog and status
            $table->index(['dog_id', 'status'], 'bookings_dog_status_idx');
            // Status-only filtering (admin list views)
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
