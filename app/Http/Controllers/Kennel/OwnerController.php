<?php

namespace App\Http\Controllers\Kennel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kennel\StoreOwnerRequest;
use App\Http\Requests\Kennel\UpdateOwnerRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\DogResource;
use App\Http\Resources\OwnerResource;
use App\Models\Owner;
use App\Services\OwnerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController extends Controller
{
    public function __construct(private readonly OwnerService $ownerService) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'active']);

        return Inertia::render('Staff/Owners/Index', [
            'owners'  => OwnerResource::collection($this->ownerService->list($filters)),
            'filters' => $filters,
        ]);
    }

    public function show(Owner $owner): Response
    {
        $owner->load(['user', 'dogs']);

        $recentBookings = $owner->bookings()
            ->with('dog')
            ->latest('check_in_date')
            ->take(5)
            ->get();

        return Inertia::render('Staff/Owners/Show', [
            'owner'          => new OwnerResource($owner),
            'recentBookings' => BookingResource::collection($recentBookings),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Staff/Owners/Create');
    }

    public function store(StoreOwnerRequest $request): RedirectResponse
    {
        $owner = $this->ownerService->create($request->validated());

        return redirect()
            ->route('staff.owners.show', $owner)
            ->with('success', "{$request->name} has been added as an owner.");
    }

    public function edit(Owner $owner): Response
    {
        $owner->load('user');

        return Inertia::render('Staff/Owners/Edit', [
            'owner' => new OwnerResource($owner),
        ]);
    }

    public function update(UpdateOwnerRequest $request, Owner $owner): RedirectResponse
    {
        $this->ownerService->update($owner, $request->validated());

        return redirect()
            ->route('staff.owners.show', $owner)
            ->with('success', 'Owner updated successfully.');
    }

    public function destroy(Owner $owner): RedirectResponse
    {
        try {
            $this->ownerService->delete($owner);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('staff.owners.index')
            ->with('success', 'Owner removed.');
    }
}
