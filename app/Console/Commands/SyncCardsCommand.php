<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

#[Signature('cards:sync {--fetch : Run cards:fetch before syncing}')]
#[Description('Sync local card JSON to the production server and import it there')]
class SyncCardsCommand extends Command
{
    public function handle(): int
    {
        $host = config('import.sync_host');
        $user = config('import.sync_user');
        $port = (string) config('import.sync_port');
        $path = config('import.sync_path');
        $php = config('import.sync_php');

        if (! $host || ! $user || ! $path) {
            $this->error('Sync config missing. Set SYNC_HOST, SYNC_USER, and SYNC_PATH in .env.');

            return self::FAILURE;
        }

        if ($this->option('fetch')) {
            $this->info('Fetching latest card data...');
            $this->call('cards:fetch');
        }

        $localJsonDir = config('import.vegapull_path').'/json';

        if (! File::isDirectory($localJsonDir)
            || (! File::exists($localJsonDir.'/packs.json') && File::glob($localJsonDir.'/cards_*.json') === [])
        ) {
            $this->error("No card JSON found at: {$localJsonDir}");

            return self::FAILURE;
        }

        $remoteJsonDir = "{$path}/storage/vegapull/json";

        $this->info('Ensuring remote json directory exists...');

        Process::run([
            'ssh', "{$user}@{$host}", '-p', $port,
            "mkdir -p {$remoteJsonDir}",
        ]);

        $this->info('Uploading card json to production...');

        $scpResult = Process::run(['scp', '-r', '-P', $port, "{$localJsonDir}/.", "{$user}@{$host}:{$remoteJsonDir}/"]);

        if ($scpResult->failed()) {
            $this->error('SCP failed: '.$scpResult->errorOutput());

            return self::FAILURE;
        }

        $this->info('Importing card data on production...');

        $importResult = Process::run([
            'ssh', "{$user}@{$host}", '-p', $port,
            "cd {$path} && {$php} artisan cards:import",
        ]);

        if ($importResult->failed()) {
            $this->error('Remote import failed: '.$importResult->errorOutput());

            return self::FAILURE;
        }

        $this->info('Clearing production cache...');

        $sshResult = Process::run([
            'ssh', "{$user}@{$host}", '-p', $port,
            "cd {$path} && {$php} artisan optimize:clear",
        ]);

        if ($sshResult->failed()) {
            $this->warn('Cache clear failed: '.$sshResult->errorOutput());
        }

        $this->info('Sync complete.');

        return self::SUCCESS;
    }
}
