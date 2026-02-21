<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\CareLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareLog>
 */
class CareLogFactory extends Factory
{
    protected $model = CareLog::class;

    private static array $activityTypes = [
        'feeding', 'walking', 'medication', 'grooming',
        'play', 'toilet', 'health_check', 'other',
    ];

    private static array $notesByActivity = [
        'feeding'      => ['Fed 200g of dry food', 'Had breakfast - ate well', 'Given afternoon meal', 'Ate all food enthusiastically'],
        'walking'      => ['30-minute morning walk', '20-minute garden walk', 'Run in the field - very energetic', 'Short toilet walk'],
        'medication'   => ['Given daily medication with food', 'Eye drops administered', 'Flea treatment applied', 'Joint supplement given'],
        'grooming'     => ['Brushed and detangled coat', 'Ears cleaned', 'Nails trimmed', 'Bath given, coat dried'],
        'play'         => ['15 minutes fetch in the garden', 'Played with rope toy', 'Interactive puzzle feeder', 'Socialised with other dogs'],
        'toilet'       => ['Toileted in garden', 'Good toilet break', 'Toileted on walk'],
        'health_check' => ['Looking bright and happy', 'Ate well, good energy levels', 'Slight lethargy noted, monitoring', 'Stools normal, appetite good'],
        'other'        => ['Settled well in kennel', 'Comfortable and calm', 'Enjoying kennel stay'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activityType = fake()->randomElement(self::$activityTypes);
        $notes = fake()->randomElement(self::$notesByActivity[$activityType] ?? ['No additional notes.']);

        return [
            'booking_id'    => Booking::factory(),
            'logged_by'     => User::factory()->staff(),
            'activity_type' => $activityType,
            'notes'         => $notes,
            'occurred_at'   => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }

    public function feeding(): static
    {
        return $this->state(['activity_type' => 'feeding', 'notes' => fake()->randomElement(self::$notesByActivity['feeding'])]);
    }

    public function walking(): static
    {
        return $this->state(['activity_type' => 'walking', 'notes' => fake()->randomElement(self::$notesByActivity['walking'])]);
    }

    public function forToday(): static
    {
        return $this->state(['occurred_at' => now()->subHours(fake()->numberBetween(1, 8))]);
    }
}
