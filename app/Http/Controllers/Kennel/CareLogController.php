<?php

namespace App\Http\Controllers\Kennel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kennel\StoreCareLogRequest;
use App\Http\Requests\Kennel\UpdateCareLogRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\CareLogResource;
use App\Models\Booking;
use App\Models\CareLog;
use App\Services\CareLogMediaService;
use App\Services\CareLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareLogController extends Controller
{
    public function __construct(
        private readonly CareLogService $careLogService,
        private readonly CareLogMediaService $careLogMediaService,
    ) {}

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
        $careLog = $this->careLogService->log(
            $booking,
            $request->user()->id,
            $request->validated()
        );

        if ($request->hasFile('images')) {
            $this->careLogMediaService->attach($careLog, $request->file('images'));
        }

        return back()->with('success', 'Care activity logged.');
    }

    public function show(CareLog $careLog): Response
    {
        $careLog->load(['booking.dog.owner.user', 'loggedByUser', 'media']);

        return Inertia::render('Staff/CareLogs/Show', [
            'careLog' => new CareLogResource($careLog),
        ]);
    }

    public function edit(CareLog $careLog): Response
    {
        $careLog->load(['booking.dog.owner.user', 'loggedByUser', 'media']);

        return Inertia::render('Staff/CareLogs/Edit', [
            'careLog' => new CareLogResource($careLog),
        ]);
    }

    public function update(UpdateCareLogRequest $request, CareLog $careLog): RedirectResponse
    {
        $this->careLogService->update($careLog, $request->safe()->only([
            'activity_type', 'notes', 'occurred_at',
        ]));

        // Delete removed media
        if ($request->filled('delete_media_ids')) {
            foreach ($request->input('delete_media_ids') as $mediaId) {
                $media = $careLog->media()->find($mediaId);
                if ($media) {
                    $this->careLogMediaService->delete($media);
                }
            }
        }

        // Attach new images
        if ($request->hasFile('images')) {
            $this->careLogMediaService->attach($careLog, $request->file('images'));
        }

        return redirect()->route('staff.care-logs.show', [
            'company' => app(\App\Models\CompanyContext::class)->slug,
            'careLog' => $careLog,
        ])->with('success', 'Care log updated.');
    }

    public function destroy(CareLog $careLog): RedirectResponse
    {
        $this->careLogService->delete($careLog);

        return back()->with('success', 'Log entry removed.');
    }
}
