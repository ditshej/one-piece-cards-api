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

        $cardFiles = File::glob($jsonPath.'/'.config('import.vegapull_cards_glob'));

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
                    'name' => $this->decodeText($packData['title_parts']['title'] ?? null) ?? $packId,
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
            'card_set' => explode('-', $cardData['id'])[0],
            'name' => $this->decodeText($cardData['name']),
            'rarity' => $cardData['rarity'],
            'category' => $cardData['category'],
            'colors' => $cardData['colors'],
            'cost' => ($cardData['cost'] === null && $cardData['category'] === 'Event') ? 0 : $cardData['cost'],
            'power' => $cardData['power'],
            'counter' => $cardData['counter'],
            'attributes' => $cardData['attributes'],
            'types' => $this->normalizeTypes($cardData['types']),
            'effect' => $this->decodeText($cardData['effect']),
            'trigger' => $this->stripLabel($this->decodeText($cardData['trigger']), 'Trigger '),
            'img_url' => $cardData['img_full_url'] ?? $cardData['img_url'],
            'alt_art_variant' => preg_match('/_p(\d+)$/', $cardData['id'], $m) ? (int) $m[1] : null,
        ];
    }

    /**
     * Removes the scraped `Type ` label that vegapull leaves in the type values.
     * Only the first element carries it, but stripping every value is safe either way.
     *
     * @param  array<int, string>  $types
     * @return array<int, string>
     */
    private function normalizeTypes(array $types): array
    {
        return collect($types)
            ->map(fn (string $type): ?string => $this->stripLabel($this->decodeText($type), 'Type '))
            ->all();
    }

    /**
     * Removes a scraped label prefix. Keeps the trailing space in the prefix so
     * that genuine values such as `Typhoon` stay untouched, and is a no-op once
     * vegapull stops emitting the label.
     */
    private function stripLabel(?string $value, string $label): ?string
    {
        if ($value === null) {
            return null;
        }

        return str_starts_with($value, $label) ? substr($value, strlen($label)) : $value;
    }

    private function decodeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * @return array<string, array{id: string, raw_title: string, title_parts: array{prefix: string, title: string, label: string}}>
     */
    private function loadPacks(string $jsonPath): array
    {
        $packsFile = $jsonPath.'/'.config('import.vegapull_packs_file');

        if (! File::exists($packsFile)) {
            return [];
        }

        return json_decode(File::get($packsFile), true) ?? [];
    }
}
