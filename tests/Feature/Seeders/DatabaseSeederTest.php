<?php

use App\Models\Card;
use App\Models\Pack;
use App\Models\User;

it('seeds packs with associated cards', function () {
    $this->seed();

    expect(Pack::count())->toBeGreaterThan(0)
        ->and(Card::count())->toBeGreaterThan(0)
        ->and(Pack::first()->cards)->not->toBeEmpty();
});

it('seeds a test user', function () {
    $this->seed();

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

it('completes without errors on a fresh database', function () {
    $this->seed();

    expect(true)->toBeTrue();
});
