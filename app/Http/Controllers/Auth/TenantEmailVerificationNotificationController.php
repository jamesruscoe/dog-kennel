<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantEmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $company = app(CompanyContext::class);

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                route('staff.dashboard', ['company' => $company->slug], absolute: false)
            );
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
