<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $spaceTypes = [
            'Bureau privé',
            'Open Space',
            'Salle de réunion',
            'Salle de conférence',
            'Atelier',
            'Studio',
            'Espace créatif',
            'Coworking',
        ];

        return [
            'name' => fake()->randomElement($spaceTypes) . ' ' . fake()->city(),
            'description' => fake()->paragraph(3),
            'latitude' => fake()->latitude(48.8, 48.9),
            'longitude' => fake()->longitude(2.2, 2.4),
            'capacity' => fake()->numberBetween(2, 50),
            'price_per_hour' => fake()->randomFloat(2, 10, 150),
            'owner_id' => User::factory(),
            'status' => fake()->randomElement(['AVAILABLE', 'AVAILABLE', 'AVAILABLE', 'MAINTENANCE', 'DISABLED']),
        ];
    }

    /**
     * Indicate that the space is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'AVAILABLE',
        ]);
    }

    /**
     * Indicate that the space is under maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'MAINTENANCE',
        ]);
    }

    /**
     * Indicate that the space is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'DISABLED',
        ]);
    }
}
