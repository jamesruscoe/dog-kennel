<?php

namespace App\Http\Controllers;

use App\Services\CompanyCreationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class CompanySignupController extends Controller
{
    public function __construct(
        private readonly CompanyCreationService $companyCreationService,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Signup');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::defaults()],
        ]);

        $company = $this->companyCreationService->create($validated);

        // Log in the newly created admin user
        Auth::login(
            $company->users()->where('email', $validated['email'])->firstOrFail()
        );

        return redirect("/{$company->slug}/staff/dashboard")
            ->with('success', "Welcome to Dog Desk! Your kennel '{$company->name}' is ready.");
    }
}
