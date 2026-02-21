<?php

namespace App\Http\Controllers\Staff;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manages staff user accounts.
 * Only existing staff can create or deactivate other staff accounts.
 * Owners are created through OwnerController (which creates both User + Owner).
 */
class StaffUserController extends Controller
{
    public function index(): Response
    {
        $staffUsers = User::where('role', UserRole::Staff->value)
            ->latest()
            ->get(['id', 'name', 'email', 'created_at']);

        return Inertia::render('Staff/Users/Index', [
            'staffUsers' => $staffUsers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Staff/Users/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => UserRole::Staff->value,
            'email_verified_at' => now(), // Staff accounts are pre-verified
        ]);

        return redirect()
            ->route('staff.users.index')
            ->with('success', 'Staff account created successfully.');
    }

    /**
     * Permanently remove a staff user.
     * Cannot remove yourself.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if(
            $user->id === $request->user()->id,
            403,
            'You cannot delete your own account from here.'
        );

        abort_unless($user->isStaff(), 404);

        $user->delete();

        return redirect()
            ->route('staff.users.index')
            ->with('success', 'Staff account removed.');
    }
}
