## 1. Parsing and validation (TDD)

- [ ] 1.1 Write failing tests for the two accepted line formats (`4xST01-011`, `4 OP17-113 Streusen`), blank lines, surrounding whitespace, and alt art IDs (`ST10-008_p3`)
- [ ] 1.2 Write failing tests for the aborting errors: malformed line (names line number and content), quantity below 1, duplicate card ID, unknown card IDs reported together in one run
- [ ] 1.3 Write failing tests for the advisory warnings: no leader, two leaders, main deck not 50, quantity above 4, trailing name disagreeing with the database
- [ ] 1.4 Create `ResolveDeckCommand` with `#[Signature('deck:resolve {file} {--format=json}')]` and implement parsing and validation until 1.1–1.3 pass
- [ ] 1.5 Verify a missing file and an unsupported `--format` value both abort with a descriptive message and non-zero status
- [ ] 1.6 Write failing tests for input sources: file argument, piped stdin, explicit `-`, and the interactive hint on stderr; then make the `file` argument optional and read stdin when omitted or `-`

## 2. Resolution against the database

- [ ] 2.1 Write a failing test asserting a single database query for a multi-card list
- [ ] 2.2 Resolve via `Card::whereIn('id', $ids)->get()->keyBy('id')` and assemble the deck in memory
- [ ] 2.3 Separate the leader by `category === 'Leader'`, keeping main deck entries in input order; cover the leader-not-first case
- [ ] 2.4 Assert all thirteen fields are present per entry, with null preserved

## 3. Output formats

- [ ] 3.1 Write failing tests for the JSON shape: `leader` (null when absent), `main_deck`, `totals`, `warnings`
- [ ] 3.2 Implement JSON output, written raw so the document is byte-exact
- [ ] 3.3 Write failing tests for the Markdown shape: leader block, main deck table, effect and trigger section
- [ ] 3.4 Implement Markdown output
- [ ] 3.5 Write a failing test asserting warnings go to stderr and never into stdout, then route them through `$this->output->getErrorOutput()`

## 4. Verification

- [ ] 4.1 Run `php artisan test --compact --filter=ResolveDeckCommand`, then the full suite
- [ ] 4.2 Run `vendor/bin/pint --dirty --format agent`
- [ ] 4.3 Resolve the real 51-line Big Mom list (1 leader + 50 main deck) in both formats and confirm no warnings
- [ ] 4.4 Confirm `php artisan deck:resolve deck.txt > deck-data.json` produces valid JSON, verified with `jq empty`
- [ ] 4.5 Resolve a deliberately broken list (typo'd ID, duplicate, quantity 0) and confirm each error message is actionable

## 5. Documentation

- [ ] 5.1 Document the command in `README.md` with both input formats and both output formats
- [ ] 5.2 Add a `CHANGELOG.md` entry
