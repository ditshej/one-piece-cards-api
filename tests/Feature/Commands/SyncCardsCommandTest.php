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
    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync')
        ->expectsOutputToContain('Sync complete')
        ->assertSuccessful();

    Process::assertRan(fn ($p) => is_array($p->command)
        && in_array('scp', $p->command)
        && in_array('22', $p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'example.com'))
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'database.sqlite'))
    );
});

it('clears production cache after upload', function () {
    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync')->assertSuccessful();

    Process::assertRan(fn ($p) => is_array($p->command)
        && in_array('ssh', $p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'optimize:clear'))
    );
});

it('fails when scp fails', function () {
    Process::fake(['*' => Process::result(errorOutput: 'Connection refused', exitCode: 1)]);

    $this->artisan('cards:sync')
        ->expectsOutputToContain('SCP failed')
        ->assertFailed();
});

it('calls cards:fetch first when --fetch is passed', function () {
    Process::fake(['*' => Process::result(exitCode: 1)]);

    $this->artisan('cards:sync --fetch')
        ->expectsOutputToContain('Fetching latest card data');
});
