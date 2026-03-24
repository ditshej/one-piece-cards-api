<?php

use App\Models\Card;
use App\Models\Pack;

// --- Show endpoint ---

it('returns 404 for a missing card', function () {
    $this->getJson('/api/v1/cards/INVALID-999')
        ->assertNotFound();
});

it('returns a single card on show', function () {
    $pack = Pack::factory()->create(['id' => 'OP01']);
    Card::factory()->for($pack)->create([
        'id' => 'OP01-001',
        'name' => 'Monkey.D.Luffy',
    ]);

    $this->getJson('/api/v1/cards/OP01-001')
        ->assertOk()
        ->assertJsonPath('data.id', 'OP01-001')
        ->assertJsonPath('data.name', 'Monkey.D.Luffy')
        ->assertJsonPath('data.pack_id', 'OP01');
});

// --- Index endpoint: edge cases ---

it('returns empty data array when no cards exist', function () {
    $this->getJson('/api/v1/cards')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

// --- Index endpoint: filters ---

it('filters cards by color', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['colors' => ['Red']]);
    Card::factory()->for($pack)->create(['colors' => ['Blue']]);
    Card::factory()->for($pack)->create(['colors' => ['Red', 'Green']]);

    $response = $this->getJson('/api/v1/cards?color=Red')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by category', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['category' => 'Leader']);
    Card::factory()->for($pack)->create(['category' => 'Character']);
    Card::factory()->for($pack)->create(['category' => 'Leader']);

    $response = $this->getJson('/api/v1/cards?category=Leader')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by cost', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['cost' => 5]);
    Card::factory()->for($pack)->create(['cost' => 3]);
    Card::factory()->for($pack)->create(['cost' => 5]);

    $response = $this->getJson('/api/v1/cards?cost=5')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by pack', function () {
    $pack1 = Pack::factory()->create(['id' => 'OP15']);
    $pack2 = Pack::factory()->create(['id' => 'OP01']);
    Card::factory()->for($pack1)->count(2)->create();
    Card::factory()->for($pack2)->create();

    $response = $this->getJson('/api/v1/cards?pack=OP15')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('combines multiple filters', function () {
    $pack = Pack::factory()->create(['id' => 'OP15']);
    Card::factory()->for($pack)->create(['colors' => ['Red'], 'cost' => 5]);
    Card::factory()->for($pack)->create(['colors' => ['Red'], 'cost' => 3]);
    Card::factory()->for($pack)->create(['colors' => ['Blue'], 'cost' => 5]);

    $response = $this->getJson('/api/v1/cards?color=Red&cost=5')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

// --- Index endpoint: search ---

it('searches cards by effect text', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => 'Draw 2 cards', 'trigger' => null]);
    Card::factory()->for($pack)->create(['effect' => 'Give +1000 power', 'trigger' => null]);

    $response = $this->getJson('/api/v1/cards?search=draw')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('searches cards by trigger text', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => null, 'trigger' => 'Draw 1 card']);
    Card::factory()->for($pack)->create(['effect' => null, 'trigger' => null]);

    $response = $this->getJson('/api/v1/cards?search=draw')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

// --- Index endpoint: pagination ---

it('paginates card results', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->count(20)->create();

    $response = $this->getJson('/api/v1/cards')->assertOk();

    expect($response->json('data'))->toHaveCount(15)
        ->and($response->json('meta.total'))->toBe(20)
        ->and($response->json('meta.last_page'))->toBe(2);
});

it('returns second page of results', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->count(20)->create();

    $response = $this->getJson('/api/v1/cards?page=2')->assertOk();

    expect($response->json('data'))->toHaveCount(5);
});

// --- Index endpoint: happy path ---

it('returns paginated cards on index', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->count(3)->create();

    $this->getJson('/api/v1/cards')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'pack_id', 'name', 'rarity', 'category', 'colors', 'cost', 'power', 'counter', 'attributes', 'types', 'effect', 'trigger', 'img_url']],
            'links',
            'meta',
        ]);
});
