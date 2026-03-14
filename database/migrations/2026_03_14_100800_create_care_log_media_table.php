<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_log_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('care_log_id')->constrained('care_logs')->cascadeOnDelete();
            $table->string('path', 500);
            $table->string('disk', 20)->default('s3');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();

            $table->index('care_log_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_log_media');
    }
};
