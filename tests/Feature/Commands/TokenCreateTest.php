<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('creates a user and stores a token', function () {
    $this->artisan('token:create', [
        'name' => 'Brook Deck Sim',
        'email' => 'brook@apps.test',
    ])->assertSuccessful();

    expect(User::where('email', 'brook@apps.test')->exists())->toBeTrue();
    expect(PersonalAccessToken::count())->toBe(1);
});

it('outputs the plaintext token once', function () {
    $this->artisan('token:create', [
        'name' => 'Brook Deck Sim',
        'email' => 'brook@apps.test',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('Token created for [Brook Deck Sim]');
});

it('requires name and email arguments', function () {
    $this->artisan('token:create');
})->throws(\RuntimeException::class, 'Not enough arguments');
