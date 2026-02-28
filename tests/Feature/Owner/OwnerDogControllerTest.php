<?php

namespace Tests\Feature\Owner;

use App\Models\Company;
use App\Models\Dog;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDogControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $ownerUser;
    private Owner $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company   = Company::factory()->stripeReady()->create();
        $this->ownerUser = User::factory()->owner()->forCompany($this->company)->create();
        $this->owner     = Owner::factory()->forCompany($this->company)->create([
            'user_id' => $this->ownerUser->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_owner_can_view_their_dogs_list(): void
    {
        Dog::factory()->forCompany($this->company)->create([
            'owner_id' => $this->owner->id,
            'name'     => 'Buddy',
        ]);

        $this->actingAs($this->ownerUser)
            ->get("/{$this->company->slug}/owner/dogs")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Owner/Dogs/Index')
                ->has('dogs', 1)
            );
    }

    // -------------------------------------------------------------------------
    // Create / Store — the critical happy path
    // -------------------------------------------------------------------------

    public function test_owner_can_view_create_dog_form(): void
    {
        $this->actingAs($this->ownerUser)
            ->get("/{$this->company->slug}/owner/dogs/create")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Owner/Dogs/Create'));
    }

    public function test_owner_can_store_a_dog_and_is_redirected_to_show(): void
    {
        $response = $this->actingAs($this->ownerUser)
            ->post("/{$this->company->slug}/owner/dogs", [
                'name'                  => 'Biscuit',
                'breed'                 => 'Labrador Retriever',
                'sex'                   => 'male',
                'neutered'              => true,
                'vaccination_confirmed' => false,
            ]);

        $dog = Dog::where('name', 'Biscuit')->first();

        $this->assertNotNull($dog, 'Dog was not created in the database.');
        $this->assertSame($this->company->id, $dog->company_id, 'Dog company_id not stamped correctly.');
        $this->assertSame($this->owner->id, $dog->owner_id, 'Dog owner_id not set correctly.');

        $response->assertRedirect("/{$this->company->slug}/owner/dogs/{$dog->id}");
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->ownerUser)
            ->post("/{$this->company->slug}/owner/dogs", [])
            ->assertSessionHasErrors(['name', 'breed', 'sex', 'neutered', 'vaccination_confirmed']);
    }

    // -------------------------------------------------------------------------
    // Show — proves route model binding resolves the Dog correctly
    // -------------------------------------------------------------------------

    public function test_owner_can_view_their_own_dog(): void
    {
        $dog = Dog::factory()->forCompany($this->company)->create([
            'owner_id' => $this->owner->id,
            'name'     => 'Poppy',
        ]);

        $this->actingAs($this->ownerUser)
            ->get("/{$this->company->slug}/owner/dogs/{$dog->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Owner/Dogs/Show')
                ->where('dog.name', 'Poppy')
            );
    }

    public function test_owner_cannot_view_another_owners_dog(): void
    {
        $otherOwner = Owner::factory()->forCompany($this->company)->create();
        $otherDog   = Dog::factory()->forCompany($this->company)->create([
            'owner_id' => $otherOwner->id,
        ]);

        $this->actingAs($this->ownerUser)
            ->get("/{$this->company->slug}/owner/dogs/{$otherDog->id}")
            ->assertForbidden();
    }

    public function test_owner_cannot_view_a_dog_from_another_company(): void
    {
        $otherCompany = Company::factory()->create();
        $otherOwner   = Owner::factory()->forCompany($otherCompany)->create();
        $otherDog     = Dog::factory()->forCompany($otherCompany)->create([
            'owner_id' => $otherOwner->id,
        ]);

        // SubstituteBindings (global web middleware) runs before ResolveCompanyFromPath
        // (route middleware), so the company scope is not yet active when the dog is
        // resolved. The dog is found but authorizeOwner rejects it — still secure.
        $this->actingAs($this->ownerUser)
            ->get("/{$this->company->slug}/owner/dogs/{$otherDog->id}")
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function test_owner_can_view_edit_form_for_their_dog(): void
    {
        $dog = Dog::factory()->forCompany($this->company)->create([
            'owner_id' => $this->owner->id,
        ]);

        $this->actingAs($this->ownerUser)
            ->get("/{$this->company->slug}/owner/dogs/{$dog->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Owner/Dogs/Edit'));
    }

    public function test_owner_can_update_their_dog(): void
    {
        $dog = Dog::factory()->forCompany($this->company)->create([
            'owner_id' => $this->owner->id,
            'name'     => 'OldName',
        ]);

        $this->actingAs($this->ownerUser)
            ->patch("/{$this->company->slug}/owner/dogs/{$dog->id}", [
                'name'                  => 'NewName',
                'breed'                 => $dog->breed,
                'sex'                   => $dog->sex,
                'neutered'              => $dog->neutered,
                'vaccination_confirmed' => $dog->vaccination_confirmed,
            ])
            ->assertRedirect("/{$this->company->slug}/owner/dogs/{$dog->id}");

        $this->assertSame('NewName', $dog->fresh()->name);
    }

    public function test_owner_cannot_edit_another_owners_dog(): void
    {
        $otherOwner = Owner::factory()->forCompany($this->company)->create();
        $otherDog   = Dog::factory()->forCompany($this->company)->create([
            'owner_id' => $otherOwner->id,
        ]);

        $this->actingAs($this->ownerUser)
            ->get("/{$this->company->slug}/owner/dogs/{$otherDog->id}/edit")
            ->assertForbidden();
    }
}
