<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Services\CompanySlugService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySlugServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanySlugService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CompanySlugService::class);
    }

    public function test_generates_lowercase_hyphenated_slug(): void
    {
        $slug = $this->service->generate('Paws & Relax Kennels');

        $this->assertStringNotContainsString(' ', $slug);
        $this->assertStringNotContainsString('&', $slug);
        $this->assertEquals(strtolower($slug), $slug);
    }

    public function test_generates_unique_slug_when_taken(): void
    {
        Company::factory()->create(['slug' => 'pawstay']);

        $slug = $this->service->generateUnique('PawStay');

        $this->assertNotEquals('pawstay', $slug);
        $this->assertStringStartsWith('pawstay', $slug);
    }

    public function test_first_slug_is_used_when_not_taken(): void
    {
        $slug = $this->service->generateUnique('MyKennel');

        $this->assertEquals('mykennel', $slug);
    }

    public function test_is_unique_returns_true_for_available_slug(): void
    {
        $this->assertTrue($this->service->isUnique('brand-new-slug'));
    }

    public function test_is_unique_returns_false_for_taken_slug(): void
    {
        Company::factory()->create(['slug' => 'taken-slug']);

        $this->assertFalse($this->service->isUnique('taken-slug'));
    }

    public function test_is_unique_excludes_given_id(): void
    {
        $company = Company::factory()->create(['slug' => 'existing-slug']);

        // Should return true when checking uniqueness for the same company's own slug
        $this->assertTrue($this->service->isUnique('existing-slug', $company->id));
    }

    public function test_appends_numeric_suffix_on_collision(): void
    {
        Company::factory()->create(['slug' => 'kennel']);

        $slug = $this->service->generateUnique('kennel');

        $this->assertEquals('kennel-2', $slug);
    }

    public function test_increments_suffix_through_multiple_collisions(): void
    {
        Company::factory()->create(['slug' => 'kennel']);
        Company::factory()->create(['slug' => 'kennel-2']);

        $slug = $this->service->generateUnique('kennel');

        $this->assertEquals('kennel-3', $slug);
    }
}
