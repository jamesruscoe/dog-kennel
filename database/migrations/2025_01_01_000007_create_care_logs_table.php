<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            // Staff member who logged the activity
            $table->foreignId('logged_by')
                ->constrained('users')
                ->restrictOnDelete();

            // Activity type: feeding | walking | medication | grooming | play |
            //                toilet | health_check | other
            $table->string('activity_type', 30);

            $table->text('notes')->nullable();

            // When the activity actually happened (may differ from created_at)
            $table->timestamp('occurred_at')->useCurrent();

            $table->timestamps();

            // Queries: care log feed per booking ordered by time
            $table->index(['booking_id', 'occurred_at'], 'care_logs_booking_time_idx');
            // Staff performance / activity-type reporting
            $table->index(['activity_type', 'occurred_at'], 'care_logs_activity_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_logs');
    }
};
