<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kennel\StoreBookingRequest;
use App\Http\Requests\Owner\CancelBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\DogResource;
use App\Http\Resources\KennelSettingsResource;
use App\Models\Booking;
use App\Models\Dog;
use App\Models\KennelSettings;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerBookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function index(Request $request): Response
    {
        $owner   = $request->user()->owner;
        $filters = $request->only(['status']);

        $bookings = $owner
            ? $this->bookingService->listForOwner($owner->id, $filters)
            : Booking::query()->whereRaw('1=0')->paginate(10);

        return Inertia::render('Owner/Bookings/Index', [
            'bookings' => BookingResource::collection($bookings),
            'filters'  => $filters,
        ]);
    }

    public function show(Booking $booking): Response
    {
        $this->authorizeOwnerAccess($booking);

        $booking->load(['dog', 'careLogs.loggedByUser', 'payment']);

        return Inertia::render('Owner/Bookings/Show', [
            'booking'    => new BookingResource($booking),
            'stripe_key' => config('services.stripe.public'),
        ]);
    }

    public function create(Request $request): Response
    {
        $owner = $request->user()->owner;

        return Inertia::render('Owner/Bookings/Create', [
            'dogs'     => DogResource::collection($owner?->dogs ?? collect()),
            'settings' => new KennelSettingsResource(KennelSettings::sole()),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $dog = Dog::findOrFail($request->validated('dog_id'));

        // Ensure the dog belongs to the authenticated owner
        abort_unless(
            $dog->owner_id === $request->user()->owner?->id,
            403,
            'This dog does not belong to your profile.'
        );

        $booking = $this->bookingService->create($dog, $request->validated());

        return redirect()
            ->route('owner.bookings.show', $booking)
            ->with('success', 'Booking request submitted. We will confirm shortly.');
    }

    public function cancel(CancelBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorizeOwnerAccess($booking);

        $this->bookingService->cancel($booking, $request->validated('cancellation_reason'));

        return back()->with('success', 'Booking cancelled.');
    }

    private function authorizeOwnerAccess(Booking $booking): void
    {
        $ownerId = request()->user()->owner?->id;
        abort_unless($booking->dog->owner_id === $ownerId, 403);
    }
}
