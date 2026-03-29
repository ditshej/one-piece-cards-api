<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Pack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pack_id' => Pack::factory(),
            'id' => function () {
                $prefix = fake()->randomElement(['OP01', 'ST01', 'EB01']);
                $number = str_pad((string) fake()->unique()->numberBetween(1, 150), 3, '0', STR_PAD_LEFT);

                return $prefix.'-'.$number;
            },
            'card_set' => fn (array $attributes) => explode('-', $attributes['id'])[0],
            'alt_art_variant' => fn (array $attributes) => preg_match('/_p(\d+)$/', $attributes['id'], $m) ? (int) $m[1] : null,
            'name' => fake()->name(),
            'rarity' => fake()->randomElement(['C', 'UC', 'R', 'SR', 'SEC', 'L', 'P']),
            'category' => fake()->randomElement(['Leader', 'Character', 'Event', 'Stage']),
            'colors' => [fake()->randomElement(['Red', 'Green', 'Blue', 'Purple', 'Black', 'Yellow'])],
            'cost' => fake()->numberBetween(1, 10),
            'power' => fake()->randomElement([null, 1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000]),
            'counter' => fake()->randomElement([null, 1000, 2000]),
            'attributes' => [fake()->randomElement(['Strike', 'Ranged', 'Wisdom', 'Slash'])],
            'types' => [fake()->randomElement(['Straw Hat Crew', 'Fish-Man', 'Navy', 'The Seven Warlords of the Sea'])],
            'effect' => fake()->optional()->sentence(),
            'trigger' => fake()->optional()->sentence(),
            'img_url' => 'https://en.onepiece-cardgame.com/images/cardlist/card/OP01-001.png',
        ];
    }
}
