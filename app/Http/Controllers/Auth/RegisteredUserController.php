<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * Public registration creates dog owner accounts only.
     * Staff accounts are created by existing staff via the staff portal.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Creates a User (role=owner) and an associated Owner profile in one
     * atomic transaction. Phone/address can be completed via profile settings.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => UserRole::Owner->value,
            ]);

            // Create a skeletal Owner profile. The user will be prompted to
            // complete phone / address / emergency contact on first visit.
            Owner::create([
                'user_id' => $user->id,
                'phone'   => null,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        // Route to the owner portal; the `/dashboard` redirect would also work
        // but skipping one hop gives a faster first-load experience.
        return redirect()->route('owner.dashboard');
    }
}
