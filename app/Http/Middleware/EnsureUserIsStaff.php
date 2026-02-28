<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->role?->isStaffOrAdmin()) {
            abort(403, 'Access restricted to staff.');
        }

        // Verify user belongs to the resolved tenant company
        if (app()->bound(CompanyContext::class)) {
            $company = app(CompanyContext::class);
            if ($user->company_id !== $company->id) {
                abort(403, 'You do not belong to this company.');
            }
        }

        return $next($request);
    }
}
