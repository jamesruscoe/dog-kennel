<?php

namespace App\Http\Controllers\Kennel;

use App\Exceptions\BookingConflictException;
use App\Exceptions\InvalidBookingDateException;
use App\Exceptions\OperatingDayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kennel\StoreBookingRequest;
use App\Http\Requests\Kennel\UpdateBookingRequest;
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

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function index(Request $request): Response
    {
        $filters  = $request->only(['status', 'search', 'date_from', 'date_to']);
        $bookings = $this->bookingService->list($filters);

        return Inertia::render('Staff/Bookings/Index', [
            'bookings' => BookingResource::collection($bookings),
            'filters'  => $filters,
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->load(['dog.owner.user', 'careLogs.loggedByUser', 'payment']);

        return Inertia::render('Staff/Bookings/Show', [
            'booking' => new BookingResource($booking),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Staff/Bookings/Create', [
            'dogs'     => DogResource::collection(Dog::with('owner.user')->orderBy('name')->get()),
            'settings' => new KennelSettingsResource(KennelSettings::sole()),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $dog = Dog::findOrFail($request->validated('dog_id'));

        try {
            $booking = $this->bookingService->create($dog, $request->validated());
        } catch (InvalidBookingDateException|OperatingDayException|BookingConflictException $e) {
            return back()->withErrors(['booking' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('staff.bookings.show', ['company' => app(\App\Models\CompanyContext::class)->slug, 'booking' => $booking])
            ->with('success', 'Booking created and pending approval.');
    }

    public function approve(Booking $booking): RedirectResponse
    {
        $this->bookingService->approve($booking);

        return back()->with('success', 'Booking approved.');
    }

    public function reject(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->bookingService->reject($booking, $request->validated('rejection_reason'));

        return back()->with('success', 'Booking rejected.');
    }

    public function cancel(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->bookingService->cancel($booking, $request->validated('cancellation_reason'));

        return back()->with('success', 'Booking cancelled.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        try {
            $this->bookingService->complete($booking);
        } catch (\LogicException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        return back()->with('success', 'Booking marked as completed.');
    }
}
