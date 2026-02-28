<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Run order:
     * 1. Company first — all other seeders depend on the demo company existing
     * 2. Super admin — platform-level user with no company
     * 3. Staff users — attached to demo company
     * 4. Kennel settings — per-company singleton
     * 5. Demo data — owners, dogs, bookings, care logs
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            SuperAdminSeeder::class,
            StaffUserSeeder::class,
            KennelSettingsSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
