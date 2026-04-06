# Contributing

Contributions are welcome — bug reports, improvements, and new features.

## Development Workflow

This project uses [OpenSpec](https://github.com/fission-ai/openspec) for structured change management. Every new feature or significant change **must start with a proposal** — never implement directly.

### Required Tools

- PHP 8.4, Composer, Node.js
- [vegapull](https://github.com/Coko7/vegapull) (`vega` on `$PATH`)
- [OpenSpec CLI](https://github.com/fission-ai/openspec): `npm install -g @fission-ai/openspec`
- [Claude Code](https://claude.ai/code) (recommended — skills are pre-configured in `.claude/`)

### Feature Branch Convention

Every change gets its own branch:

```bash
git checkout -b feat/<change-name>   # e.g. feat/card-filtering
```

No squash merges — full history stays on `main`.

### Workflow per Change

```bash
# 1. Create a feature branch
git checkout -b feat/<change-name>

# 2. Propose the change (generates proposal, specs, design, tasks)
/opsx:propose

# 3. Commit the artifacts before implementing
git add openspec/ && git commit -m "docs(<change-name>): add proposal, design and tasks"

# 4. Implement (TDD — tests first)
/opsx:apply

# 5. Archive the change
/opsx:archive

# 6. Merge to main
git checkout main && git merge feat/<change-name>
```

Use the change name as the commit scope on every commit on that branch:

```
feat(card-filtering): add color filter to cards endpoint
test(card-filtering): add filter assertions
docs(card-filtering): archive change
```

## TDD

Tests are written **before** implementation. A pre-commit hook enforces this — commits are blocked when tests fail.

```bash
# Run tests
php artisan test --compact

# Run a specific test file or filter
php artisan test --compact --filter=CardsEndpointTest
```

## Code Style

[Laravel Pint](https://laravel.com/docs/pint) is used for formatting. Run it before committing:

```bash
vendor/bin/pint --dirty
```

Coding standards follow the [Spatie PHP/Laravel Guidelines](docs/spatie-guidelines.md).

## Reporting Issues

Open a GitHub issue with a clear description of the problem and steps to reproduce.
