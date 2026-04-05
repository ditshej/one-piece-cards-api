<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('revokes all tokens for the given app name', function () {
    $user = User::factory()->create();
    $user->createToken('Brook Deck Sim');
    $user->createToken('Brook Deck Sim');

    expect(PersonalAccessToken::where('name', 'Brook Deck Sim')->count())->toBe(2);

    $this->artisan('token:revoke', ['name' => 'Brook Deck Sim'])
        ->assertSuccessful()
        ->expectsOutputToContain('Revoked 2 token(s) for [Brook Deck Sim]');

    expect(PersonalAccessToken::where('name', 'Brook Deck Sim')->count())->toBe(0);
});

it('returns failure when no tokens found', function () {
    $this->artisan('token:revoke', ['name' => 'Unknown App'])
        ->assertFailed()
        ->expectsOutputToContain('No tokens found for [Unknown App]');
});

it('requires a name argument', function () {
    $this->artisan('token:revoke');
})->throws(RuntimeException::class, 'Not enough arguments');
