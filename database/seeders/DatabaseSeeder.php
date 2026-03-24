<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Pack;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Pack::factory()
            ->count(3)
            ->has(Card::factory()->count(12))
            ->create();
    }
}
