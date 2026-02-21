<?php

namespace Database\Factories;

use App\Models\Dog;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dog>
 */
class DogFactory extends Factory
{
    protected $model = Dog::class;

    private static array $breeds = [
        'Labrador Retriever', 'German Shepherd', 'Golden Retriever',
        'French Bulldog', 'Bulldog', 'Poodle', 'Beagle', 'Rottweiler',
        'Yorkshire Terrier', 'Dachshund', 'Boxer', 'Siberian Husky',
        'Shih Tzu', 'Chihuahua', 'Border Collie', 'Cocker Spaniel',
        'Springer Spaniel', 'Cavalier King Charles Spaniel', 'Pug',
    ];

    private static array $dogNames = [
        'Buddy', 'Max', 'Charlie', 'Cooper', 'Milo', 'Bear', 'Rocky',
        'Duke', 'Zeus', 'Bella', 'Lucy', 'Daisy', 'Molly', 'Lola',
        'Sadie', 'Maggie', 'Sophie', 'Chloe', 'Penny', 'Rosie',
        'Archie', 'Biscuit', 'Poppy', 'Alfie', 'Teddy', 'Barley',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sex = fake()->randomElement(['male', 'female']);

        return [
            'owner_id'              => Owner::factory(),
            'name'                  => fake()->randomElement(self::$dogNames),
            'breed'                 => fake()->randomElement(self::$breeds),
            'date_of_birth'         => fake()->dateTimeBetween('-12 years', '-6 months')->format('Y-m-d'),
            'sex'                   => $sex,
            'neutered'              => fake()->boolean(60),
            'weight_kg'             => fake()->randomFloat(1, 3, 45),
            'colour'                => fake()->randomElement(['Black', 'Brown', 'Golden', 'White', 'Grey', 'Brindle', 'Tan']),
            'microchip_number'      => fake()->optional(0.7)->numerify('###############'),
            'vet_name'              => fake()->optional(0.8)->company() . ' Veterinary Practice',
            'vet_phone'             => fake()->optional(0.8)->phoneNumber(),
            'vaccination_confirmed' => fake()->boolean(85),
            'medical_notes'         => fake()->optional(0.2)->sentence(),
            'dietary_notes'         => fake()->optional(0.3)->sentence(),
            'behavioural_notes'     => fake()->optional(0.4)->sentence(),
        ];
    }

    public function vaccinated(): static
    {
        return $this->state(['vaccination_confirmed' => true]);
    }

    public function unvaccinated(): static
    {
        return $this->state(['vaccination_confirmed' => false]);
    }
}
