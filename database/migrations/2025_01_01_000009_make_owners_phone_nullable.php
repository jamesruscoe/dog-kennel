<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Public self-registration creates an Owner record without a phone number.
// Owners complete their profile (phone, address) after their first login.
// Staff-created owners still require phone via StoreOwnerRequest.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->string('phone', 30)->nullable(false)->change();
        });
    }
};
