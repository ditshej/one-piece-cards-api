<?php

namespace Database\Factories;

use App\Models\Pack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pack>
 */
class PackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefix = fake()->randomElement(['OP', 'ST', 'EB']);
        $number = str_pad((string) fake()->unique()->numberBetween(1, 30), 2, '0', STR_PAD_LEFT);

        return [
            'id' => $prefix.$number,
            'name' => fake()->words(3, true),
        ];
    }
}
