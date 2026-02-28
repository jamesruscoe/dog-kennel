<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Company;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PlatformController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Platform/Dashboard', [
            'stats' => [
                'total_companies' => Company::count(),
                'total_users'     => User::whereNotNull('company_id')->count(),
                'total_bookings'  => Booking::withoutGlobalScopes()->count(),
            ],
            'companies' => Company::withCount(['users', 'bookings' => fn ($q) => $q->withoutGlobalScopes()])
                ->latest()
                ->get()
                ->map(fn ($company) => [
                    'id'           => $company->id,
                    'name'         => $company->name,
                    'slug'         => $company->slug,
                    'stripe_ready' => $company->isStripeReady(),
                    'users_count'  => $company->users_count,
                    'bookings_count' => $company->bookings_count,
                    'created_at'   => $company->created_at->toDateString(),
                ]),
        ]);
    }
}
