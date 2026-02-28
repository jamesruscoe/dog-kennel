<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantWelcomeController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $company = app(CompanyContext::class);
        $user    = $request->user();

        // Authenticated company member — send them straight to their portal
        if ($user && $user->company_id === $company->id) {
            if ($user->isOwner()) {
                return redirect()->route('owner.dashboard', ['company' => $company->slug]);
            }

            return redirect()->route('staff.dashboard', ['company' => $company->slug]);
        }

        return Inertia::render('Tenant/Welcome');
    }
}
