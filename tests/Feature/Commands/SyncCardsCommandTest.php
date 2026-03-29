<?php

use Illuminate\Support\Facades\Process;

beforeEach(function () {
    config([
        'import.sync_host' => 'example.com',
        'import.sync_user' => 'deploy-user',
        'import.sync_port' => 22,
        'import.sync_path' => '/op-cards.ditshej.ch',
    ]);
});

it('fails when sync config is missing', function () {
    config(['import.sync_host' => null]);

    $this->artisan('cards:sync')
        ->expectsOutputToContain('Sync config missing')
        ->assertFailed();
});

it('uploads the database via scp', function () {
    Process::fake([
        'scp *' => Process::result(exitCode: 0),
        'ssh *' => Process::result(exitCode: 0),
    ]);

    $this->artisan('cards:sync')
        ->expectsOutputToContain('Sync complete')
        ->assertSuccessful();

    Process::assertRan(fn ($p) => str_contains($p->command, 'scp')
        && str_contains($p->command, 'database.sqlite')
        && str_contains($p->command, 'example.com')
        && str_contains($p->command, '22')
    );
});

it('clears production cache after upload', function () {
    Process::fake([
        'scp *' => Process::result(exitCode: 0),
        'ssh *' => Process::result(exitCode: 0),
    ]);

    $this->artisan('cards:sync')->assertSuccessful();

    Process::assertRan(fn ($p) => str_contains($p->command, 'ssh')
        && str_contains($p->command, 'optimize:clear')
    );
});

it('fails when scp fails', function () {
    Process::fake([
        'scp *' => Process::result(errorOutput: 'Connection refused', exitCode: 1),
    ]);

    $this->artisan('cards:sync')
        ->expectsOutputToContain('SCP failed')
        ->assertFailed();
});

it('calls cards:fetch first when --fetch is passed', function () {
    Process::fake([
        '* --version' => Process::result(exitCode: 1),
    ]);

    $this->artisan('cards:sync --fetch')
        ->expectsOutputToContain('Fetching latest card data');
});
