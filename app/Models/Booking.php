<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'dog_id',
        'check_in_date',
        'check_out_date',
        'status',
        'notes',
        'special_requirements',
        'rejection_reason',
        'cancellation_reason',
        'amount_pence',
        'payment_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in_date'  => 'date',
            'check_out_date' => 'date',
            'status'         => BookingStatus::class,
            'amount_pence'   => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function dog(): BelongsTo
    {
        return $this->belongsTo(Dog::class);
    }

    public function careLogs(): HasMany
    {
        return $this->hasMany(CareLog::class)->orderBy('occurred_at');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Approved->value]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Pending->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Approved->value);
    }

    public function scopeInDateRange(Builder $query, string $from, string $to): Builder
    {
        // Overlapping date range: booking starts before $to AND ends after $from
        return $query
            ->where('check_in_date', '<', $to)
            ->where('check_out_date', '>', $from);
    }

    /**
     * Bookings that overlap a single night (the dog is in the kennel on $date).
     */
    public function scopeOccupyingOnDate(Builder $query, string $date): Builder
    {
        return $query
            ->where('check_in_date', '<=', $date)
            ->where('check_out_date', '>', $date);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getNightsAttribute(): int
    {
        return (int) $this->check_in_date?->diffInDays($this->check_out_date);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof BookingStatus
            ? $this->status->label()
            : BookingStatus::from($this->status)->label();
    }

    public function getAmountDisplayAttribute(): string
    {
        return $this->amount_pence
            ? '£' . number_format($this->amount_pence / 100, 2)
            : '—';
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === BookingStatus::Approved;
    }

    public function isActive(): bool
    {
        return $this->status?->isActive() ?? false;
    }
}
