<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company() . ' Kennels';

        return [
            'name'                       => $name,
            'slug'                       => Str::slug($name) . '-' . Str::random(4),
            'stripe_account_id'          => null,
            'stripe_onboarding_complete' => false,
            'application_fee_percent'    => 2.00,
        ];
    }

    public function stripeReady(): static
    {
        return $this->state([
            'stripe_account_id'          => 'acct_' . Str::random(16),
            'stripe_onboarding_complete' => true,
        ]);
    }
}
