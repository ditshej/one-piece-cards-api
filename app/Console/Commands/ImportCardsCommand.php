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
        $jsonPath = $path.'/json';

        $packs = $this->loadPacks($jsonPath);

        $cardFiles = File::glob($jsonPath.'/cards_*.json');

        if (empty($cardFiles)) {
            $this->warn('No card JSON files found in: '.$jsonPath);

            return self::SUCCESS;
        }

        $importedCardCount = 0;

        foreach ($cardFiles as $file) {
            $cards = json_decode(File::get($file), true);

            if (empty($cards)) {
                $this->warn('Skipping empty or invalid file: '.basename($file));

                continue;
            }

            $packId = $cards[0]['pack_id'];
            $packData = $packs[$packId] ?? null;

            Pack::updateOrCreate(
                ['id' => $packId],
                [
                    'name' => $packData['title_parts']['title'] ?? $packId,
                    'label' => $packData['title_parts']['label'] ?? null,
                ],
            );

            foreach ($cards as $cardData) {
                Card::updateOrCreate(
                    ['id' => $cardData['id']],
                    $this->cardAttributes($cardData),
                );

                $importedCardCount++;
            }
        }

        $this->info("Imported {$importedCardCount} cards from ".count($cardFiles).' file(s).');

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $cardData */
    private function cardAttributes(array $cardData): array
    {
        return [
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
            'img_url' => $cardData['img_full_url'] ?? $cardData['img_url'],
        ];
    }

    /**
     * @return array<string, array{id: string, raw_title: string, title_parts: array{prefix: string, title: string, label: string}}>
     */
    private function loadPacks(string $jsonPath): array
    {
        $packsFile = $jsonPath.'/packs.json';

        if (! File::exists($packsFile)) {
            return [];
        }

        return json_decode(File::get($packsFile), true) ?? [];
    }
}
