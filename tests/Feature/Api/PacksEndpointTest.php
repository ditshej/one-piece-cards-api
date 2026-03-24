<?php

use App\Models\Card;
use App\Models\Pack;

it('does not respond on unversioned api path', function () {
    Pack::factory()->create(['id' => 'OP01']);

    $this->getJson('/api/packs')->assertNotFound();
});

it('returns 404 for a missing pack', function () {
    $this->getJson('/api/v1/packs/INVALID')
        ->assertNotFound();
});

it('returns empty data array when no packs exist', function () {
    $this->getJson('/api/v1/packs')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('returns packs ordered by id', function () {
    Pack::factory()->create(['id' => 'ST01']);
    Pack::factory()->create(['id' => 'OP01']);
    Pack::factory()->create(['id' => 'OP15']);

    $response = $this->getJson('/api/v1/packs')->assertOk();

    expect($response->json('data.*.id'))->toBe(['OP01', 'OP15', 'ST01']);
});

it('returns all packs on index', function () {
    Pack::factory()->create(['id' => 'OP01', 'name' => 'Romance Dawn']);
    Pack::factory()->create(['id' => 'OP02', 'name' => 'Paramount War']);

    $this->getJson('/api/v1/packs')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure(['data' => [['id', 'name']]])
        ->assertJsonPath('data.0.id', 'OP01')
        ->assertJsonPath('data.1.id', 'OP02');
});

it('returns a single pack with its cards on show', function () {
    Pack::factory()->create(['id' => 'OP01', 'name' => 'Romance Dawn']);
    Card::factory()->count(3)->create(['pack_id' => 'OP01']);

    $this->getJson('/api/v1/packs/OP01')
        ->assertOk()
        ->assertJsonPath('data.id', 'OP01')
        ->assertJsonPath('data.name', 'Romance Dawn')
        ->assertJsonCount(3, 'data.cards');
});

it('returns all card fields through CardResource on pack show', function () {
    $pack = Pack::factory()->create(['id' => 'OP01']);
    Card::factory()->for($pack)->create([
        'id' => 'OP01-001',
        'name' => 'Monkey.D.Luffy',
        'rarity' => 'L',
        'category' => 'Leader',
        'colors' => ['Red'],
        'cost' => null,
        'power' => 5000,
        'counter' => null,
        'attributes' => ['Strike'],
        'types' => ['Straw Hat Crew'],
        'effect' => 'Activate: Main',
        'trigger' => null,
        'img_url' => 'https://example.com/OP01-001.png',
    ]);

    $this->getJson('/api/v1/packs/OP01')
        ->assertOk()
        ->assertJsonPath('data.cards.0.id', 'OP01-001')
        ->assertJsonPath('data.cards.0.pack_id', 'OP01')
        ->assertJsonPath('data.cards.0.name', 'Monkey.D.Luffy')
        ->assertJsonPath('data.cards.0.rarity', 'L')
        ->assertJsonPath('data.cards.0.category', 'Leader')
        ->assertJsonPath('data.cards.0.colors', ['Red'])
        ->assertJsonPath('data.cards.0.cost', null)
        ->assertJsonPath('data.cards.0.power', 5000)
        ->assertJsonPath('data.cards.0.counter', null)
        ->assertJsonPath('data.cards.0.attributes', ['Strike'])
        ->assertJsonPath('data.cards.0.types', ['Straw Hat Crew'])
        ->assertJsonPath('data.cards.0.effect', 'Activate: Main')
        ->assertJsonPath('data.cards.0.trigger', null)
        ->assertJsonPath('data.cards.0.img_url', 'https://example.com/OP01-001.png');
});
