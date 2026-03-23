<?php

use App\Models\Card;
use App\Models\Pack;

beforeEach(function () {
    $this->fixturePath = __DIR__.'/../../Fixtures/vegapull';
});

it('imports packs and cards from vegapull JSON files', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath])
        ->assertSuccessful();

    expect(Pack::count())->toBe(1)
        ->and(Pack::first()->id)->toBe('OP01')
        ->and(Pack::first()->name)->toBe('Romance Dawn')
        ->and(Card::count())->toBe(3);
});

it('is idempotent', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath]);
    $this->artisan('cards:import', ['path' => $this->fixturePath]);

    expect(Pack::count())->toBe(1)
        ->and(Card::count())->toBe(3);
});

it('updates existing card data', function () {
    Card::factory()->create([
        'id' => 'OP01-001',
        'pack_id' => Pack::factory()->create(['id' => 'OP01'])->id,
        'power' => 3000,
    ]);

    $this->artisan('cards:import', ['path' => $this->fixturePath]);

    expect(Card::find('OP01-001')->power)->toBe(5000);
});

it('uses default config path when no argument given', function () {
    config(['import.vegapull_path' => $this->fixturePath]);

    $this->artisan('cards:import')
        ->assertSuccessful();

    expect(Card::count())->toBe(3);
});

it('warns when no JSON files are found', function () {
    $emptyDir = sys_get_temp_dir().'/empty-vegapull-'.uniqid();
    mkdir($emptyDir);

    try {
        $this->artisan('cards:import', ['path' => $emptyDir])
            ->expectsOutputToContain('No JSON files found');

        expect(Card::count())->toBe(0);
    } finally {
        rmdir($emptyDir);
    }
});

it('skips empty or invalid JSON files', function () {
    $tempDir = sys_get_temp_dir().'/bad-vegapull-'.uniqid();
    mkdir($tempDir);
    file_put_contents($tempDir.'/bad.json', 'not valid json');

    try {
        $this->artisan('cards:import', ['path' => $tempDir])
            ->expectsOutputToContain('Skipping empty or invalid file');

        expect(Card::count())->toBe(0);
    } finally {
        unlink($tempDir.'/bad.json');
        rmdir($tempDir);
    }
});

it('displays import summary', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath])
        ->expectsOutputToContain('Imported 3 cards');
});
