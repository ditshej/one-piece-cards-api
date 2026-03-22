<?php

use App\Models\Card;
use App\Models\Pack;

it('can be created with a string id', function () {
    $pack = Pack::factory()->create([
        'id' => 'OP01',
        'name' => 'Romance Dawn',
    ]);

    expect($pack->id)->toBe('OP01')
        ->and($pack->name)->toBe('Romance Dawn');
});

it('does not use auto-incrementing ids', function () {
    $pack = Pack::factory()->create(['id' => 'ST01']);

    expect($pack->getIncrementing())->toBeFalse()
        ->and($pack->getKeyType())->toBe('string');
});

it('has many cards', function () {
    $pack = Pack::factory()->create(['id' => 'OP01']);

    Card::factory()->count(3)->create(['pack_id' => 'OP01']);

    expect($pack->cards)->toHaveCount(3)
        ->and($pack->cards->first())->toBeInstanceOf(Card::class);
});
