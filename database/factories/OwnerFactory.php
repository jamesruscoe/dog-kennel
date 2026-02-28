<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Owner>
 */
class OwnerFactory extends Factory
{
    protected $model = Owner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id'              => Company::factory(),
            'user_id'                 => User::factory()->owner(),
            'phone'                   => fake()->phoneNumber(),
            'address'                 => fake()->address(),
            'emergency_contact_name'  => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'notes'                   => fake()->optional(0.3)->sentence(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }

    public function forCompany(Company $company): static
    {
        return $this->state(['company_id' => $company->id]);
    }
}
