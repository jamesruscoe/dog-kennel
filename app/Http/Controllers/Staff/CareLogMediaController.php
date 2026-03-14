<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreCareLogMediaRequest;
use App\Models\CareLog;
use App\Models\CompanyContext;
use App\Services\CareLogMediaService;
use Illuminate\Http\RedirectResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CareLogMediaController extends Controller
{
    public function __construct(private readonly CareLogMediaService $mediaService) {}

    public function store(StoreCareLogMediaRequest $request, CareLog $careLog): RedirectResponse
    {
        $company = app(CompanyContext::class);

        if ($careLog->company_id !== $company->id) {
            abort(403);
        }

        $this->mediaService->attach($careLog, $request->file('images'));

        return back()->with('success', 'Photos uploaded.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $company = app(CompanyContext::class);

        $careLog = CareLog::findOrFail($media->model_id);
        if ($careLog->company_id !== $company->id) {
            abort(403);
        }

        $this->mediaService->delete($media);

        return back()->with('success', 'Photo removed.');
    }
}
