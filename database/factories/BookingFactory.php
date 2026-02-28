<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Dog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn  = Carbon::instance(fake()->dateTimeBetween('-2 months', '+3 months'));
        $nights   = fake()->numberBetween(1, 14);
        $checkOut = $checkIn->copy()->addDays($nights);

        return [
            'company_id'           => Company::factory(),
            'dog_id'               => Dog::factory(),
            'check_in_date'        => $checkIn->toDateString(),
            'check_out_date'       => $checkOut->toDateString(),
            'status'               => BookingStatus::Pending->value,
            'notes'                => fake()->optional(0.4)->sentence(),
            'special_requirements' => fake()->optional(0.2)->sentence(),
            'rejection_reason'     => null,
            'cancellation_reason'  => null,
            'amount_pence'         => $nights * 3500,
            'payment_status'       => null,
        ];
    }

    // ── Status states ────────────────────────────────────────────────────────

    public function pending(): static
    {
        return $this->state(['status' => BookingStatus::Pending->value]);
    }

    public function approved(): static
    {
        return $this->state(['status' => BookingStatus::Approved->value]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status'           => BookingStatus::Rejected->value,
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status'              => BookingStatus::Cancelled->value,
            'cancellation_reason' => fake()->sentence(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(['status' => BookingStatus::Completed->value]);
    }

    // ── Date helpers ──────────────────────────────────────────────────────────

    public function upcoming(): static
    {
        return $this->state(function () {
            $checkIn  = Carbon::now()->addDays(fake()->numberBetween(1, 30));
            $checkOut = $checkIn->copy()->addDays(fake()->numberBetween(1, 7));
            return [
                'check_in_date'  => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
            ];
        });
    }

    public function currentlyBoarded(): static
    {
        return $this->state(function () {
            $checkIn  = Carbon::now()->subDays(fake()->numberBetween(1, 3));
            $checkOut = Carbon::now()->addDays(fake()->numberBetween(1, 5));
            return [
                'check_in_date'  => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'status'         => BookingStatus::Approved->value,
            ];
        });
    }

    public function past(): static
    {
        return $this->state(function () {
            $checkOut = Carbon::now()->subDays(fake()->numberBetween(1, 90));
            $checkIn  = $checkOut->copy()->subDays(fake()->numberBetween(1, 10));
            return [
                'check_in_date'  => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'status'         => BookingStatus::Completed->value,
            ];
        });
    }

    public function forCompany(Company $company): static
    {
        return $this->state(['company_id' => $company->id]);
    }
}
