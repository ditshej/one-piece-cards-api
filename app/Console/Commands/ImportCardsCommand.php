<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Models\Pack;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('cards:import {path?}')]
#[Description('Import card data from vegapull JSON files')]
class ImportCardsCommand extends Command
{
    public function handle(): int
    {
        $path = $this->argument('path') ?? config('import.vegapull_path');

        $files = File::glob($path.'/*.json');

        if (empty($files)) {
            $this->warn('No JSON files found in: '.$path);

            return self::SUCCESS;
        }

        $importedCardCount = 0;

        foreach ($files as $file) {
            $cards = json_decode(File::get($file), true);

            if (empty($cards)) {
                $this->warn('Skipping empty or invalid file: '.basename($file));

                continue;
            }

            $packId = $cards[0]['pack_id'];
            $packName = $cards[0]['pack_name'] ?? $packId;

            Pack::updateOrCreate(
                ['id' => $packId],
                ['name' => $packName],
            );

            foreach ($cards as $cardData) {
                Card::updateOrCreate(
                    ['id' => $cardData['id']],
                    [
                        'pack_id' => $cardData['pack_id'],
                        'name' => $cardData['name'],
                        'rarity' => $cardData['rarity'],
                        'category' => $cardData['category'],
                        'colors' => $cardData['colors'],
                        'cost' => $cardData['cost'],
                        'power' => $cardData['power'],
                        'counter' => $cardData['counter'],
                        'attributes' => $cardData['attributes'],
                        'types' => $cardData['types'],
                        'effect' => $cardData['effect'],
                        'trigger' => $cardData['trigger'],
                        'img_url' => $cardData['img_url'],
                    ],
                );

                $importedCardCount++;
            }
        }

        $this->info("Imported {$importedCardCount} cards from ".count($files).' file(s).');

        return self::SUCCESS;
    }
}
