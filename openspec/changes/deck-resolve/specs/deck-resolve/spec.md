## ADDED Requirements

### Requirement: Deck list resolution command
The system SHALL provide a `deck:resolve {file} {--format=json}` Artisan command that reads a deck list file and resolves every card number against the local `cards` table. The command SHALL NOT perform any network request; all card data SHALL come from the local database.

#### Scenario: Resolving a deck list to JSON
- **WHEN** a user runs `php artisan deck:resolve deck.txt` with a file containing valid entries
- **THEN** the command writes a JSON document to stdout containing the leader, the main deck in input order, and totals, and exits with status 0

#### Scenario: Resolving a deck list to Markdown
- **WHEN** a user runs `php artisan deck:resolve deck.txt --format=markdown`
- **THEN** the command writes a Markdown document to stdout containing a leader section, a main deck table, and the effect and trigger text per card, and exits with status 0

#### Scenario: Unsupported format requested
- **WHEN** a user passes a `--format` value other than `json` or `markdown`
- **THEN** the command aborts with a non-zero status and names the supported values

#### Scenario: Deck file does not exist
- **WHEN** the given file path does not exist
- **THEN** the command aborts with a non-zero status and an error naming the path

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

### Requirement: Composition problems are reported as warnings
The command SHALL report deck-composition problems as warnings without aborting: a leader count other than one, a main deck other than 50 cards, more than four copies of a card, and a trailing card name that disagrees with the database. Output SHALL still be produced.

#### Scenario: Deck list without a leader
- **WHEN** a deck list contains no card whose category is `Leader`
- **THEN** the command emits a warning, sets `leader` to null in the output, and exits with status 0

#### Scenario: Main deck size other than 50
- **WHEN** the resolved main deck totals a number of cards other than 50
- **THEN** the command emits a warning naming the actual total and exits with status 0

#### Scenario: More than four copies of a card
- **WHEN** an entry specifies a quantity greater than 4
- **THEN** the command emits a warning naming that card and exits with status 0

#### Scenario: Trailing name disagrees with the database
- **WHEN** a line supplies a trailing card name that differs from the name stored for that card ID
- **THEN** the command emits a warning naming both the supplied and the stored name, and resolves the entry from the card ID

### Requirement: Leader separated by card category
The command SHALL identify the leader by the card's `category` field rather than by its position in the file.

#### Scenario: Leader not on the first line
- **WHEN** the leader entry appears somewhere other than the first line
- **THEN** it is still reported as the leader and excluded from the main deck totals

### Requirement: Resolved card fields
For every resolved entry the command SHALL output quantity, id, name, category, colors, cost, power, counter, types, effect, trigger, rarity, and card_set.

#### Scenario: All fields present for a resolved card
- **WHEN** a deck list entry resolves to a card in the database
- **THEN** the output for that entry carries all thirteen fields, with null preserved for fields the card does not have

### Requirement: Warnings separated from payload
The command SHALL write warnings to standard error and the JSON or Markdown document to standard output, so that redirecting standard output to a file yields the document alone.

#### Scenario: Redirected output stays clean
- **WHEN** a user runs `php artisan deck:resolve deck.txt > deck-data.json` on a list that produces warnings
- **THEN** `deck-data.json` contains valid JSON only, and the warnings appear on the terminal
