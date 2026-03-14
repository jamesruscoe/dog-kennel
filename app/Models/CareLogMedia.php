<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CareLogMedia extends Model
{
    use BelongsToCompany;

    protected $table = 'care_log_media';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'care_log_id',
        'path',
        'disk',
        'mime_type',
        'size_bytes',
        'order',
    ];

    /**
     * @var list<string>
     */
    protected $appends = ['signed_url'];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function careLog(): BelongsTo
    {
        return $this->belongsTo(CareLog::class);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getSignedUrlAttribute(): ?string
    {
        try {
            return Storage::disk($this->disk ?? 's3')
                ->temporaryUrl($this->path, now()->addMinutes(60));
        } catch (\Exception) {
            return null;
        }
    }
}
