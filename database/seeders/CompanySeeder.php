<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::firstOrCreate(
            ['slug' => 'pawstay-demo'],
            [
                'name'                       => 'PawStay Demo',
                'stripe_account_id'          => null,
                'stripe_onboarding_complete' => false,
                'application_fee_percent'    => 2.00,
            ]
        );
    }
}
