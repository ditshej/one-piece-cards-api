## Why

Deck lists in this game are exchanged as bare card numbers — `4 OP17-113 Streusen` or `4xST01-011`. That is enough for a deck builder, but useless to any tool or person reasoning about the deck, because none of the actual card data is in the list: no cost, no power, no counter, no types, no effect text.

Resolving such a list by hand means looking up fifteen to twenty distinct cards one at a time. The data is already here — 4843 cards in the local database, with every field needed — but there is no way to join a deck list against it.

The concrete trigger: an external assistant needs to analyse a 50-card deck and cannot look the cards up itself. Handing it a resolved deck file solves that completely, without granting anything access to the API. The same output is equally useful for a human reading their own list, for diffing two versions of a deck, and for any future deck tooling.

## What Changes

- Add a `deck:resolve` Artisan command that reads a deck list file and resolves every card against the local `cards` table.
- Accept two line formats, both common in deck-builder exports:
  - `<quantity>x<card-id>` — e.g. `4xST01-011`
  - `<quantity> <card-id> [card name]` — e.g. `4 OP17-113 Streusen`; the trailing name is optional and informational
- Emit either JSON (`--format=json`, the default and the canonical machine-readable form) or Markdown (`--format=markdown`, for reading and for pasting into a chat project).
- Per card, output: quantity, id, name, category, colors, cost, power, counter, types, effect, trigger, rarity, card_set.
- Separate the leader from the main deck by card category rather than by line position.
- Reject structurally broken input with a clear, actionable error: malformed lines, quantities below 1, duplicate card IDs, and card IDs absent from the database.
- Report deck-composition problems as warnings rather than errors: no leader or more than one, a main deck other than 50 cards, more than four copies of a card, and a trailing name that disagrees with the database.
- Write warnings to stderr so that redirecting stdout to a file yields clean output.

## Capabilities

### New Capabilities
- `deck-resolve`: resolve a deck list of card numbers into full card data, as JSON or Markdown

### Modified Capabilities
<!-- none -->

## Impact

- New file: `app/Console/Commands/ResolveDeckCommand.php`
- New file: `tests/Feature/Commands/ResolveDeckCommandTest.php`
- Reads `App\Models\Card`; no writes, no schema change, no migration
- No new dependencies, no route changes, no API changes
- Non-goals:
  - No external API of any kind. The data comes from the local database only.
  - No deck legality engine. Composition rules are surfaced as warnings; this command resolves data, it does not referee a game.
  - No deck-building, editing, pricing, or collection tracking.
  - No further input formats until one is actually needed.
