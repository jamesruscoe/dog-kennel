<?php

namespace App\Http\Controllers\Kennel;

use App\Http\Controllers\Controller;
use App\Http\Resources\KennelSettingsResource;
use App\Models\KennelSettings;
use App\Services\CapacityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __construct(private readonly CapacityService $capacityService) {}

    public function index(): Response
    {
        $settings = KennelSettings::sole();

        return Inertia::render('Staff/Calendar/Index', [
            'settings' => new KennelSettingsResource($settings),
        ]);
    }

    /**
     * Return occupancy data for a given month (used by the calendar component via fetch).
     */
    public function occupancy(Request $request): JsonResponse
    {
        $request->validate([
            'year'  => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $from = Carbon::create($request->integer('year'), $request->integer('month'), 1)->startOfMonth();
        $to   = $from->copy()->endOfMonth();

        $occupancy = $this->capacityService->occupancyByDate($from, $to);
        $max       = $this->capacityService->maxCapacity();

        return response()->json([
            'occupancy'    => $occupancy,
            'max_capacity' => $max,
        ]);
    }
}
