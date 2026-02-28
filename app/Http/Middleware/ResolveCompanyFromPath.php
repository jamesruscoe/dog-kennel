<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ResolveCompanyFromPath
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('company');

        if (! $slug) {
            return $next($request);
        }

        $company = Company::where('slug', $slug)->first();

        if (! $company) {
            abort(404, 'Company not found.');
        }

        // Bind into DI container so BelongsToCompany global scope can read it
        app()->instance(CompanyContext::class, $company);

        // Share with all Inertia pages
        Inertia::share('company', [
            'id'           => $company->id,
            'name'         => $company->name,
            'slug'         => $company->slug,
            'stripe_ready' => $company->isStripeReady(),
        ]);

        return $next($request);
    }
}
