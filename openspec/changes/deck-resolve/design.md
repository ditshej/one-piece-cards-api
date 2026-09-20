## Context

The `cards` table holds all card data (`app/Models/Card.php`), keyed by the card number as a string primary key (`$incrementing = false`, `$keyType = 'string'`), with `colors`, `attributes` and `types` cast to arrays. Everything the command needs is one `whereIn` away.

The existing console commands set the pattern to follow: PHP attributes for signature and description, a `handle(): int` returning `self::SUCCESS` / `self::FAILURE`, and private helper methods rather than extracted classes while the command is the only consumer (`ImportCardsCommand`, `SyncCardsCommand`).

A real deck list, used to validate the design:

```
1 OP09-062 Nico Robin          <- Leader (category = Leader)
2 ST34-003 Charlotte Brulee
4 OP17-113 Streusen
...                             51 lines of quantity -> 1 leader + 50 main deck
```

## Goals / Non-Goals

**Goals:**
- Turn a bare deck list into complete card data, offline, with no external calls.
- Produce output that is immediately usable both by a machine (JSON) and by a reader (Markdown).
- Fail loudly on input that cannot be trusted; merely warn on input that is trustworthy but unusual.
- Keep redirected output clean, so `> deck-data.json` yields valid JSON and nothing else.

**Non-Goals:**
- Validating deck legality as a rules engine. Composition checks are advisory.
- Supporting every deck-builder export format in existence. Two formats cover the actual need.
- Any network access.

## Decisions

### Two accepted line formats, one regex each

```
^(\d+)\s*[xX]\s*([A-Za-z0-9-]+(?:_[a-z0-9]+)?)$      ->  4xST01-011
^(\d+)\s+([A-Za-z0-9-]+(?:_[a-z0-9]+)?)(?:\s+(.*))?$ ->  4 OP17-113 Streusen
```

Blank lines and surrounding whitespace are ignored. Any other line is a parse error naming the line number and its content.

- **Why two formats:** `4xST01-011` is the compact form people type by hand; `4 OP17-113 Streusen` is what deck builders export. Supporting both costs one extra regex and removes a manual conversion step.
- **Why the optional `_p1` suffix in the ID pattern:** alt-art variants are real IDs in this database (`ST10-008_p3`), and a list containing one should resolve rather than fail.
- **Trailing name is informational.** It is compared against the database name and a mismatch produces a warning, never an error. This catches a stale or hand-edited list — a renamed or mistyped card is worth flagging — without making the command reject input whose card numbers are perfectly valid. The card number is the identity; the name is a comment.

### Errors abort, composition problems warn

Two distinct classes, because they mean different things:

| Condition | Behaviour |
|---|---|
| Malformed line | error, abort |
| Quantity below 1 | error, abort |
| Duplicate card ID | error, abort |
| Card ID not in database | error, abort (all unknown IDs listed at once) |
| No leader, or more than one | warning, output still produced |
| Main deck not exactly 50 cards | warning |
| More than 4 copies of a card | warning |
| Trailing name disagrees with database | warning |

- **Why:** The first group means the input cannot be resolved correctly — carrying on would produce output that silently misrepresents the list. The second group means the input resolved fine but describes a deck that would not be tournament-legal. That is a judgement about the game, not about the data, and a partial or experimental list is a legitimate thing to resolve. Aborting on it would make the command useless during deck building, which is exactly when it is most wanted.
- **All unknown IDs are collected and reported together** rather than failing at the first one, so a list with three typos takes one run to fix, not three.

### Warnings to stderr, payload to stdout

`$this->output->getErrorOutput()->writeln(...)` for warnings; the JSON or Markdown document to stdout.

- **Why:** the intended use is `php artisan deck:resolve deck.txt > deck-data.json`. If warnings went to stdout they would corrupt the file. This way the file is always clean and the warnings still reach the terminal.

### Raw output, not `$this->line()`

Write the payload with `OutputInterface::OUTPUT_RAW`.

- **Why:** Symfony Console interprets `<...>` as style tags and would mangle or crash on card text containing angle brackets. Raw output also guarantees byte-exact JSON.

### One query, grouped in PHP

`Card::whereIn('id', $ids)->get()->keyBy('id')`, then the deck is assembled in memory.

- **Why:** a deck is at most ~20 distinct cards; a single indexed query on the primary key is the whole cost. No N+1, no per-card lookups.

### Leader determined by category, not by position

The leader is the entry whose card has `category === 'Leader'`.

- **Why:** deck-builder exports usually put the leader first, but not always, and a hand-written list may not. The database already knows what a leader is; relying on that is both simpler and correct. It also makes "no leader" and "two leaders" detectable rather than silently assumed.

### JSON shape

```json
{
  "leader": { "quantity": 1, "id": "OP09-062", "name": "Nico Robin", "...": "..." },
  "main_deck": [ { "quantity": 4, "id": "OP17-113", "...": "..." } ],
  "totals": { "leader": 1, "main_deck": 50, "distinct_main_deck": 14 },
  "warnings": []
}
```

`leader` is `null` when the list contains none. `main_deck` keeps the input order, so a diff between two resolved files stays readable. Warnings appear both on stderr and in the JSON, so a downstream consumer sees them too.

### Markdown shape

A leader block, a table of the main deck with the short fields, then the effect and trigger text per card in a separate section.

- **Why not effects in the table:** effect text runs to several lines; inside a table cell it destroys readability. Short fields belong in a table where they can be scanned and compared; prose belongs underneath.

### Formatting stays in the command

Private methods, no formatter classes, matching `ImportCardsCommand`.

- **Why:** two formats and one consumer. Extracting a formatter hierarchy now would add indirection without removing duplication. If a third format or a second consumer appears, extract then.

## Risks / Trade-offs

- **A stale local database yields "unknown card ID" for a card that genuinely exists.** Acceptable and visible: the error names the ID, and `php artisan cards:fetch && php artisan cards:import` is the fix. Worth mentioning in the error message.
- **Composition warnings are advisory**, so a malformed deck still produces output. Intended — see the decision above — and the warnings make it obvious.
- **The trailing name is not authoritative**, so a list pairing the right number with the wrong name resolves to the number. The mismatch warning is what surfaces this.

## Migration Plan

None. New command, no schema change, no existing behaviour touched.

## Open Questions

<!-- none -->
