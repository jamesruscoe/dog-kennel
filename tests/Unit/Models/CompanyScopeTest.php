<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyContext;
use App\Models\Dog;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_scope_filters_dogs_to_resolved_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $ownerA = Owner::factory()->forCompany($companyA)->create();
        $ownerB = Owner::factory()->forCompany($companyB)->create();

        Dog::factory()->forCompany($companyA)->create(['owner_id' => $ownerA->id]);
        Dog::factory()->forCompany($companyA)->create(['owner_id' => $ownerA->id]);
        Dog::factory()->forCompany($companyB)->create(['owner_id' => $ownerB->id]);

        // Bind Company A as context
        app()->instance(CompanyContext::class, $companyA);

        $this->assertCount(2, Dog::all());

        // Switch to Company B
        app()->instance(CompanyContext::class, $companyB);

        $this->assertCount(1, Dog::all());

        app()->forgetInstance(CompanyContext::class);
    }

    public function test_global_scope_does_not_apply_without_company_context(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $ownerA = Owner::factory()->forCompany($companyA)->create();
        $ownerB = Owner::factory()->forCompany($companyB)->create();

        Dog::factory()->forCompany($companyA)->create(['owner_id' => $ownerA->id]);
        Dog::factory()->forCompany($companyB)->create(['owner_id' => $ownerB->id]);

        // No company context bound — scope inactive
        app()->forgetInstance(CompanyContext::class);

        $this->assertCount(2, Dog::all());
    }

    public function test_super_admin_bypasses_scope(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $superAdmin = User::factory()->superAdmin()->create();

        $ownerA = Owner::factory()->forCompany($companyA)->create();
        $ownerB = Owner::factory()->forCompany($companyB)->create();

        Dog::factory()->forCompany($companyA)->create(['owner_id' => $ownerA->id]);
        Dog::factory()->forCompany($companyB)->create(['owner_id' => $ownerB->id]);

        // Acting as super admin with company A context — should still see both
        // (super admin bypasses scope per BelongsToCompany trait)
        $this->actingAs($superAdmin);
        app()->instance(CompanyContext::class, $companyA);

        // Super admin sees all dogs regardless of company context
        $this->assertCount(2, Dog::withoutGlobalScopes()->get());

        app()->forgetInstance(CompanyContext::class);
    }

    public function test_bookings_are_isolated_per_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $ownerA = Owner::factory()->forCompany($companyA)->create();
        $ownerB = Owner::factory()->forCompany($companyB)->create();

        $dogA = Dog::factory()->forCompany($companyA)->create(['owner_id' => $ownerA->id]);
        $dogB = Dog::factory()->forCompany($companyB)->create(['owner_id' => $ownerB->id]);

        Booking::factory()->forCompany($companyA)->create(['dog_id' => $dogA->id]);
        Booking::factory()->forCompany($companyA)->create(['dog_id' => $dogA->id]);
        Booking::factory()->forCompany($companyB)->create(['dog_id' => $dogB->id]);

        app()->instance(CompanyContext::class, $companyA);
        $this->assertCount(2, Booking::all());

        app()->instance(CompanyContext::class, $companyB);
        $this->assertCount(1, Booking::all());

        app()->forgetInstance(CompanyContext::class);
    }
}
