<?php

namespace App\Http\Controllers\Kennel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kennel\UpdateKennelSettingsRequest;
use App\Http\Resources\KennelSettingsResource;
use App\Models\KennelSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class KennelSettingsController extends Controller
{
    public function edit(): Response
    {
        $settings = KennelSettings::sole();

        return Inertia::render('Staff/Settings/Index', [
            'settings' => new KennelSettingsResource($settings),
        ]);
    }

    public function update(UpdateKennelSettingsRequest $request): RedirectResponse
    {
        KennelSettings::sole()->update($request->validated());

        return back()->with('success', 'Settings saved.');
    }
}
