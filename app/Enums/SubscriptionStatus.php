<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Trialing = 'trialing';
    case Incomplete = 'incomplete';
    case Unpaid = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::Active     => 'Active',
            self::PastDue    => 'Past Due',
            self::Canceled   => 'Cancelled',
            self::Trialing   => 'Trialing',
            self::Incomplete => 'Incomplete',
            self::Unpaid     => 'Unpaid',
        };
    }

    /**
     * Whether the subscription is in a usable state (bookings allowed).
     * Past due gets a grace period — Stripe is retrying the payment.
     */
    public function isUsable(): bool
    {
        return in_array($this, [self::Active, self::Trialing, self::PastDue]);
    }
}
