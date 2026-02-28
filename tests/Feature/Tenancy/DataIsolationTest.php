<?php

namespace Tests\Feature\Tenancy;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Dog;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_see_another_companys_dogs(): void
    {
        $companyA = Company::factory()->create(['slug' => 'company-a']);
        $companyB = Company::factory()->create(['slug' => 'company-b']);

        $staffA = User::factory()->staff()->forCompany($companyA)->create();

        $ownerA = Owner::factory()->forCompany($companyA)->create();
        $ownerB = Owner::factory()->forCompany($companyB)->create();

        Dog::factory()->forCompany($companyA)->create(['owner_id' => $ownerA->id, 'name' => 'DogA']);
        Dog::factory()->forCompany($companyB)->create(['owner_id' => $ownerB->id, 'name' => 'DogB']);

        $response = $this->actingAs($staffA)
            ->get("/{$companyA->slug}/staff/dogs");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('dogs.data.0.name', 'DogA')
        );
    }

    public function test_owner_cannot_see_another_companys_bookings(): void
    {
        $companyA = Company::factory()->create(['slug' => 'company-a']);
        $companyB = Company::factory()->create(['slug' => 'company-b']);

        $userA = User::factory()->owner()->forCompany($companyA)->create();
        $ownerA = Owner::factory()->forCompany($companyA)->create(['user_id' => $userA->id]);
        $dogA   = Dog::factory()->forCompany($companyA)->create(['owner_id' => $ownerA->id]);

        $userB = User::factory()->owner()->forCompany($companyB)->create();
        $ownerB = Owner::factory()->forCompany($companyB)->create(['user_id' => $userB->id]);
        $dogB   = Dog::factory()->forCompany($companyB)->create(['owner_id' => $ownerB->id]);

        Booking::factory()->forCompany($companyA)->create(['dog_id' => $dogA->id]);
        Booking::factory()->forCompany($companyB)->create(['dog_id' => $dogB->id]);

        $response = $this->actingAs($userA)
            ->get("/{$companyA->slug}/owner/bookings");

        $response->assertStatus(200);

        // Only 1 booking visible — their own company's
        $response->assertInertia(fn ($page) => $page
            ->where('bookings.meta.total', 1)
        );
    }

    public function test_staff_from_company_a_cannot_access_company_b_routes(): void
    {
        $companyA = Company::factory()->create(['slug' => 'company-a']);
        $companyB = Company::factory()->create(['slug' => 'company-b']);

        $staffA = User::factory()->staff()->forCompany($companyA)->create();

        $response = $this->actingAs($staffA)
            ->get("/{$companyB->slug}/staff/dashboard");

        // Should be forbidden — wrong company
        $response->assertStatus(403);
    }

    public function test_resolving_unknown_company_slug_returns_404(): void
    {
        // Use a guest route so auth middleware does not redirect first
        $response = $this->get('/nonexistent-company/login');

        $response->assertStatus(404);
    }
}
