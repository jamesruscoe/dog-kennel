<?php

namespace App\Services;

use App\Models\CareLog;
use App\Models\CareLogMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CareLogMediaService
{
    /**
     * Attach uploaded images to a care log entry.
     *
     * @param  CareLog  $careLog
     * @param  UploadedFile[]  $files
     */
    public function attach(CareLog $careLog, array $files): void
    {
        $existingCount = $careLog->media()->count();
        $newCount = count($files);

        if ($existingCount + $newCount > 5) {
            throw ValidationException::withMessages([
                'images' => "Maximum 5 photos allowed. This care log already has {$existingCount}.",
            ]);
        }

        $companyId = $careLog->company_id;
        $order = $existingCount;

        foreach ($files as $file) {
            $uuid = Str::uuid();
            $ext = $file->getClientOriginalExtension();
            $path = "media/company-{$companyId}/care-logs/{$uuid}.{$ext}";

            Storage::disk('s3')->put($path, file_get_contents($file->getRealPath()));

            CareLogMedia::create([
                'company_id'  => $companyId,
                'care_log_id' => $careLog->id,
                'path'        => $path,
                'disk'        => 's3',
                'mime_type'   => $file->getMimeType(),
                'size_bytes'  => $file->getSize(),
                'order'       => $order++,
            ]);
        }
    }

    public function delete(CareLogMedia $media): void
    {
        Storage::disk($media->disk ?? 's3')->delete($media->path);
        $media->delete();
    }
}
