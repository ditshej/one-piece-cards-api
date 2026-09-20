## ADDED Requirements

### Requirement: Deck list resolution command
The system SHALL provide a `cards:resolve {file?} {--format=json} {--deck}` Artisan command that reads a deck list and resolves every card number against the local `cards` table. The command SHALL NOT perform any network request; all card data SHALL come from the local database.

#### Scenario: Resolving a deck list to JSON
- **WHEN** a user runs `php artisan cards:resolve deck.txt` with a file containing valid entries
- **THEN** the command writes a JSON document to stdout containing the leader, the remaining cards in input order, and totals, and exits with status 0

#### Scenario: Resolving a deck list to Markdown
- **WHEN** a user runs `php artisan cards:resolve deck.txt --format=markdown`
- **THEN** the command writes a Markdown document to stdout containing a leader section, a card table, and the effect and trigger text per card, and exits with status 0

#### Scenario: Unsupported format requested
- **WHEN** a user passes a `--format` value other than `json` or `markdown`
- **THEN** the command aborts with a non-zero status and names the supported values

#### Scenario: Deck file does not exist
- **WHEN** the given file path does not exist
- **THEN** the command aborts with a non-zero status and an error naming the path

### Requirement: Deck list accepted from a file or standard input
The command SHALL read the deck list from the `file` argument when given, and from standard input when the argument is omitted or given as `-`. When the argument is omitted and standard input is an interactive terminal, the command SHALL print a hint to standard error.

#### Scenario: Deck list piped in
- **WHEN** a deck list is piped in, as in `pbpaste | php artisan cards:resolve`
- **THEN** the command resolves it exactly as it would from a file

#### Scenario: Explicit standard input
- **WHEN** the file argument is given as `-`
- **THEN** the command reads the deck list from standard input

#### Scenario: Interactive paste
- **WHEN** the command is run without a file argument and standard input is an interactive terminal
- **THEN** a hint to paste the list and press Ctrl-D is written to standard error, and the pasted list is resolved once input ends

### Requirement: Accepted deck list line formats
The command SHALL accept two line formats: `<quantity>x<card-id>` and `<quantity> <card-id> [card name]`. Blank lines and surrounding whitespace SHALL be ignored. The trailing card name SHALL be optional and informational only.

#### Scenario: Compact format
- **WHEN** a line reads `4xST01-011`
- **THEN** the entry resolves to card `ST01-011` with quantity 4

#### Scenario: Deck-builder export format
- **WHEN** a line reads `4 OP17-113 Streusen`
- **THEN** the entry resolves to card `OP17-113` with quantity 4

#### Scenario: Alt art variant identifier
- **WHEN** a line references an alt art identifier such as `ST10-008_p3`
- **THEN** the entry resolves to that card

#### Scenario: Malformed line
- **WHEN** a line matches neither format and is not blank
- **THEN** the command aborts with a non-zero status and an error naming the line number and its content

### Requirement: Structural validation aborts resolution
The command SHALL reject input it cannot resolve correctly: quantities below 1, duplicate card IDs, and card IDs absent from the database. All unknown card IDs SHALL be reported together in a single run.

#### Scenario: Quantity below one
- **WHEN** a line specifies a quantity of `0` or a negative quantity
- **THEN** the command aborts with a non-zero status and an error naming the offending line

#### Scenario: Duplicate card ID
- **WHEN** the same card ID appears on more than one line
- **THEN** the command aborts with a non-zero status and an error naming the duplicated ID

#### Scenario: Unknown card IDs are reported together
- **WHEN** a deck list references three card IDs that do not exist in the database
- **THEN** the command aborts with a non-zero status and an error listing all three IDs

### Requirement: Deck composition checks are opt-in
The command SHALL check deck composition only when the `--deck` flag is given: exactly one leader, a 50-card main deck, and at most four copies of a card. Violations SHALL be reported as warnings without aborting, and SHALL NOT change the exit status. Without the flag, the command SHALL NOT emit composition warnings.

#### Scenario: Short card list resolves without composition warnings
- **WHEN** a list of five cards with no leader is resolved without `--deck`
- **THEN** no composition warnings are emitted and the command exits with status 0

#### Scenario: Deck list without a leader
- **WHEN** a list is resolved with `--deck` and contains no card whose category is `Leader`
- **THEN** the command emits a warning, sets `leader` to null in the output, and exits with status 0

#### Scenario: Main deck size other than 50
- **WHEN** a list is resolved with `--deck` and the non-leader cards total a number other than 50
- **THEN** the command emits a warning naming the actual total and exits with status 0

#### Scenario: More than four copies of a card
- **WHEN** a list is resolved with `--deck` and an entry specifies a quantity greater than 4
- **THEN** the command emits a warning naming that card and exits with status 0

#### Scenario: A legal deck produces no warnings
- **WHEN** a list of one leader and 50 main deck cards, none exceeding four copies, is resolved with `--deck`
- **THEN** no warnings are emitted and the command exits with status 0

### Requirement: Trailing name mismatch always warns
The command SHALL compare a supplied trailing card name against the name stored for that card ID and SHALL emit a warning on mismatch, regardless of the `--deck` flag. The entry SHALL be resolved from the card ID. A mismatch SHALL NOT abort resolution.

#### Scenario: Trailing name disagrees with the database
- **WHEN** a line supplies a trailing card name that differs from the name stored for that card ID
- **THEN** the command emits a warning naming both the supplied and the stored name, resolves the entry from the card ID, and exits with status 0

### Requirement: Leader separated by card category
The command SHALL identify the leader by the card's `category` field rather than by its position in the file.

#### Scenario: Leader not on the first line
- **WHEN** the leader entry appears somewhere other than the first line
- **THEN** it is still reported as the leader and excluded from the card totals

### Requirement: Resolved card fields
For every resolved entry the command SHALL output quantity, id, name, category, colors, cost, power, counter, types, effect, trigger, rarity, and card_set.

#### Scenario: All fields present for a resolved card
- **WHEN** a deck list entry resolves to a card in the database
- **THEN** the output for that entry carries all thirteen fields, with null preserved for fields the card does not have

### Requirement: Warnings separated from payload
The command SHALL write warnings to standard error and the JSON or Markdown document to standard output, so that redirecting standard output to a file yields the document alone.

#### Scenario: Redirected output stays clean
- **WHEN** a user runs `php artisan cards:resolve deck.txt > deck-data.json` on a list that produces warnings
- **THEN** `deck-data.json` contains valid JSON only, and the warnings appear on the terminal
