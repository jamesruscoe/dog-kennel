<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dogs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('owners')
                ->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('breed', 100);
            $table->date('date_of_birth')->nullable();
            $table->string('sex', 10); // male | female
            $table->boolean('neutered')->default(false);
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->string('colour', 100)->nullable();
            $table->string('microchip_number', 50)->nullable()->unique();

            // Vet details
            $table->string('vet_name', 255)->nullable();
            $table->string('vet_phone', 30)->nullable();
            $table->boolean('vaccination_confirmed')->default(false);

            // Care notes
            $table->text('medical_notes')->nullable();
            $table->text('dietary_notes')->nullable();
            $table->text('behavioural_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dogs');
    }
};
