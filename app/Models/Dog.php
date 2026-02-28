<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Database\Factories\DogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dog extends Model
{
    /** @use HasFactory<DogFactory> */
    use HasFactory, SoftDeletes, BelongsToCompany;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'owner_id',
        'name',
        'breed',
        'date_of_birth',
        'sex',
        'neutered',
        'weight_kg',
        'colour',
        'microchip_number',
        'vet_name',
        'vet_phone',
        'vaccination_confirmed',
        'medical_notes',
        'dietary_notes',
        'behavioural_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth'         => 'date',
            'neutered'              => 'boolean',
            'weight_kg'             => 'decimal:2',
            'vaccination_confirmed' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function latestBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->latestOfMany('check_in_date');
    }

    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->whereIn('status', ['pending', 'approved'])
            ->latestOfMany('check_in_date');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeVaccinationConfirmed(Builder $query): Builder
    {
        return $query->where('vaccination_confirmed', true);
    }

    public function scopeByBreed(Builder $query, string $breed): Builder
    {
        return $query->where('breed', 'like', "%{$breed}%");
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Age in complete years, null if date of birth is unknown.
     */
    public function getAgeYearsAttribute(): ?int
    {
        return $this->date_of_birth?->diffInYears(now());
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->breed})";
    }
}
