<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PathLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_login_page_renders_for_valid_company(): void
    {
        $company = Company::factory()->create(['slug' => 'test-kennel']);

        $response = $this->get("/{$company->slug}/login");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_tenant_login_page_returns_404_for_invalid_company(): void
    {
        $response = $this->get('/nonexistent/login');

        $response->assertStatus(404);
    }

    public function test_staff_can_login_with_correct_company(): void
    {
        $company = Company::factory()->create(['slug' => 'test-kennel']);
        $staff   = User::factory()->staff()->forCompany($company)->create([
            'email' => 'staff@test.com',
        ]);

        $response = $this->post("/{$company->slug}/login", [
            'email'    => 'staff@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect("/{$company->slug}/staff/dashboard");
    }

    public function test_user_cannot_login_with_wrong_company(): void
    {
        $companyA = Company::factory()->create(['slug' => 'company-a']);
        $companyB = Company::factory()->create(['slug' => 'company-b']);

        User::factory()->staff()->forCompany($companyA)->create([
            'email' => 'staff@company-a.com',
        ]);

        // Try to log into company B with company A credentials
        $this->post("/{$companyB->slug}/login", [
            'email'    => 'staff@company-a.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_owner_is_redirected_to_owner_dashboard_after_login(): void
    {
        $company = Company::factory()->create(['slug' => 'test-kennel']);
        $owner   = User::factory()->owner()->forCompany($company)->create([
            'email' => 'owner@test.com',
        ]);

        $response = $this->post("/{$company->slug}/login", [
            'email'    => 'owner@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect("/{$company->slug}/owner/dashboard");
    }

    public function test_tenant_logout_redirects_to_company_login(): void
    {
        $company = Company::factory()->create(['slug' => 'test-kennel']);
        $staff   = User::factory()->staff()->forCompany($company)->create();

        $response = $this->actingAs($staff)
            ->post("/{$company->slug}/logout");

        $this->assertGuest();
        $response->assertRedirect("/{$company->slug}/login");
    }
}
