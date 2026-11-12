# Contributing

Contributions are welcome — bug reports, improvements, and new features.

## Development Workflow

### Required Tools

- PHP 8.3+, Composer, Node.js
- [Claude Code](https://claude.ai/code) (recommended — hooks and skills are pre-configured in `.claude/`)

### Steps

```bash
# 1. Branch from main
git checkout -b feat/<topic>   # or fix/<topic>, docs/<topic>, chore/<topic>

# 2. Write the tests first, then the code
php artisan test --compact

# 3. Format
vendor/bin/pint --dirty

# 4. Commit (Conventional Commits) and open a pull request
git push -u origin feat/<topic>
gh pr create --title "feat: <description>"
```

- Commits follow [Conventional Commits](https://www.conventionalcommits.org/): `<type>[optional scope]: <description>`.
- Write `feat` and `fix` messages as user-facing descriptions — they appear in the changelog.
- No direct push to `main`. The pull request needs a green CI and is merged with a merge commit.

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
