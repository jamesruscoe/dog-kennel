<?php

namespace App\Http\Controllers\Kennel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kennel\StoreDogRequest;
use App\Http\Requests\Kennel\UpdateDogRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\DogResource;
use App\Http\Resources\OwnerResource;
use App\Models\Dog;
use App\Models\Owner;
use App\Services\DogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DogController extends Controller
{
    public function __construct(private readonly DogService $dogService) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'breed', 'owner_id']);

        return Inertia::render('Staff/Dogs/Index', [
            'dogs'    => DogResource::collection($this->dogService->list($filters)),
            'filters' => $filters,
        ]);
    }

    public function show(Dog $dog): Response
    {
        $dog->load('owner.user');

        $bookings = $dog->bookings()
            ->with('careLogs')
            ->latest('check_in_date')
            ->take(10)
            ->get();

        return Inertia::render('Staff/Dogs/Show', [
            'dog'      => new DogResource($dog),
            'bookings' => BookingResource::collection($bookings),
        ]);
    }

    public function create(Request $request): Response
    {
        $selectedOwner = $request->owner_id
            ? Owner::with('user')->find($request->owner_id)
            : null;

        return Inertia::render('Staff/Dogs/Create', [
            'owners'        => OwnerResource::collection(Owner::with('user')->orderBy('id')->get()),
            'selectedOwner' => $selectedOwner ? new OwnerResource($selectedOwner) : null,
        ]);
    }

    public function store(StoreDogRequest $request): RedirectResponse
    {
        $owner = Owner::findOrFail($request->validated('owner_id'));
        $data  = collect($request->validated())->except('owner_id')->toArray();
        $dog   = $this->dogService->create($owner, $data);

        return redirect()
            ->route('staff.dogs.show', ['company' => app(\App\Models\CompanyContext::class)->slug, 'dog' => $dog])
            ->with('success', "{$dog->name} has been added.");
    }

    public function edit(Dog $dog): Response
    {
        $dog->load('owner.user');

        return Inertia::render('Staff/Dogs/Edit', [
            'dog'    => new DogResource($dog),
            'owners' => OwnerResource::collection(Owner::with('user')->orderBy('id')->get()),
        ]);
    }

    public function update(UpdateDogRequest $request, Dog $dog): RedirectResponse
    {
        $this->dogService->update($dog, $request->validated());

        return redirect()
            ->route('staff.dogs.show', ['company' => app(\App\Models\CompanyContext::class)->slug, 'dog' => $dog])
            ->with('success', "{$dog->name} updated successfully.");
    }

    public function destroy(Dog $dog): RedirectResponse
    {
        try {
            $this->dogService->delete($dog);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('staff.dogs.index', ['company' => app(\App\Models\CompanyContext::class)->slug])
            ->with('success', 'Dog removed.');
    }
}
