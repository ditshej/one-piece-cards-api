<?php

use App\Models\Card;
use App\Models\Pack;
use App\Models\User;

it('seeds packs with associated cards and a test user', function () {
    $this->seed();

    expect(Pack::count())->toBeGreaterThan(0)
        ->and(Card::count())->toBeGreaterThan(0)
        ->and(Pack::first()->cards)->not->toBeEmpty()
        ->and(User::where('email', 'test@example.com')->exists())->toBeTrue();
});
