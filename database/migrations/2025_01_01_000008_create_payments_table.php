<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // One payment per booking
            $table->foreignId('booking_id')
                ->unique()
                ->constrained('bookings')
                ->cascadeOnDelete();

            // Stripe identifiers (nullable until Stripe is fully integrated — JOB 11)
            $table->string('stripe_payment_id', 100)->nullable()->unique();
            $table->string('stripe_payment_intent_id', 100)->nullable();

            // Amount in smallest currency unit (pence)
            $table->unsignedInteger('amount_pence');
            $table->string('currency', 3)->default('gbp');

            // pending | succeeded | failed | refunded
            $table->string('status', 20)->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
