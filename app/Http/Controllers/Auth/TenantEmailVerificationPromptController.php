<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantEmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $company = app(CompanyContext::class);

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                route('staff.dashboard', ['company' => $company->slug], absolute: false)
            );
        }

        return Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
    }
}
