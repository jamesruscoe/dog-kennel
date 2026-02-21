<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

class KennelSettings extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'max_capacity',
        'nightly_rate_pence',
        'operating_days',
        'check_in_time',
        'check_out_time',
        'booking_lead_days',
        'terms_and_conditions',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'operating_days'    => 'array',
            'max_capacity'      => 'integer',
            'nightly_rate_pence' => 'integer',
            'booking_lead_days' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Singleton enforcement
    // -------------------------------------------------------------------------

    /**
     * Prevent direct instantiation of more than one settings row.
     * Use KennelSettings::sole() to fetch the single settings row.
     */
    protected static function booted(): void
    {
        static::creating(function () {
            if (static::exists()) {
                throw new LogicException('Only one KennelSettings row may exist.');
            }
        });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Whether the given day of week integer is an operating day.
     */
    public function isOperatingDay(int $dayOfWeek): bool
    {
        return in_array($dayOfWeek, $this->operating_days ?? []);
    }

    /**
     * Return operating days as DayOfWeek enums.
     *
     * @return DayOfWeek[]
     */
    public function operatingDayEnums(): array
    {
        return array_map(fn(int $d) => DayOfWeek::from($d), $this->operating_days ?? []);
    }

    /**
     * Formatted nightly rate for display (e.g. "£35.00").
     */
    public function getNightlyRateDisplayAttribute(): string
    {
        return '£' . number_format($this->nightly_rate_pence / 100, 2);
    }
}
