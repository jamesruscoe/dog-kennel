<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Database\Factories\OwnerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    /** @use HasFactory<OwnerFactory> */
    use HasFactory, SoftDeletes, BelongsToCompany;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'phone',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dogs(): HasMany
    {
        return $this->hasMany(Dog::class);
    }

    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, Dog::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to owners who currently have at least one active booking.
     */
    public function scopeWithActiveBookings(Builder $query): Builder
    {
        return $query->whereHas('bookings', fn($q) => $q->whereIn('status', ['pending', 'approved']));
    }

    // -------------------------------------------------------------------------
    // Convenience
    // -------------------------------------------------------------------------

    /**
     * Return only active (pending/approved) bookings for this owner.
     */
    public function activeBookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, Dog::class)
            ->whereIn('bookings.status', ['pending', 'approved']);
    }

    /**
     * Display name — proxied from the related User.
     */
    public function getNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }

    /**
     * Email — proxied from the related User.
     */
    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }
}
