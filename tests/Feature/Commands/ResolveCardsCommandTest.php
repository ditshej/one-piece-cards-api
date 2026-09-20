<?php

use App\Models\Card;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Tests\Support\BufferedConsoleOutput;
use Tests\Support\InteractiveResolveCardsCommand;

/**
 * Runs `cards:resolve` with separated stdout and stderr buffers.
 *
 * @param  array<string, mixed>  $parameters
 * @return array{status: int, stdout: string, stderr: string}
 */
function runResolve(array $parameters = [], ?string $stdin = null, ?SymfonyCommand $command = null): array
{
    $command ??= app(Kernel::class)->all()['cards:resolve'];

    $definition = $command->getDefinition();

    // Symfony merges the application definition into the command on its first run,
    // which adds the `command` argument a later run then has to satisfy.
    if ($definition->hasArgument('command')) {
        $parameters['command'] = $command->getName();
    }

    $input = new ArrayInput($parameters, $definition);

    if ($stdin !== null) {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $stdin);
        rewind($stream);
        $input->setStream($stream);
    }

    $output = new BufferedConsoleOutput;
    $status = $command->run($input, $output);

    return ['status' => $status, 'stdout' => $output->fetch(), 'stderr' => $output->errorFetch()];
}

/** @return array<string, mixed> */
function resolveJson(array $parameters = [], ?string $stdin = null): array
{
    $result = runResolve($parameters, $stdin);

    expect($result['status'])->toBe(0);

    return json_decode($result['stdout'], true, flags: JSON_THROW_ON_ERROR);
}

function deckListFile(string $contents): string
{
    $path = sys_get_temp_dir().'/'.uniqid('deck-', true).'.txt';
    file_put_contents($path, $contents);

    return $path;
}

/** Builds a legal list: one leader, 50 main deck cards across 13 entries. */
function legalDeckList(): string
{
    Card::factory()->create(['id' => 'OP09-062', 'name' => 'Nico Robin', 'category' => 'Leader']);

    $lines = ['1 OP09-062 Nico Robin'];
    $quantities = [4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 2];

    foreach ($quantities as $index => $quantity) {
        $id = sprintf('OP17-%03d', $index + 1);
        Card::factory()->create(['id' => $id, 'name' => "Card {$id}", 'category' => 'Character']);
        $lines[] = "{$quantity} {$id} Card {$id}";
    }

    return implode("\n", $lines)."\n";
}

describe('line formats', function () {
    it('resolves the compact quantity-x-id format', function () {
        Card::factory()->create(['id' => 'ST01-011', 'name' => 'Monkey.D.Luffy', 'category' => 'Character']);

        $payload = resolveJson(stdin: "4xST01-011\n");

        expect($payload['cards'])->toHaveCount(1)
            ->and($payload['cards'][0]['id'])->toBe('ST01-011')
            ->and($payload['cards'][0]['quantity'])->toBe(4);
    });

    it('resolves the deck-builder export format with a trailing name', function () {
        Card::factory()->create(['id' => 'OP17-113', 'name' => 'Streusen', 'category' => 'Character']);

        $payload = resolveJson(stdin: "4 OP17-113 Streusen\n");

        expect($payload['cards'][0]['id'])->toBe('OP17-113')
            ->and($payload['cards'][0]['quantity'])->toBe(4)
            ->and($payload['cards'][0]['name'])->toBe('Streusen');
    });

    it('ignores blank lines and surrounding whitespace', function () {
        Card::factory()->create(['id' => 'ST01-011', 'category' => 'Character']);
        Card::factory()->create(['id' => 'OP17-113', 'category' => 'Character']);

        $payload = resolveJson(stdin: "\n   4x ST01-011  \n\n\t2 OP17-113\n   \n");

        expect($payload['cards'])->toHaveCount(2)
            ->and($payload['totals']['cards'])->toBe(6);
    });

    it('resolves alt art identifiers', function () {
        Card::factory()->create(['id' => 'ST10-008_p3', 'category' => 'Character']);

        $payload = resolveJson(stdin: "1xST10-008_p3\n");

        expect($payload['cards'][0]['id'])->toBe('ST10-008_p3');
    });
});

describe('aborting errors', function () {
    it('aborts on a malformed line, naming the line number and content', function () {
        $result = runResolve(stdin: "4xST01-011\nnot a deck line\n");

        expect($result['status'])->not->toBe(0)
            ->and($result['stderr'])->toContain('2')
            ->and($result['stderr'])->toContain('not a deck line')
            ->and($result['stdout'])->toBe('');
    });

    it('aborts on a quantity below one', function () {
        Card::factory()->create(['id' => 'ST01-011', 'category' => 'Character']);

        $zero = runResolve(stdin: "0 ST01-011\n");
        $negative = runResolve(stdin: "-2 ST01-011\n");

        expect($zero['status'])->not->toBe(0)
            ->and($zero['stderr'])->toContain('1')
            ->and($negative['status'])->not->toBe(0);
    });

    it('aborts on a duplicate card ID', function () {
        Card::factory()->create(['id' => 'ST01-011', 'category' => 'Character']);

        $result = runResolve(stdin: "2 ST01-011\n2 ST01-011\n");

        expect($result['status'])->not->toBe(0)
            ->and($result['stderr'])->toContain('ST01-011');
    });

    it('reports all unknown card IDs together in one run', function () {
        Card::factory()->create(['id' => 'ST01-011', 'category' => 'Character']);

        $result = runResolve(stdin: "1 ST01-011\n1 OP99-001\n1 OP99-002\n1 OP99-003\n");

        expect($result['status'])->not->toBe(0)
            ->and($result['stderr'])->toContain('OP99-001')
            ->and($result['stderr'])->toContain('OP99-002')
            ->and($result['stderr'])->toContain('OP99-003');
    });

    it('aborts when the given file does not exist', function () {
        $result = runResolve(['file' => '/tmp/does-not-exist-deck.txt']);

        expect($result['status'])->not->toBe(0)
            ->and($result['stderr'])->toContain('/tmp/does-not-exist-deck.txt');
    });

    it('aborts on an unsupported format', function () {
        $result = runResolve(['--format' => 'xml'], stdin: '');

        expect($result['status'])->not->toBe(0)
            ->and($result['stderr'])->toContain('json')
            ->and($result['stderr'])->toContain('markdown');
    });
});

describe('warnings', function () {
    it('warns when a trailing name disagrees with the database', function () {
        Card::factory()->create(['id' => 'ST34-003', 'name' => 'Charlotte Brulee', 'category' => 'Character']);

        $result = runResolve(stdin: "2 ST34-003 Charlotte Brulée\n");

        expect($result['status'])->toBe(0)
            ->and($result['stderr'])->toContain('Charlotte Brulée')
            ->and($result['stderr'])->toContain('Charlotte Brulee');

        $payload = json_decode($result['stdout'], true);

        expect($payload['cards'][0]['name'])->toBe('Charlotte Brulee')
            ->and($payload['warnings'])->toHaveCount(1);
    });

    it('emits no composition warnings for a short list without --deck', function () {
        collect(range(1, 5))->each(fn (int $i) => Card::factory()->create([
            'id' => sprintf('OP01-%03d', $i), 'name' => "Card {$i}", 'category' => 'Character',
        ]));

        $result = runResolve(stdin: collect(range(1, 5))
            ->map(fn (int $i) => sprintf('1 OP01-%03d', $i))
            ->implode("\n"));

        expect($result['status'])->toBe(0)
            ->and($result['stderr'])->toBe('')
            ->and(json_decode($result['stdout'], true)['warnings'])->toBe([]);
    });

    it('warns with --deck when there is no leader', function () {
        Card::factory()->create(['id' => 'OP01-001', 'category' => 'Character']);

        $result = runResolve(['--deck' => true], "4 OP01-001\n");

        expect($result['status'])->toBe(0)
            ->and($result['stderr'])->toContain('leader');

        $payload = json_decode($result['stdout'], true);

        expect($payload['leader'])->toBeNull();
    });

    it('warns with --deck when there are two leaders', function () {
        Card::factory()->create(['id' => 'OP09-062', 'category' => 'Leader']);
        Card::factory()->create(['id' => 'ST01-001', 'category' => 'Leader']);

        $result = runResolve(['--deck' => true], "1 OP09-062\n1 ST01-001\n");

        expect($result['status'])->toBe(0)
            ->and($result['stderr'])->toContain('OP09-062')
            ->and($result['stderr'])->toContain('ST01-001');
    });

    it('warns with --deck when the main deck is not 50 cards', function () {
        Card::factory()->create(['id' => 'OP09-062', 'category' => 'Leader']);
        Card::factory()->create(['id' => 'OP01-001', 'category' => 'Character']);

        $result = runResolve(['--deck' => true], "1 OP09-062\n4 OP01-001\n");

        expect($result['status'])->toBe(0)
            ->and($result['stderr'])->toContain('4')
            ->and($result['stderr'])->toContain('50');
    });

    it('warns with --deck when a card exceeds four copies', function () {
        Card::factory()->create(['id' => 'OP01-001', 'category' => 'Character']);

        $result = runResolve(['--deck' => true], "5 OP01-001\n");

        expect($result['status'])->toBe(0)
            ->and($result['stderr'])->toContain('OP01-001');
    });

    it('emits no warnings for a legal deck resolved with --deck', function () {
        $list = legalDeckList();

        $result = runResolve(['--deck' => true], $list);

        expect($result['status'])->toBe(0)
            ->and($result['stderr'])->toBe('')
            ->and(json_decode($result['stdout'], true)['warnings'])->toBe([]);
    });

    it('keeps warnings out of stdout', function () {
        Card::factory()->create(['id' => 'ST34-003', 'name' => 'Charlotte Brulee', 'category' => 'Character']);

        $result = runResolve(stdin: "2 ST34-003 Wrong Name\n");

        expect($result['stderr'])->toContain('Wrong Name')
            ->and(json_decode($result['stdout'], true))->toBeArray();
    });
});

describe('input sources', function () {
    it('reads the deck list from a file argument', function () {
        Card::factory()->create(['id' => 'ST01-011', 'category' => 'Character']);

        $path = deckListFile("4xST01-011\n");

        $payload = resolveJson(['file' => $path]);

        expect($payload['cards'][0]['id'])->toBe('ST01-011');

        unlink($path);
    });

    it('reads the deck list from piped standard input', function () {
        Card::factory()->create(['id' => 'ST01-011', 'category' => 'Character']);

        $payload = resolveJson(stdin: "4xST01-011\n");

        expect($payload['cards'][0]['id'])->toBe('ST01-011');
    });

    it('reads standard input when the file argument is a dash', function () {
        Card::factory()->create(['id' => 'ST01-011', 'category' => 'Character']);

        $payload = resolveJson(['file' => '-'], "4xST01-011\n");

        expect($payload['cards'][0]['id'])->toBe('ST01-011');
    });

    it('prints a paste hint to stderr when standard input is a terminal', function () {
        Card::factory()->create(['id' => 'ST01-011', 'category' => 'Character']);

        $command = new InteractiveResolveCardsCommand;
        $command->setLaravel(app());

        $result = runResolve(stdin: "4xST01-011\n", command: $command);

        expect($result['status'])->toBe(0)
            ->and($result['stderr'])->toContain('Ctrl-D')
            ->and(json_decode($result['stdout'], true)['cards'][0]['id'])->toBe('ST01-011');
    });
});

describe('resolution', function () {
    it('resolves a multi-card list with a single database query', function () {
        collect(range(1, 5))->each(fn (int $i) => Card::factory()->create([
            'id' => sprintf('OP01-%03d', $i), 'category' => 'Character',
        ]));

        $stdin = collect(range(1, 5))->map(fn (int $i) => sprintf('2 OP01-%03d', $i))->implode("\n");

        DB::enableQueryLog();
        DB::flushQueryLog();

        runResolve(stdin: $stdin);

        expect(DB::getQueryLog())->toHaveCount(1);

        DB::disableQueryLog();
    });

    it('separates the leader by category even when it is not on the first line', function () {
        Card::factory()->create(['id' => 'OP01-001', 'category' => 'Character']);
        Card::factory()->create(['id' => 'OP09-062', 'name' => 'Nico Robin', 'category' => 'Leader']);
        Card::factory()->create(['id' => 'OP01-002', 'category' => 'Character']);

        $payload = resolveJson(stdin: "4 OP01-001\n1 OP09-062 Nico Robin\n4 OP01-002\n");

        expect($payload['leader']['id'])->toBe('OP09-062')
            ->and($payload['cards'])->toHaveCount(2)
            ->and(array_column($payload['cards'], 'id'))->toBe(['OP01-001', 'OP01-002'])
            ->and($payload['totals']['cards'])->toBe(8);
    });

    it('outputs all thirteen fields per entry and preserves null', function () {
        Card::factory()->create([
            'id' => 'OP01-001',
            'name' => 'Roronoa Zoro',
            'category' => 'Character',
            'colors' => ['Red'],
            'cost' => 3,
            'power' => 5000,
            'counter' => null,
            'types' => ['Straw Hat Crew'],
            'effect' => 'Some effect',
            'trigger' => null,
            'rarity' => 'SR',
            'card_set' => 'OP01',
        ]);

        $payload = resolveJson(stdin: "4 OP01-001\n");

        expect(array_keys($payload['cards'][0]))->toBe([
            'quantity', 'id', 'name', 'category', 'colors', 'cost', 'power',
            'counter', 'types', 'effect', 'trigger', 'rarity', 'card_set',
        ])
            ->and($payload['cards'][0]['counter'])->toBeNull()
            ->and($payload['cards'][0]['trigger'])->toBeNull()
            ->and($payload['cards'][0]['colors'])->toBe(['Red'])
            ->and($payload['cards'][0]['power'])->toBe(5000);
    });
});

describe('output formats', function () {
    it('produces the documented JSON shape', function () {
        Card::factory()->create(['id' => 'OP09-062', 'name' => 'Nico Robin', 'category' => 'Leader']);
        Card::factory()->create(['id' => 'OP17-113', 'name' => 'Streusen', 'category' => 'Character']);

        $payload = resolveJson(stdin: "1 OP09-062 Nico Robin\n4 OP17-113 Streusen\n");

        expect(array_keys($payload))->toBe(['leader', 'cards', 'totals', 'warnings'])
            ->and($payload['leader']['id'])->toBe('OP09-062')
            ->and($payload['totals'])->toBe(['leader' => 1, 'cards' => 4, 'distinct' => 1])
            ->and($payload['warnings'])->toBe([]);
    });

    it('sets leader to null when the list contains none', function () {
        Card::factory()->create(['id' => 'OP17-113', 'category' => 'Character']);

        $payload = resolveJson(stdin: "4 OP17-113\n");

        expect($payload['leader'])->toBeNull()
            ->and($payload['totals']['leader'])->toBe(0);
    });

    it('writes JSON without console style mangling of angle brackets', function () {
        Card::factory()->create([
            'id' => 'OP17-113', 'name' => 'Streusen', 'category' => 'Character',
            'effect' => '[On Play] <Draw> a card.',
        ]);

        $payload = resolveJson(stdin: "4 OP17-113\n");

        expect($payload['cards'][0]['effect'])->toBe('[On Play] <Draw> a card.');
    });

    it('produces the documented Markdown shape', function () {
        Card::factory()->create([
            'id' => 'OP09-062', 'name' => 'Nico Robin', 'category' => 'Leader', 'effect' => 'Leader effect',
        ]);
        Card::factory()->create([
            'id' => 'OP17-113', 'name' => 'Streusen', 'category' => 'Character',
            'effect' => '[On Play] Draw a card.', 'trigger' => 'Play this card.',
        ]);

        $result = runResolve(['--format' => 'markdown'], "1 OP09-062 Nico Robin\n4 OP17-113 Streusen\n");

        expect($result['status'])->toBe(0)
            ->and($result['stdout'])->toContain('## Leader')
            ->and($result['stdout'])->toContain('Nico Robin')
            ->and($result['stdout'])->toContain('| Qty | ID | Name |')
            ->and($result['stdout'])->toContain('| 4 | OP17-113 | Streusen |')
            ->and($result['stdout'])->toContain('## Effects')
            ->and($result['stdout'])->toContain('[On Play] Draw a card.')
            ->and($result['stdout'])->toContain('Play this card.');
    });
});
