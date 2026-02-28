<?php

namespace Tests\Feature\Finance;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_finance_page(): void
    {
        $company = Company::factory()->create(['slug' => 'test-kennel']);
        $admin   = User::factory()->admin()->forCompany($company)->create();

        $this->withoutVite()
            ->actingAs($admin)
            ->get("/{$company->slug}/staff/finance")
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Staff/Finance/Index'));
    }

    public function test_staff_cannot_access_finance_page(): void
    {
        $company = Company::factory()->create(['slug' => 'test-kennel']);
        $staff   = User::factory()->staff()->forCompany($company)->create();

        $this->actingAs($staff)
            ->get("/{$company->slug}/staff/finance")
            ->assertStatus(403);
    }

    public function test_owner_cannot_access_finance_page(): void
    {
        $company = Company::factory()->create(['slug' => 'test-kennel']);
        $owner   = User::factory()->owner()->forCompany($company)->create();

        $this->actingAs($owner)
            ->get("/{$company->slug}/staff/finance")
            ->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_from_finance(): void
    {
        $company = Company::factory()->create(['slug' => 'test-kennel']);

        $this->get("/{$company->slug}/staff/finance")
            ->assertRedirect('/login');
    }

    public function test_finance_page_returns_company_scoped_stats(): void
    {
        $companyA = Company::factory()->create(['slug' => 'company-a']);
        $companyB = Company::factory()->create(['slug' => 'company-b']);

        $admin = User::factory()->admin()->forCompany($companyA)->create();

        $this->withoutVite()
            ->actingAs($admin)
            ->get("/{$companyA->slug}/staff/finance")
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->has('stats')->has('recentPayments'));
    }
}
