<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\DogResource;
use App\Models\Dog;
use App\Services\DogService;
use App\Http\Requests\Kennel\StoreDogRequest;
use App\Http\Requests\Kennel\UpdateDogRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerDogController extends Controller
{
    public function __construct(private readonly DogService $dogService) {}

    public function index(Request $request): Response
    {
        $owner = $request->user()->owner;

        return Inertia::render('Owner/Dogs/Index', [
            'dogs' => DogResource::collection(
                $owner?->dogs()->withCount('bookings')->orderBy('name')->get() ?? collect()
            ),
        ]);
    }

    public function show(Request $request, Dog $dog): Response
    {
        $this->authorizeOwner($request, $dog);

        $dog->load('owner.user');

        $bookings = $dog->bookings()
            ->with('careLogs')
            ->latest('check_in_date')
            ->take(5)
            ->get();

        return Inertia::render('Owner/Dogs/Show', [
            'dog'      => new DogResource($dog),
            'bookings' => BookingResource::collection($bookings),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owner/Dogs/Create');
    }

    public function store(StoreDogRequest $request): RedirectResponse
    {
        $owner = $request->user()->owner;
        abort_unless($owner !== null, 403, 'No owner profile found.');

        $data = collect($request->validated())->except('owner_id')->toArray();
        $dog  = $this->dogService->create($owner, $data);

        return redirect()
            ->route('owner.dogs.show', ['company' => app(\App\Models\CompanyContext::class)->slug, 'dog' => $dog])
            ->with('success', "{$dog->name} has been added to your profile.");
    }

    public function edit(Request $request, Dog $dog): Response
    {
        $this->authorizeOwner($request, $dog);

        return Inertia::render('Owner/Dogs/Edit', [
            'dog' => new DogResource($dog),
        ]);
    }

    public function update(UpdateDogRequest $request, Dog $dog): RedirectResponse
    {
        $this->authorizeOwner($request, $dog);

        $this->dogService->update($dog, $request->validated());

        return redirect()
            ->route('owner.dogs.show', ['company' => app(\App\Models\CompanyContext::class)->slug, 'dog' => $dog])
            ->with('success', "{$dog->name} updated successfully.");
    }

    private function authorizeOwner(Request $request, Dog $dog): void
    {
        abort_unless(
            $dog->owner_id === $request->user()->owner?->id,
            403,
            'This dog does not belong to your profile.'
        );
    }
}
