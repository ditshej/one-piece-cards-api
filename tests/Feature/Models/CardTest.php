<?php

use App\Models\Card;
use App\Models\Pack;

it('can be created with a string id', function () {
    $card = Card::factory()->create([
        'id' => 'OP01-001',
        'name' => 'Monkey.D.Luffy',
    ]);

    expect($card->id)->toBe('OP01-001')
        ->and($card->name)->toBe('Monkey.D.Luffy');
});

it('does not use auto-incrementing ids', function () {
    $card = Card::factory()->create();

    expect($card->getIncrementing())->toBeFalse()
        ->and($card->getKeyType())->toBe('string');
});

it('belongs to a pack', function () {
    $pack = Pack::factory()->create(['id' => 'OP01']);
    $card = Card::factory()->create(['pack_id' => 'OP01']);

    expect($card->pack)->toBeInstanceOf(Pack::class)
        ->and($card->pack->id)->toBe('OP01');
});

it('casts colors to array', function () {
    $card = Card::factory()->create([
        'colors' => ['Red', 'Green'],
    ]);

    $card->refresh();

    expect($card->colors)->toBeArray()
        ->and($card->colors)->toBe(['Red', 'Green']);
});

it('casts attributes to array', function () {
    $card = Card::factory()->create([
        'attributes' => ['Strike', 'Ranged'],
    ]);

    $card->refresh();

    expect($card->attributes)->toBeArray()
        ->and($card->attributes)->toBe(['Strike', 'Ranged']);
});

it('casts types to array', function () {
    $card = Card::factory()->create([
        'types' => ['Straw Hat Crew'],
    ]);

    $card->refresh();

    expect($card->types)->toBeArray()
        ->and($card->types)->toBe(['Straw Hat Crew']);
});

it('allows nullable fields', function () {
    $card = Card::factory()->create([
        'cost' => null,
        'power' => null,
        'counter' => null,
        'effect' => null,
        'trigger' => null,
    ]);

    expect($card->cost)->toBeNull()
        ->and($card->power)->toBeNull()
        ->and($card->counter)->toBeNull()
        ->and($card->effect)->toBeNull()
        ->and($card->trigger)->toBeNull();
});
