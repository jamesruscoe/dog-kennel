<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['owners', 'dogs', 'bookings', 'care_logs', 'payments'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->cascadeOnDelete();

                $blueprint->index('company_id', "{$table}_company_id_index");
            });
        }

        // kennel_settings: add company_id with unique constraint (one row per company)
        Schema::table('kennel_settings', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        $tables = ['owners', 'dogs', 'bookings', 'care_logs', 'payments'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign(['company_id']);
                $blueprint->dropIndex("{$table}_company_id_index");
                $blueprint->dropColumn('company_id');
            });
        }

        Schema::table('kennel_settings', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
