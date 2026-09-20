<?php

namespace App\Console\Commands;

use App\Models\Card;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[Signature('cards:resolve {file?} {--format=json} {--deck}')]
#[Description('Resolve a list of card numbers against the local card database')]
class ResolveCardsCommand extends Command
{
    private const FORMATS = ['json', 'markdown'];

    private const COMPACT_LINE = '/^(-?\d+)\s*[xX]\s*([A-Za-z0-9-]+(?:_[a-z0-9]+)?)$/';

    private const EXPORT_LINE = '/^(-?\d+)\s+([A-Za-z0-9-]+(?:_[a-z0-9]+)?)(?:\s+(.*))?$/';

    private const MAIN_DECK_SIZE = 50;

    private const MAX_COPIES = 4;

    /** @var list<string> */
    private array $warnings = [];

    public function handle(): int
    {
        $format = $this->option('format');

        if (! in_array($format, self::FORMATS, true)) {
            return $this->abortWith('Unsupported --format value "'.$format.'". Supported values: '.implode(', ', self::FORMATS).'.');
        }

        $list = $this->readList();

        if ($list === null) {
            return self::FAILURE;
        }

        ['entries' => $entries, 'errors' => $parseErrors] = $this->parse($list);

        if ($parseErrors !== []) {
            return $this->abortWith(...$parseErrors);
        }

        $ids = collect($entries)->pluck('id');

        $cards = Card::whereIn('id', $ids)->get()->keyBy('id');

        $unknownIds = $ids->diff($cards->keys())->values()->all();

        if ($unknownIds !== []) {
            return $this->abortWith(
                'Unknown card IDs: '.implode(', ', $unknownIds).'.',
                'The local database may be out of date — run "php artisan cards:fetch" to update it.',
            );
        }

        $resolved = collect($entries)->map(function (array $entry) use ($cards): array {
            $card = $cards[$entry['id']];

            $this->checkName($entry, $card);

            return $this->resolveEntry($entry, $card);
        });

        [$leaders, $deck] = $resolved->partition(fn (array $entry): bool => $entry['category'] === 'Leader');

        if ($this->option('deck')) {
            $this->checkComposition($leaders, $deck);
        }

        $payload = [
            'leader' => $leaders->first(),
            'cards' => $deck->values()->all(),
            'totals' => [
                'leader' => (int) $leaders->sum('quantity'),
                'cards' => (int) $deck->sum('quantity'),
                'distinct' => $deck->count(),
            ],
            'warnings' => $this->warnings,
        ];

        $this->writeWarnings();

        $document = $format === 'json' ? $this->toJson($payload) : $this->toMarkdown($payload);

        $this->output->writeln($document, OutputInterface::OUTPUT_RAW);

        return self::SUCCESS;
    }

    /**
     * Reads the deck list from the file argument, or from standard input when the
     * argument is omitted or given as `-`. Returns null when the file is missing.
     */
    private function readList(): ?string
    {
        $file = $this->argument('file');

        if ($file !== null && $file !== '-') {
            if (! File::exists($file)) {
                $this->abortWith('Deck list file not found: '.$file);

                return null;
            }

            return File::get($file);
        }

        $stream = $this->stdinStream();

        if ($file === null && $this->stdinIsInteractive($stream)) {
            $this->writeToError('Paste the deck list, then press Ctrl-D.');
        }

        return (string) stream_get_contents($stream);
    }

    /** @return resource */
    private function stdinStream()
    {
        $stream = $this->input instanceof StreamableInputInterface ? $this->input->getStream() : null;

        return $stream ?? (defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
    }

    /**
     * Whether standard input is a terminal, meaning the user is expected to paste
     * the list by hand.
     *
     * @param  resource  $stream
     */
    protected function stdinIsInteractive($stream): bool
    {
        return stream_isatty($stream);
    }

    /**
     * @return array{entries: list<array{line: int, quantity: int, id: string, name: ?string}>, errors: list<string>}
     */
    private function parse(string $list): array
    {
        $errors = [];
        $entries = [];
        $seen = [];

        foreach (preg_split('/\R/', $list) as $index => $rawLine) {
            $line = trim($rawLine);
            $number = $index + 1;

            if ($line === '') {
                continue;
            }

            if (! preg_match(self::COMPACT_LINE, $line, $matches) && ! preg_match(self::EXPORT_LINE, $line, $matches)) {
                $errors[] = "Line {$number}: cannot parse \"{$line}\". Expected \"<quantity>x<card-id>\" or \"<quantity> <card-id> [card name]\".";

                continue;
            }

            $quantity = (int) $matches[1];
            $id = $matches[2];

            if ($quantity < 1) {
                $errors[] = "Line {$number}: quantity must be at least 1, got {$quantity}.";

                continue;
            }

            if (isset($seen[$id])) {
                $errors[] = "Line {$number}: duplicate card ID {$id}, first seen on line {$seen[$id]}.";

                continue;
            }

            $seen[$id] = $number;

            $entries[] = [
                'line' => $number,
                'quantity' => $quantity,
                'id' => $id,
                'name' => isset($matches[3]) && trim($matches[3]) !== '' ? trim($matches[3]) : null,
            ];
        }

        return ['entries' => $entries, 'errors' => $errors];
    }

    /**
     * @param  array{line: int, quantity: int, id: string, name: ?string}  $entry
     * @return array<string, mixed>
     */
    private function resolveEntry(array $entry, Card $card): array
    {
        return [
            'quantity' => $entry['quantity'],
            'id' => $card->id,
            'name' => $card->name,
            'category' => $card->category,
            'colors' => $card->colors,
            'cost' => $card->cost,
            'power' => $card->power,
            'counter' => $card->counter,
            'types' => $card->types,
            'effect' => $card->effect,
            'trigger' => $card->trigger,
            'rarity' => $card->rarity,
            'card_set' => $card->card_set,
        ];
    }

    /** @param array{line: int, quantity: int, id: string, name: ?string} $entry */
    private function checkName(array $entry, Card $card): void
    {
        if ($entry['name'] === null || $entry['name'] === $card->name) {
            return;
        }

        $this->warnings[] = "Line {$entry['line']}: name \"{$entry['name']}\" does not match \"{$card->name}\" stored for {$card->id}.";
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $leaders
     * @param  Collection<int, array<string, mixed>>  $deck
     */
    private function checkComposition(Collection $leaders, Collection $deck): void
    {
        if ($leaders->isEmpty()) {
            $this->warnings[] = 'No leader found in the list.';
        }

        if ($leaders->count() > 1) {
            $this->warnings[] = 'Expected exactly one leader, found '.$leaders->count().': '.$leaders->pluck('id')->implode(', ').'.';
        }

        $total = (int) $deck->sum('quantity');

        if ($total !== self::MAIN_DECK_SIZE) {
            $this->warnings[] = "Main deck has {$total} cards, expected ".self::MAIN_DECK_SIZE.'.';
        }

        $deck->filter(fn (array $entry): bool => $entry['quantity'] > self::MAX_COPIES)
            ->each(function (array $entry): void {
                $this->warnings[] = "Card {$entry['id']} appears {$entry['quantity']} times, at most ".self::MAX_COPIES.' copies are allowed.';
            });
    }

    /** @param array<string, mixed> $payload */
    private function toJson(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** @param array<string, mixed> $payload */
    private function toMarkdown(array $payload): string
    {
        $sections = ['# Resolved Cards'];

        if ($payload['leader'] !== null) {
            $sections[] = "## Leader\n\n".$this->markdownSummary($payload['leader']);
        }

        if ($payload['cards'] !== []) {
            $sections[] = "## Cards\n\n".$this->markdownTable($payload['cards']);
        }

        $withText = collect($payload['leader'] === null ? [] : [$payload['leader']])
            ->concat($payload['cards'])
            ->filter(fn (array $entry): bool => $entry['effect'] !== null || $entry['trigger'] !== null);

        if ($withText->isNotEmpty()) {
            $sections[] = "## Effects\n\n".$withText->map(fn (array $entry): string => $this->markdownCardText($entry))->implode("\n\n");
        }

        $sections[] = "## Totals\n\n- Leader: {$payload['totals']['leader']}\n"
            ."- Cards: {$payload['totals']['cards']} ({$payload['totals']['distinct']} distinct)";

        if ($payload['warnings'] !== []) {
            $sections[] = "## Warnings\n\n".collect($payload['warnings'])->map(fn (string $warning): string => "- {$warning}")->implode("\n");
        }

        return implode("\n\n", $sections)."\n";
    }

    /** @param array<string, mixed> $entry */
    private function markdownSummary(array $entry): string
    {
        return "**{$entry['id']}** — {$entry['name']}\n\n"
            .'- Colors: '.$this->markdownValue($entry['colors'])."\n"
            .'- Cost: '.$this->markdownValue($entry['cost']).' · Power: '.$this->markdownValue($entry['power'])
            .' · Counter: '.$this->markdownValue($entry['counter'])."\n"
            .'- Types: '.$this->markdownValue($entry['types'])."\n"
            .'- Rarity: '.$this->markdownValue($entry['rarity']).' · Set: '.$this->markdownValue($entry['card_set']);
    }

    /** @param list<array<string, mixed>> $entries */
    private function markdownTable(array $entries): string
    {
        $rows = collect($entries)->map(fn (array $entry): string => '| '.implode(' | ', [
            $entry['quantity'],
            $entry['id'],
            $entry['name'],
            $this->markdownValue($entry['category']),
            $this->markdownValue($entry['colors']),
            $this->markdownValue($entry['cost']),
            $this->markdownValue($entry['power']),
            $this->markdownValue($entry['counter']),
            $this->markdownValue($entry['types']),
            $this->markdownValue($entry['rarity']),
            $this->markdownValue($entry['card_set']),
        ]).' |');

        return "| Qty | ID | Name | Category | Colors | Cost | Power | Counter | Types | Rarity | Set |\n"
            ."| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |\n"
            .$rows->implode("\n");
    }

    /** @param array<string, mixed> $entry */
    private function markdownCardText(array $entry): string
    {
        $block = "### {$entry['id']} — {$entry['name']}";

        if ($entry['effect'] !== null) {
            $block .= "\n\n**Effect:** {$entry['effect']}";
        }

        if ($entry['trigger'] !== null) {
            $block .= "\n\n**Trigger:** {$entry['trigger']}";
        }

        return $block;
    }

    private function markdownValue(mixed $value): string
    {
        if (is_array($value)) {
            return $value === [] ? '-' : implode(', ', $value);
        }

        return $value === null ? '-' : (string) $value;
    }

    private function writeWarnings(): void
    {
        foreach ($this->warnings as $warning) {
            $this->writeToError('Warning: '.$warning);
        }
    }

    private function abortWith(string ...$messages): int
    {
        foreach ($messages as $message) {
            $this->writeToError($message);
        }

        return self::FAILURE;
    }

    /**
     * Writes to standard error so that redirecting standard output to a file
     * yields the resolved document alone.
     */
    private function writeToError(string $message): void
    {
        $output = $this->output->getOutput();

        $target = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $target->writeln($message, OutputInterface::OUTPUT_RAW);
    }
}
