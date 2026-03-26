<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

#[Signature('cards:fetch')]
#[Description('Fetch card data from Bandai via vegapull and import')]
class FetchCardsCommand extends Command
{
    public function handle(): int
    {
        $binary = config('import.vegapull_binary');
        $path = config('import.vegapull_path');

        $versionResult = Process::run("{$binary} --version");

        if ($versionResult->failed()) {
            $this->error("Vegapull binary '{$binary}' not found. Install with: cargo install vegapull");

            return self::FAILURE;
        }

        $this->info('Fetching pack list from Bandai...');

        $packsResult = Process::timeout(60)->run("{$binary} pull --language english -o {$path} packs");

        if ($packsResult->failed()) {
            $this->error('Failed to fetch packs: '.$packsResult->errorOutput());

            return self::FAILURE;
        }

        $packIds = $this->getPackIds($path);

        $this->info('Fetching cards for '.count($packIds).' packs...');

        foreach ($packIds as $packId) {
            $result = Process::timeout(120)->run("{$binary} pull --language english -o {$path} cards {$packId}");

            if ($result->failed()) {
                $this->warn("Failed to fetch cards for pack {$packId}, skipping.");

                continue;
            }
        }

        Artisan::call('cards:import', [], $this->output);

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function getPackIds(string $path): array
    {
        $packsFile = $path.'/json/packs.json';

        if (! File::exists($packsFile)) {
            return [];
        }

        $packs = json_decode(File::get($packsFile), true) ?? [];

        return array_keys($packs);
    }
}
