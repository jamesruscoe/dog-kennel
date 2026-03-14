<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Database\Factories\CareLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CareLog extends Model implements HasMedia
{
    /** @use HasFactory<CareLogFactory> */
    use HasFactory, BelongsToCompany, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'booking_id',
        'logged_by',
        'activity_type',
        'notes',
        'occurred_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function loggedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    // -------------------------------------------------------------------------
    // Media Collections (Spatie)
    // -------------------------------------------------------------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('care-log-photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk(config('media-library.disk_name'));
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeByActivityType(Builder $query, string $type): Builder
    {
        return $query->where('activity_type', $type);
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('occurred_at', $date);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('occurred_at', today());
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getActivityLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->activity_type));
    }
}
