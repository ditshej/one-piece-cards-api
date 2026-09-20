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
- Turn a bare list of card numbers into complete card data, offline, with no external calls.
- Work as well for five looked-up cards as for a full deck; the deck case is the special case, not the default.
- Produce output that is immediately usable both by a machine (JSON) and by a reader (Markdown).
- Fail loudly on input that cannot be trusted; warn only where a warning carries information.
- Keep redirected output clean, so `> deck-data.json` yields valid JSON and nothing else.

**Non-Goals:**
- Validating deck legality as a rules engine. The `--deck` checks are advisory.
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

### Deck list from a file or from standard input

The `file` argument is optional. When it is omitted, or given as `-`, the command reads standard input.

```bash
php artisan cards:resolve deck.txt              # from a file
pbpaste | php artisan cards:resolve             # straight from the clipboard
php artisan cards:resolve                       # paste, then Ctrl-D
php artisan cards:resolve - --format=markdown   # explicit stdin, for readability in scripts
```

- **Why:** requiring a file for a list that is being copied out of a deck builder adds a pointless step. Standard input is the ordinary shell idiom for this and makes the clipboard case a one-liner.
- **When standard input is an interactive terminal** — no file given and nothing piped in — the command prints a short hint to stderr ("Paste the deck list, then press Ctrl-D") so it does not look hung. The hint goes to stderr, never stdout.
- The file path, when given, must exist; a missing file is an error rather than a silent fall back to stdin.

### Errors always abort; deck rules are opt-in

Three classes, because they mean three different things:

| Condition | Behaviour |
|---|---|
| Malformed line | error, abort — always |
| Quantity below 1 | error, abort — always |
| Duplicate card ID | error, abort — always |
| Card ID not in database | error, abort — always (all unknown IDs listed at once) |
| Trailing name disagrees with database | warning — always |
| Leader count other than one | warning — **only with `--deck`** |
| Main deck not exactly 50 cards | warning — **only with `--deck`** |
| More than 4 copies of a card | warning — **only with `--deck`** |

- **Why errors abort:** they mean the input cannot be resolved correctly. Carrying on would produce output that silently misrepresents the list. This holds for five cards as much as for fifty.
- **Why deck rules are opt-in rather than on by default:** the common case is resolving a handful of cards, not a full deck. If "no leader" and "main deck is not 50 cards" fired on every such run, they would be noise within a week — and a warning that always appears is a warning nobody reads, which costs exactly the times it matters. `--deck` states the intent, and only then are deck rules a meaningful thing to check.
- **Why the name mismatch warns unconditionally:** it says something about *this line*, not about the shape of the collection, so it is as relevant for five cards as for a deck. It never aborts, because the card number is the identity and the name is a comment.
- **All unknown IDs are collected and reported together** rather than failing at the first one, so a list with three typos takes one run to fix, not three.
- **`--deck` never changes the exit code.** A deck that breaks composition rules still resolves and still exits 0; the warnings are advisory. A partial or experimental list is a legitimate thing to resolve, and that is exactly when the command is most wanted.

### Warnings to stderr, payload to stdout

`$this->output->getErrorOutput()->writeln(...)` for warnings; the JSON or Markdown document to stdout.

- **Why:** the intended use is `php artisan cards:resolve deck.txt > deck-data.json`. If warnings went to stdout they would corrupt the file. This way the file is always clean and the warnings still reach the terminal.

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
  "cards": [ { "quantity": 4, "id": "OP17-113", "...": "..." } ],
  "totals": { "leader": 1, "cards": 50, "distinct": 14 },
  "warnings": []
}
```

`leader` is `null` when the list contains none — the ordinary case for a short lookup. `cards` holds everything that is not a leader and keeps the input order, so a diff between two resolved files stays readable. Warnings appear both on stderr and in the JSON, so a downstream consumer sees them too.

- **Why `cards` rather than `main_deck`:** the shape must read sensibly for five looked-up cards, not only for a deck. The same key carries the main deck when `--deck` is used; nothing about the structure changes between the two modes.

### Markdown shape

A leader block when there is one, a table of the remaining cards with the short fields, then the effect and trigger text per card in a separate section.

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
