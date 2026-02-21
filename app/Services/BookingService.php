<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Events\BookingApproved;
use App\Events\BookingCancelled;
use App\Events\BookingCreated;
use App\Exceptions\BookingConflictException;
use App\Exceptions\InvalidBookingDateException;
use App\Exceptions\OperatingDayException;
use App\Models\Booking;
use App\Models\Dog;
use App\Models\KennelSettings;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly CapacityService $capacityService,
    ) {}

    /**
     * Return a paginated list of bookings with eager-loaded relations.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['dog.owner.user'])
            ->withCount('careLogs')
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->whereHas('dog', fn ($d) =>
                    $d->where('name', 'like', "%{$search}%")
                      ->orWhereHas('owner.user', fn ($u) =>
                          $u->where('name', 'like', "%{$search}%")
                      )
                );
            })
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('check_in_date', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('check_out_date', '<=', $d))
            ->latest('check_in_date')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Return bookings for a specific owner, optionally filtered by status.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listForOwner(int $ownerId, array $filters = []): LengthAwarePaginator
    {
        return Booking::query()
            ->whereHas('dog', fn ($q) => $q->where('owner_id', $ownerId))
            ->with(['dog'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->latest('check_in_date')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Create a new booking after validating dates, operating schedule and capacity.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidBookingDateException
     * @throws OperatingDayException
     * @throws BookingConflictException
     */
    public function create(Dog $dog, array $data): Booking
    {
        $checkIn  = Carbon::parse($data['check_in_date']);
        $checkOut = Carbon::parse($data['check_out_date']);

        $this->validateDates($checkIn, $checkOut);
        $this->validateOperatingDays($checkIn, $checkOut);
        $this->validateCapacity($checkIn, $checkOut);

        $settings    = KennelSettings::sole();
        $nights      = $checkIn->diffInDays($checkOut);
        $amountPence = $nights * $settings->nightly_rate_pence;

        return DB::transaction(function () use ($dog, $data, $checkIn, $checkOut, $amountPence) {
            $booking = Booking::create([
                'dog_id'               => $dog->id,
                'check_in_date'        => $checkIn->toDateString(),
                'check_out_date'       => $checkOut->toDateString(),
                'status'               => BookingStatus::Pending->value,
                'notes'                => $data['notes'] ?? null,
                'special_requirements' => $data['special_requirements'] ?? null,
                'amount_pence'         => $amountPence,
            ]);

            event(new BookingCreated($booking));

            return $booking;
        });
    }

    /**
     * Approve a pending booking (with capacity re-check).
     */
    public function approve(Booking $booking): Booking
    {
        $this->assertCanTransition($booking, BookingStatus::Approved);

        $checkIn  = Carbon::parse($booking->check_in_date);
        $checkOut = Carbon::parse($booking->check_out_date);
        $this->validateCapacity($checkIn, $checkOut, $booking->id);

        $booking->update(['status' => BookingStatus::Approved->value]);
        event(new BookingApproved($booking->fresh('dog.owner.user')));

        return $booking;
    }

    /**
     * Reject a pending booking with an optional reason.
     */
    public function reject(Booking $booking, ?string $reason = null): Booking
    {
        $this->assertCanTransition($booking, BookingStatus::Rejected);

        $booking->update([
            'status'           => BookingStatus::Rejected->value,
            'rejection_reason' => $reason,
        ]);

        return $booking;
    }

    /**
     * Cancel an active booking (usable by both staff and the booking owner).
     */
    public function cancel(Booking $booking, ?string $reason = null): Booking
    {
        $this->assertCanTransition($booking, BookingStatus::Cancelled);

        $booking->update([
            'status'              => BookingStatus::Cancelled->value,
            'cancellation_reason' => $reason,
        ]);

        event(new BookingCancelled($booking->fresh('dog.owner.user')));

        return $booking;
    }

    /**
     * Mark an approved booking as completed.
     */
    public function complete(Booking $booking): Booking
    {
        $this->assertCanTransition($booking, BookingStatus::Completed);

        $booking->update(['status' => BookingStatus::Completed->value]);

        return $booking;
    }

    // -------------------------------------------------------------------------
    // Private validation helpers
    // -------------------------------------------------------------------------

    private function validateDates(Carbon $checkIn, Carbon $checkOut): void
    {
        if ($checkOut->lte($checkIn)) {
            throw new InvalidBookingDateException('Check-out must be after check-in.');
        }

        if ($checkIn->isPast() && !$checkIn->isToday()) {
            throw new InvalidBookingDateException('Check-in date cannot be in the past.');
        }

        $settings = KennelSettings::sole();
        if ($settings->booking_lead_days > 0) {
            $minDate = today()->addDays($settings->booking_lead_days);
            if ($checkIn->lt($minDate)) {
                throw new InvalidBookingDateException(
                    "Bookings must be made at least {$settings->booking_lead_days} day(s) in advance."
                );
            }
        }
    }

    private function validateOperatingDays(Carbon $checkIn, Carbon $checkOut): void
    {
        $settings = KennelSettings::sole();

        if (!$settings->isOperatingDay($checkIn->isoWeekday())) {
            throw new OperatingDayException(
                "The kennel does not accept check-ins on {$checkIn->format('l')}s."
            );
        }

        if (!$settings->isOperatingDay($checkOut->isoWeekday())) {
            throw new OperatingDayException(
                "The kennel does not accept check-outs on {$checkOut->format('l')}s."
            );
        }
    }

    private function validateCapacity(Carbon $checkIn, Carbon $checkOut, ?int $excludeId = null): void
    {
        if (!$this->capacityService->isAvailable($checkIn, $checkOut, $excludeId)) {
            throw new BookingConflictException('No capacity available for the requested dates.');
        }
    }

    private function assertCanTransition(Booking $booking, BookingStatus $next): void
    {
        $current = $booking->status instanceof BookingStatus
            ? $booking->status
            : BookingStatus::from($booking->status);

        if (!$current->canTransitionTo($next)) {
            throw new \LogicException(
                "Cannot transition booking from {$current->label()} to {$next->label()}."
            );
        }
    }
}
