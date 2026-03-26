<?php

use App\Models\Card;
use App\Models\Pack;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    config(['import.vegapull_path' => __DIR__.'/../../Fixtures/vegapull']);
});

it('fails when vegapull binary is not found', function () {
    Process::fake([
        '* --version' => Process::result(exitCode: 1),
    ]);

    $this->artisan('cards:fetch')
        ->expectsOutputToContain('not found')
        ->assertFailed();
});

it('fails when packs fetch fails', function () {
    Process::fake([
        '* --version' => Process::result(output: 'vega 1.2.1'),
        '* pull *packs' => Process::result(
            errorOutput: 'Connection refused',
            exitCode: 1,
        ),
    ]);

    $this->artisan('cards:fetch')
        ->expectsOutputToContain('Connection refused')
        ->assertFailed();
});

it('fetches packs then cards and imports successfully', function () {
    Process::fake([
        '* --version' => Process::result(output: 'vega 1.2.1'),
        '* pull *packs' => Process::result(output: 'downloaded 1 packs'),
        '* pull *cards *' => Process::result(output: 'fetched cards'),
    ]);

    $this->artisan('cards:fetch')
        ->assertSuccessful();

    expect(Pack::count())->toBe(1)
        ->and(Card::count())->toBe(3);

    Process::assertRan(fn ($process) => str_contains($process->command, 'pull') && str_contains($process->command, 'packs'));
    Process::assertRan(fn ($process) => str_contains($process->command, 'pull') && str_contains($process->command, 'cards'));
});

it('uses configured binary path', function () {
    config(['import.vegapull_binary' => '/custom/path/vega']);

    Process::fake([
        '* --version' => Process::result(output: 'vega 1.2.1'),
        '* pull *packs' => Process::result(output: 'downloaded 1 packs'),
        '* pull *cards *' => Process::result(output: 'fetched cards'),
    ]);

    $this->artisan('cards:fetch')
        ->assertSuccessful();

    Process::assertRan(fn ($process) => str_contains($process->command, '/custom/path/vega pull'));
});

it('passes --language english to vegapull', function () {
    Process::fake([
        '* --version' => Process::result(output: 'vega 1.2.1'),
        '* pull *packs' => Process::result(output: 'downloaded 1 packs'),
        '* pull *cards *' => Process::result(output: 'fetched cards'),
    ]);

    $this->artisan('cards:fetch')
        ->assertSuccessful();

    Process::assertRan(fn ($process) => str_contains($process->command, '--language english'));
});

it('warns and continues when a single pack card fetch fails', function () {
    Process::fake([
        '* --version' => Process::result(output: 'vega 1.2.1'),
        '* pull *packs' => Process::result(output: 'downloaded 1 packs'),
        '* pull *cards *' => Process::result(exitCode: 1),
    ]);

    $this->artisan('cards:fetch')
        ->expectsOutputToContain('Failed to fetch cards')
        ->assertSuccessful();
});
