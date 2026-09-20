## Why

Card lists in this game are exchanged as bare card numbers — `4 OP17-113 Streusen` or `4xST01-011`. That is enough for a deck builder, but useless to any tool or person reasoning about the cards, because none of the actual data is in the list: no cost, no power, no counter, no types, no effect text.

Resolving such a list by hand means looking cards up one at a time. The data is already here — 4843 cards in the local database, with every field needed — but there is no way to join a list of card numbers against it.

The concrete trigger: an external assistant needs to analyse a 50-card deck and cannot look the cards up itself. Handing it a resolved file solves that completely, without granting anything access to the API. The same command is just as useful for the smaller case that comes up far more often: pasting a handful of card numbers to see what they actually do.

## What Changes

- Add a `cards:resolve` Artisan command that resolves a list of card numbers against the local `cards` table. It joins the existing `cards:fetch` / `cards:import` / `cards:sync` family.
- Take the list from a file argument, or from standard input when the argument is omitted or given as `-`, so a list can be pasted or piped without saving it first.
- Accept two line formats, both common in deck-builder exports:
  - `<quantity>x<card-id>` — e.g. `4xST01-011`
  - `<quantity> <card-id> [card name]` — e.g. `4 OP17-113 Streusen`; the trailing name is optional and informational
- Emit either JSON (`--format=json`, the default and the canonical machine-readable form) or Markdown (`--format=markdown`, for reading and for pasting into a chat project).
- Per card, output: quantity, id, name, category, colors, cost, power, counter, types, effect, trigger, rarity, card_set.
- Reject structurally broken input with a clear, actionable error: malformed lines, quantities below 1, duplicate card IDs, and card IDs absent from the database.
- Add an opt-in `--deck` flag that additionally checks deck composition — exactly one leader, a 50-card main deck, at most four copies of a card — and reports violations as warnings without aborting. Without the flag the command resolves whatever it is given and stays quiet.
- Separate the leader from the rest by card category rather than by line position.
- Write warnings to stderr so that redirecting stdout to a file yields clean output.

## Capabilities

### New Capabilities
- `card-resolution`: resolve a list of card numbers into full card data, as JSON or Markdown, with optional deck-composition checks

### Modified Capabilities
<!-- none -->

## Impact

- New file: `app/Console/Commands/ResolveCardsCommand.php`
- New file: `tests/Feature/Commands/ResolveCardsCommandTest.php`
- Reads `App\Models\Card`; no writes, no schema change, no migration
- No new dependencies, no route changes, no API changes
- Non-goals:
  - No external API of any kind. The data comes from the local database only.
  - No deck legality engine. `--deck` surfaces the three composition rules that matter in practice as warnings; this command resolves data, it does not referee a game.
  - No deck-building, editing, pricing, or collection tracking.
  - No further input formats until one is actually needed.
