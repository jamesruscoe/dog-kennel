<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\DogResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $owner = $request->user()->owner;

        // TODO: JOB 4 — load actual data for the owner portal dashboard
        return Inertia::render('Owner/Dashboard', [
            'dogs'            => DogResource::collection(
                $owner?->dogs()->withCount('bookings')->latest()->get() ?? collect()
            ),
            'activeBookings'  => BookingResource::collection(
                $owner?->activeBookings()->with('dog')->latest()->get() ?? collect()
            ),
        ]);
    }
}
