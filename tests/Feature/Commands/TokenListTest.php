<?php

use App\Models\User;

it('lists all tokens with name and user email', function () {
    $user1 = User::factory()->create(['email' => 'brook@apps.test']);
    $user1->createToken('Brook Deck Sim');
    $user2 = User::factory()->create(['email' => 'zoro@apps.test']);
    $user2->createToken('Zoro Builder');

    $this->artisan('token:list')
        ->assertSuccessful()
        ->expectsOutputToContain('Brook Deck Sim')
        ->expectsOutputToContain('zoro@apps.test');
});

it('shows a friendly message when no tokens exist', function () {
    $this->artisan('token:list')
        ->assertSuccessful()
        ->expectsOutputToContain('No tokens found');
});

it('outputs json when --json flag is passed', function () {
    $user = User::factory()->create(['email' => 'luffy@apps.test']);
    $user->createToken('Luffy Tracker');

    $this->artisan('token:list', ['--json' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Luffy Tracker');
});
