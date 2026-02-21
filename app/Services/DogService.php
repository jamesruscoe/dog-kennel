<?php

namespace App\Services;

use App\Models\Dog;
use App\Models\Owner;
use Illuminate\Pagination\LengthAwarePaginator;

class DogService
{
    /**
     * Paginated, filterable dog list.
     *
     * Supported filters:
     *   search (string)  — matches dog name
     *   breed  (string)  — partial breed match
     *   owner_id (int)   — scope to a single owner
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = [], ?Owner $owner = null): LengthAwarePaginator
    {
        return Dog::query()
            ->with('owner.user')
            ->withCount('bookings')
            ->when($owner, fn ($q) => $q->where('owner_id', $owner->id))
            ->when($filters['owner_id'] ?? null, fn ($q, $id) => $q->where('owner_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($filters['breed'] ?? null, fn ($q, $b) => $q->where('breed', 'like', "%{$b}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Create a dog under the given owner.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Owner $owner, array $data): Dog
    {
        return $owner->dogs()->create($data);
    }

    /**
     * Update an existing dog's information.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Dog $dog, array $data): Dog
    {
        $dog->update($data);

        return $dog->fresh('owner.user');
    }

    /**
     * Soft-delete a dog.
     * Refused when active bookings exist.
     *
     * @throws \RuntimeException
     */
    public function delete(Dog $dog): void
    {
        $active = $dog->bookings()->whereIn('status', ['pending', 'approved'])->count();

        if ($active > 0) {
            throw new \RuntimeException(
                "Cannot remove {$dog->name}: {$active} active booking(s) must be resolved first."
            );
        }

        $dog->delete();
    }
}
