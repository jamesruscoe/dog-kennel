<?php

namespace App\Enums;

enum DayOfWeek: int
{
    case Monday    = 1;
    case Tuesday   = 2;
    case Wednesday = 3;
    case Thursday  = 4;
    case Friday    = 5;
    case Saturday  = 6;
    case Sunday    = 7;

    public function label(): string
    {
        return match($this) {
            self::Monday    => 'Monday',
            self::Tuesday   => 'Tuesday',
            self::Wednesday => 'Wednesday',
            self::Thursday  => 'Thursday',
            self::Friday    => 'Friday',
            self::Saturday  => 'Saturday',
            self::Sunday    => 'Sunday',
        };
    }

    public function isWeekend(): bool
    {
        return in_array($this, [self::Saturday, self::Sunday]);
    }

    /**
     * Create from PHP date('N') format (1=Monday, 7=Sunday).
     */
    public static function fromDateN(int $n): self
    {
        return self::from($n);
    }
}
