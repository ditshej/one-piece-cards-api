<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

#[Signature('cards:sync {--fetch : Run cards:fetch before syncing}')]
#[Description('Sync local SQLite database to the production server')]
class SyncCardsCommand extends Command
{
    public function handle(): int
    {
        $host = config('import.sync_host');
        $user = config('import.sync_user');
        $port = config('import.sync_port');
        $path = config('import.sync_path');

        if (! $host || ! $user || ! $path) {
            $this->error('Sync config missing. Set SYNC_HOST, SYNC_USER, and SYNC_PATH in .env.');

            return self::FAILURE;
        }

        if ($this->option('fetch')) {
            $this->info('Fetching latest card data...');
            $this->call('cards:fetch');
        }

        $localDb = database_path('database.sqlite');
        $remoteDb = "{$path}/database/database.sqlite";

        $this->info('Uploading database to production...');

        $scpResult = Process::run("scp -P {$port} {$localDb} {$user}@{$host}:{$remoteDb}");

        if ($scpResult->failed()) {
            $this->error('SCP failed: '.$scpResult->errorOutput());

            return self::FAILURE;
        }

        $this->info('Clearing production cache...');

        $sshResult = Process::run("ssh {$user}@{$host} -p {$port} 'cd {$path} && /opt/php83/bin/php artisan optimize:clear'");

        if ($sshResult->failed()) {
            $this->warn('Cache clear failed: '.$sshResult->errorOutput());
        }

        $this->info('Sync complete.');

        return self::SUCCESS;
    }
}
