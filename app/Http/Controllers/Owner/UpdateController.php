<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\CareLogResource;
use App\Models\CareLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UpdateController extends Controller
{
    public function show(Request $request, CareLog $careLog): Response
    {
        Gate::authorize('view', $careLog);

        $careLog->load(['booking.dog.owner.user', 'loggedByUser', 'media']);

        return Inertia::render('Staff/CareLogs/Show', [
            'careLog' => new CareLogResource($careLog),
        ]);
    }

    public function index(Request $request): Response
    {
        $owner = $request->user()->owner;
        $dogIds = $owner?->dogs()->pluck('dogs.id') ?? collect();

        $filters = $request->only(['type']);

        $logs = CareLog::query()
            ->with(['media', 'booking.dog', 'loggedByUser'])
            ->whereHas('booking', fn ($q) => $q->whereIn('dog_id', $dogIds))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('activity_type', $type))
            ->latest('occurred_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Owner/Updates/Index', [
            'logs'    => CareLogResource::collection($logs),
            'filters' => $filters,
        ]);
    }
}
