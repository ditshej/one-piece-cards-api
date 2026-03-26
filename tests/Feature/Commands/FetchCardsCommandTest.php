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

it('fails when vegapull scrape fails', function () {
    Process::fake([
        '* --version' => Process::result(output: 'vega 1.2.1'),
        '* pull all *' => Process::result(
            errorOutput: 'Connection refused',
            exitCode: 1,
        ),
    ]);

    $this->artisan('cards:fetch')
        ->expectsOutputToContain('Connection refused')
        ->assertFailed();
});

it('fetches and imports cards successfully', function () {
    Process::fake([
        '* --version' => Process::result(output: 'vega 1.2.1'),
        '* pull all *' => Process::result(output: 'Done'),
    ]);

    $this->artisan('cards:fetch')
        ->assertSuccessful();

    expect(Pack::count())->toBe(1)
        ->and(Card::count())->toBe(3);

    Process::assertRan(fn ($process) => str_contains($process->command, 'vega pull all'));
});

it('uses configured binary path', function () {
    config(['import.vegapull_binary' => '/custom/path/vega']);

    Process::fake([
        '* --version' => Process::result(output: 'vega 1.2.1'),
        '* pull all *' => Process::result(output: 'Done'),
    ]);

    $this->artisan('cards:fetch')
        ->assertSuccessful();

    Process::assertRan(fn ($process) => str_contains($process->command, '/custom/path/vega pull all'));
});
