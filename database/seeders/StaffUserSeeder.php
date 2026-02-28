<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', 'pawstay-demo')->firstOrFail();

        // Primary admin account (has access to Finance panel)
        User::firstOrCreate(
            ['email' => 'admin@pawstay-demo.test'],
            [
                'name'              => 'Demo Admin',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Admin->value,
                'company_id'        => $company->id,
                'email_verified_at' => now(),
            ]
        );

        // Staff user
        User::firstOrCreate(
            ['email' => 'staff@pawstay-demo.test'],
            [
                'name'              => 'Kennel Staff',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Staff->value,
                'company_id'        => $company->id,
                'email_verified_at' => now(),
            ]
        );

        // Second staff member for care log variety
        User::firstOrCreate(
            ['email' => 'jane@pawstay-demo.test'],
            [
                'name'              => 'Jane Smith',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Staff->value,
                'company_id'        => $company->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
