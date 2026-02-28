<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\KennelSettings;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyCreationService
{
    public function __construct(
        private readonly CompanySlugService $slugService,
    ) {}

    /**
     * Create a new company with an admin user and default settings.
     *
     * @param  array{company_name: string, name: string, email: string, password: string, slug?: string}  $data
     */
    public function create(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $slug = isset($data['slug'])
                ? $data['slug']
                : $this->slugService->generateUnique($data['company_name']);

            $company = Company::create([
                'name' => $data['company_name'],
                'slug' => $slug,
            ]);

            User::create([
                'company_id'        => $company->id,
                'name'              => $data['name'],
                'email'             => $data['email'],
                'password'          => Hash::make($data['password']),
                'role'              => UserRole::Admin->value,
                'email_verified_at' => now(),
            ]);

            // Seed default kennel settings for the new company
            KennelSettings::create([
                'company_id'         => $company->id,
                'max_capacity'       => 10,
                'nightly_rate_pence' => 3500,
                'operating_days'     => [1, 2, 3, 4, 5, 6],
                'check_in_time'      => '10:00',
                'check_out_time'     => '16:00',
                'booking_lead_days'  => 1,
                'terms_and_conditions' => '',
            ]);

            return $company;
        });
    }
}
