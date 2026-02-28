<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'stripe_account_id',
        'stripe_onboarding_complete',
        'application_fee_percent',
    ];

    protected function casts(): array
    {
        return [
            'stripe_onboarding_complete' => 'boolean',
            'application_fee_percent'    => 'decimal:2',
        ];
    }

    /**
     * The route uses the company slug in the URL, not the numeric primary key.
     * This lets Laravel resolve {company} route parameters by slug implicitly.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(KennelSettings::class);
    }

    public function dogs(): HasMany
    {
        return $this->hasMany(Dog::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // -------------------------------------------------------------------------
    // Stripe helpers
    // -------------------------------------------------------------------------

    public function isStripeConnected(): bool
    {
        return ! empty($this->stripe_account_id);
    }

    public function isStripeReady(): bool
    {
        return $this->isStripeConnected() && $this->stripe_onboarding_complete;
    }
}
