<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@dog-desk.com'],
            [
                'name'              => 'Platform Admin',
                'password'          => Hash::make('password'),
                'role'              => UserRole::SuperAdmin->value,
                'company_id'        => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
