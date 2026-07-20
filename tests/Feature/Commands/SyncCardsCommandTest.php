<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    config([
        'import.sync_host' => 'example.com',
        'import.sync_user' => 'deploy-user',
        'import.sync_port' => 22,
        'import.sync_path' => '/op-cards.ditshej.ch',
    ]);

    $jsonPath = config('import.vegapull_path').'/json';

    File::ensureDirectoryExists($jsonPath);
    File::put($jsonPath.'/packs.json', '{}');
    File::put($jsonPath.'/cards_OP01.json', '[]');
});

afterEach(function () {
    File::deleteDirectory(config('import.vegapull_path'));
});

it('fails when sync config is missing', function () {
    config(['import.sync_host' => null]);

    $this->artisan('cards:sync')
        ->expectsOutputToContain('Sync config missing')
        ->assertFailed();
});

it('uploads the card json directory via scp', function () {
    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync')
        ->expectsOutputToContain('Sync complete')
        ->assertSuccessful();

    Process::assertRan(fn ($p) => is_array($p->command)
        && in_array('scp', $p->command)
        && in_array('22', $p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'example.com'))
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'storage/vegapull/json'))
        && ! collect($p->command)->contains(fn ($arg) => str_contains($arg, 'database.sqlite'))
    );
});

it('never transfers the sqlite database file', function () {
    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync')->assertSuccessful();

    Process::assertNotRan(fn ($p) => is_array($p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'database.sqlite'))
    );
});

it('runs cards:import remotely over ssh', function () {
    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync')->assertSuccessful();

    Process::assertRan(fn ($p) => is_array($p->command)
        && in_array('ssh', $p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'artisan cards:import'))
    );
});

it('ensures the remote json directory exists', function () {
    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync')->assertSuccessful();

    Process::assertRan(fn ($p) => is_array($p->command)
        && in_array('ssh', $p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'mkdir -p') && str_contains($arg, 'storage/vegapull/json'))
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

it('fails when a remote step fails', function (string $failingPattern, string $expectedOutput) {
    Process::fake([
        $failingPattern => Process::result(errorOutput: 'boom', exitCode: 1),
        '*' => Process::result(exitCode: 0),
    ]);

    $this->artisan('cards:sync')
        ->expectsOutputToContain($expectedOutput)
        ->assertFailed();
})->with([
    'scp' => ['*scp*', 'SCP failed'],
    'remote mkdir' => ['*mkdir*', 'Could not create remote json directory'],
    'remote cards:import' => ['*cards:import*', 'Remote import failed'],
]);

it('stops before scp when the remote mkdir fails', function () {
    Process::fake([
        '*mkdir*' => Process::result(errorOutput: 'boom', exitCode: 1),
        '*' => Process::result(exitCode: 0),
    ]);

    $this->artisan('cards:sync')->assertFailed();

    Process::assertDidntRun(fn ($p) => is_array($p->command) && in_array('scp', $p->command));
});

it('stops before optimize:clear when the remote cards:import fails', function () {
    Process::fake([
        '*cards:import*' => Process::result(errorOutput: 'boom', exitCode: 1),
        '*' => Process::result(exitCode: 0),
    ]);

    $this->artisan('cards:sync')->assertFailed();

    Process::assertDidntRun(fn ($p) => is_array($p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, 'optimize:clear'))
    );
});

it('fails when local json directory is missing or empty', function () {
    File::deleteDirectory(config('import.vegapull_path'));

    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync')->assertFailed();

    Process::assertDidntRun(fn ($p) => is_array($p->command) && in_array('scp', $p->command));
});

it('uses the configured php binary for remote artisan calls', function () {
    config(['import.sync_php' => '/opt/php83/bin/php']);

    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync')->assertSuccessful();

    Process::assertRan(fn ($p) => is_array($p->command)
        && in_array('ssh', $p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, '/opt/php83/bin/php artisan cards:import'))
    );

    Process::assertRan(fn ($p) => is_array($p->command)
        && in_array('ssh', $p->command)
        && collect($p->command)->contains(fn ($arg) => str_contains($arg, '/opt/php83/bin/php artisan optimize:clear'))
    );
});

it('calls cards:fetch first when --fetch is passed', function () {
    Process::fake(['*' => Process::result(exitCode: 0)]);

    $this->artisan('cards:sync --fetch')
        ->expectsOutputToContain('Fetching latest card data');
});
