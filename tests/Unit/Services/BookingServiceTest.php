<?php

namespace Tests\Unit\Services;

use App\Exceptions\BookingConflictException;
use App\Exceptions\StripeNotConfiguredException;
use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyContext;
use App\Models\Dog;
use App\Models\KennelSettings;
use App\Models\Owner;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $service;
    private Company $company;
    private KennelSettings $settings;
    private Dog $dog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BookingService::class);

        $this->company = Company::factory()->create([
            'stripe_account_id'          => null,
            'stripe_onboarding_complete' => false,
        ]);

        $owner = Owner::factory()->forCompany($this->company)->create();
        $this->dog = Dog::factory()->forCompany($this->company)->create([
            'owner_id'              => $owner->id,
            'vaccination_confirmed' => true,
        ]);

        $this->settings = KennelSettings::create([
            'company_id'         => $this->company->id,
            'max_capacity'       => 2,
            'nightly_rate_pence' => 3500,
            'operating_days'     => [1, 2, 3, 4, 5, 6, 7],
            'check_in_time'      => '10:00',
            'check_out_time'     => '16:00',
            'booking_lead_days'  => 0,
        ]);

        // Bind company context so global scopes work
        app()->instance(CompanyContext::class, $this->company);
    }

    protected function tearDown(): void
    {
        app()->forgetInstance(CompanyContext::class);
        parent::tearDown();
    }

    public function test_booking_throws_when_stripe_not_configured(): void
    {
        $this->expectException(StripeNotConfiguredException::class);

        $this->service->create($this->dog, [
            'check_in_date'  => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(3)->toDateString(),
        ]);
    }

    public function test_booking_succeeds_when_stripe_ready(): void
    {
        $this->company->update([
            'stripe_account_id'          => 'acct_test123',
            'stripe_onboarding_complete' => true,
        ]);

        $booking = $this->service->create($this->dog, [
            'check_in_date'  => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(3)->toDateString(),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($this->company->id, $booking->company_id);
    }

    public function test_booking_throws_on_capacity_exceeded(): void
    {
        $this->company->update([
            'stripe_account_id'          => 'acct_test123',
            'stripe_onboarding_complete' => true,
        ]);

        $checkIn  = Carbon::tomorrow()->toDateString();
        $checkOut = Carbon::tomorrow()->addDays(3)->toDateString();

        // Fill capacity (max = 2)
        $owner2 = Owner::factory()->forCompany($this->company)->create();
        $dog2   = Dog::factory()->forCompany($this->company)->create(['owner_id' => $owner2->id, 'vaccination_confirmed' => true]);
        $dog3   = Dog::factory()->forCompany($this->company)->create(['owner_id' => $owner2->id, 'vaccination_confirmed' => true]);

        $this->service->create($dog2, ['check_in_date' => $checkIn, 'check_out_date' => $checkOut]);
        $this->service->create($dog3, ['check_in_date' => $checkIn, 'check_out_date' => $checkOut]);

        $this->expectException(BookingConflictException::class);

        $this->service->create($this->dog, [
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
        ]);
    }
}
