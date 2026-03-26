<?php

use Illuminate\Support\Facades\Process;

it('fails when vegapull binary is not found', function () {
    Process::fake([
        'which *' => Process::result(exitCode: 1),
    ]);

    $this->artisan('cards:fetch')
        ->expectsOutputToContain('not found')
        ->assertFailed();
});

it('fails when vegapull scrape fails', function () {
    Process::fake([
        'which *' => Process::result(output: '/usr/local/bin/vega'),
        'vega *' => Process::result(
            errorOutput: 'Connection refused',
            exitCode: 1,
        ),
    ]);

    $this->artisan('cards:fetch')
        ->expectsOutputToContain('Connection refused')
        ->assertFailed();
});

it('fetches and imports cards successfully', function () {
    config(['import.vegapull_path' => __DIR__.'/../../Fixtures/vegapull']);

    Process::fake([
        'which *' => Process::result(output: '/usr/local/bin/vega'),
        'vega *' => Process::result(output: 'Done'),
    ]);

    $this->artisan('cards:fetch')
        ->assertSuccessful();

    Process::assertRan(fn ($process) => str_contains($process->command, 'vega pull all'));
});

it('uses configured binary path', function () {
    config(['import.vegapull_binary' => '/custom/path/vega']);
    config(['import.vegapull_path' => __DIR__.'/../../Fixtures/vegapull']);

    Process::fake([
        'which *' => Process::result(output: '/custom/path/vega'),
        '/custom/path/vega *' => Process::result(output: 'Done'),
    ]);

    $this->artisan('cards:fetch')
        ->assertSuccessful();

    Process::assertRan(fn ($process) => str_contains($process->command, '/custom/path/vega pull all'));
});
