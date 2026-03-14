<?php

namespace App\Services;

use App\Models\CareLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CareLogMediaService
{
    private const COLLECTION = 'care-log-photos';
    private const MAX_PHOTOS = 5;

    /**
     * Attach uploaded images to a care log entry.
     *
     * @param  CareLog  $careLog
     * @param  UploadedFile[]  $files
     */
    public function attach(CareLog $careLog, array $files): void
    {
        $existingCount = $careLog->getMedia(self::COLLECTION)->count();
        $newCount = count($files);

        if ($existingCount + $newCount > self::MAX_PHOTOS) {
            throw ValidationException::withMessages([
                'images' => "Maximum " . self::MAX_PHOTOS . " photos allowed. This care log already has {$existingCount}.",
            ]);
        }

        foreach ($files as $file) {
            $careLog->addMedia($file)
                ->toMediaCollection(self::COLLECTION);
        }
    }

    public function delete(Media $media): void
    {
        $media->delete();
    }
}
