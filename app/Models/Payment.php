<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToCompany;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'booking_id',
        'stripe_payment_id',
        'stripe_payment_intent_id',
        'amount_pence',
        'currency',
        'status',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_pence' => 'integer',
            'paid_at'      => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getAmountDisplayAttribute(): string
    {
        return '£' . number_format($this->amount_pence / 100, 2);
    }

    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }
}
