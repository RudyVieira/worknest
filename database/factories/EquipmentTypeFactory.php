<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EquipmentType>
 */
class EquipmentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $equipmentTypes = [
            'Écran',
            'Ordinateur',
            'Imprimante',
            'Scanner',
            'Tableau blanc',
            'Vidéoprojecteur',
            'Téléphone',
            'Fauteuil ergonomique',
            'Bureau réglable',
            'Casier',
            'Wifi haut débit',
            'Climatisation',
            'Machine à café',
            'Micro-ondes',
            'Réfrigérateur',
        ];

        return [
            'name' => fake()->unique()->randomElement($equipmentTypes),
        ];
    }
}
