<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;

        if (! in_array($role, [UserRole::Admin, UserRole::SuperAdmin])) {
            abort(403, 'Access restricted to administrators.');
        }

        return $next($request);
    }
}
