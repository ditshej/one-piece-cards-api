<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

#[Signature('cards:fetch')]
#[Description('Fetch card data from Bandai via vegapull and import')]
class FetchCardsCommand extends Command
{
    public function handle(): int
    {
        $binary = config('import.vegapull_binary');
        $path = config('import.vegapull_path');

        $whichResult = Process::run("which {$binary}");

        if ($whichResult->failed()) {
            $this->error("Vegapull binary '{$binary}' not found. Install with: cargo install vegapull");

            return self::FAILURE;
        }

        $this->info('Fetching card data from Bandai...');

        $result = Process::timeout(300)->run("{$binary} pull all -o {$path}");

        if ($result->failed()) {
            $this->error('Vegapull scrape failed: '.$result->errorOutput());

            return self::FAILURE;
        }

        Artisan::call('cards:import', [], $this->output);

        return self::SUCCESS;
    }
}
