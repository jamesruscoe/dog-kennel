<?php

namespace App\Http\Controllers\Kennel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kennel\StoreCareLogRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\CareLogResource;
use App\Models\Booking;
use App\Models\CareLog;
use App\Services\CareLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareLogController extends Controller
{
    public function __construct(private readonly CareLogService $careLogService) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['activity_type', 'date_from', 'date_to']);
        $logs    = $this->careLogService->list($filters);

        return Inertia::render('Staff/CareLogs/Index', [
            'logs'    => CareLogResource::collection($logs),
            'filters' => $filters,
        ]);
    }

    public function create(Booking $booking): Response
    {
        $booking->load(['dog.owner.user']);

        return Inertia::render('Staff/CareLogs/Create', [
            'booking' => new BookingResource($booking),
        ]);
    }

    public function store(StoreCareLogRequest $request, Booking $booking): RedirectResponse
    {
        $this->careLogService->log(
            $booking,
            $request->user()->id,
            $request->validated()
        );

        return back()->with('success', 'Care activity logged.');
    }

    public function destroy(CareLog $careLog): RedirectResponse
    {
        $this->careLogService->delete($careLog);

        return back()->with('success', 'Log entry removed.');
    }
}
