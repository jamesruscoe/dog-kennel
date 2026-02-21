<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kennel_settings', function (Blueprint $table) {
            $table->id();

            // Capacity
            $table->unsignedSmallInteger('max_capacity')->default(10);

            // Pricing — stored in smallest currency unit (pence/cents)
            $table->unsignedInteger('nightly_rate_pence')->default(3500); // £35.00

            // Operating schedule
            // JSON array of DayOfWeek integers (1=Mon ... 7=Sun)
            // NOTE: MySQL does not allow literal defaults on JSON columns;
            //       the seeder always sets this value explicitly.
            $table->json('operating_days')->nullable();

            // Check-in / check-out times (stored as HH:MM strings)
            $table->string('check_in_time', 5)->default('09:00');
            $table->string('check_out_time', 5)->default('17:00');

            // How many days in advance a booking must be made
            $table->unsignedTinyInteger('booking_lead_days')->default(1);

            $table->longText('terms_and_conditions')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kennel_settings');
    }
};
