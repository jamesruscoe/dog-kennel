<?php

namespace App\Models;

use Database\Factories\CareLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareLog extends Model
{
    /** @use HasFactory<CareLogFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
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
