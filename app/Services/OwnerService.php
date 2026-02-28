<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\CompanyContext;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OwnerService
{
    /**
     * Paginated, filterable owner list for the staff portal.
     *
     * Supported filters:
     *   search (string)  — matches name, email, or phone
     *   active (bool)    — only owners with pending/approved bookings
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Owner::query()
            ->with('user')
            ->withCount('dogs')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn ($u) =>
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                    )->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['active'] ?? false, fn ($q) => $q->withActiveBookings())
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Create a new owner with a pre-verified user account.
     * Used by staff — phone is required via StoreOwnerRequest.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Owner
    {
        return DB::transaction(function () use ($data) {
            // User does not use BelongsToCompany, so company_id must be stamped here.
            $user = User::create([
                'company_id'        => app()->bound(CompanyContext::class)
                    ? app(CompanyContext::class)->id
                    : null,
                'name'              => $data['name'],
                'email'             => $data['email'],
                'password'          => bcrypt($data['password'] ?? str()->random(16)),
                'role'              => UserRole::Owner->value,
                'email_verified_at' => now(),
            ]);

            return Owner::create([
                'user_id'                 => $user->id,
                'phone'                   => $data['phone'] ?? null,
                'address'                 => $data['address'] ?? null,
                'emergency_contact_name'  => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'notes'                   => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Update an owner's profile.
     * Name and email changes propagate to the linked users row.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Owner $owner, array $data): Owner
    {
        DB::transaction(function () use ($owner, $data) {
            $userFields = Arr::only($data, ['name', 'email']);
            if ($userFields) {
                $owner->user->update($userFields);
            }

            $owner->update(Arr::except($data, ['name', 'email']));
        });

        return $owner->fresh('user');
    }

    /**
     * Soft-delete an owner.
     * Refused when active (pending/approved) bookings exist.
     *
     * @throws \RuntimeException
     */
    public function delete(Owner $owner): void
    {
        $active = $owner->activeBookings()->count();

        if ($active > 0) {
            throw new \RuntimeException(
                "Cannot remove owner: {$active} active booking(s) must be resolved first."
            );
        }

        $owner->delete();
    }
}
