<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        // Primary staff / admin account
        User::firstOrCreate(
            ['email' => 'staff@kennel.test'],
            [
                'name'              => 'Kennel Staff',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Staff->value,
                'email_verified_at' => now(),
            ]
        );

        // Second staff member for care log variety
        User::firstOrCreate(
            ['email' => 'jane@kennel.test'],
            [
                'name'              => 'Jane Smith',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Staff->value,
                'email_verified_at' => now(),
            ]
        );
    }
}

