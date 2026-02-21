<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Run order matters — settings and staff must exist before demo data.
     */
    public function run(): void
    {
        $this->call([
            KennelSettingsSeeder::class,
            StaffUserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
