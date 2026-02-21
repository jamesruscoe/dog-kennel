<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending   = 'pending';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending Approval',
            self::Approved  => 'Approved',
            self::Rejected  => 'Rejected',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Approved]);
    }

    public function canTransitionTo(self $next): bool
    {
        return match($this) {
            self::Pending   => in_array($next, [self::Approved, self::Rejected, self::Cancelled]),
            self::Approved  => in_array($next, [self::Cancelled, self::Completed]),
            self::Rejected  => false,
            self::Cancelled => false,
            self::Completed => false,
        };
    }
}
