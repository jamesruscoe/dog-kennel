<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyContext;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TenantAuthenticatedSessionController extends Controller
{
    public function create(Request $request): Response
    {
        $company = app(CompanyContext::class);

        return Inertia::render('Auth/Login', [
            'canResetPassword' => true,
            'status'           => session('status'),
            'company'          => ['name' => $company->name, 'slug' => $company->slug],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = app(CompanyContext::class);

        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Scope authentication to this company
        if (! Auth::attempt([
            'email'      => $request->email,
            'password'   => $request->password,
            'company_id' => $company->id,
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records for this company.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        // Redirect based on role
        if ($user->isStaffOrAdmin()) {
            return redirect()->route('staff.dashboard', ['company' => $company->slug]);
        }

        return redirect()->route('owner.dashboard', ['company' => $company->slug]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $slug = app()->bound(CompanyContext::class)
            ? app(CompanyContext::class)->slug
            : null;

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $slug
            ? redirect("/{$slug}/login")
            : redirect('/');
    }
}
