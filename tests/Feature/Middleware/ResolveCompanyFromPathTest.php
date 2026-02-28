<?php

namespace Tests\Feature\Middleware;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveCompanyFromPathTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_company_from_path_and_binds_context(): void
    {
        $company = Company::factory()->create(['slug' => 'my-kennel']);
        $staff   = User::factory()->staff()->forCompany($company)->create();

        $this->withoutVite()
            ->actingAs($staff)
            ->get("/{$company->slug}/staff/dogs")
            ->assertStatus(200);
    }

    public function test_returns_404_for_nonexistent_slug(): void
    {
        // Use a guest route so auth middleware does not redirect first
        $this->get('/does-not-exist/login')
            ->assertStatus(404);
    }

    public function test_shares_company_as_inertia_prop(): void
    {
        $company = Company::factory()->create(['slug' => 'my-kennel']);
        $staff   = User::factory()->staff()->forCompany($company)->create();

        $this->withoutVite()
            ->actingAs($staff)
            ->get("/{$company->slug}/staff/dogs")
            ->assertInertia(fn ($page) => $page
                ->has('company')
                ->where('company.slug', $company->slug)
                ->where('company.name', $company->name)
            );
    }

    public function test_stripe_ready_flag_is_false_when_not_configured(): void
    {
        $company = Company::factory()->create([
            'slug'                       => 'no-stripe',
            'stripe_onboarding_complete' => false,
        ]);
        $staff = User::factory()->staff()->forCompany($company)->create();

        $this->withoutVite()
            ->actingAs($staff)
            ->get("/{$company->slug}/staff/dogs")
            ->assertInertia(fn ($page) => $page
                ->where('company.stripe_ready', false)
            );
    }

    public function test_stripe_ready_flag_is_true_when_configured(): void
    {
        $company = Company::factory()->stripeReady()->create(['slug' => 'stripe-ok']);
        $staff   = User::factory()->staff()->forCompany($company)->create();

        $this->withoutVite()
            ->actingAs($staff)
            ->get("/{$company->slug}/staff/dogs")
            ->assertInertia(fn ($page) => $page
                ->where('company.stripe_ready', true)
            );
    }
}
