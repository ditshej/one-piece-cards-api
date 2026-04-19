# Contributing

Contributions are welcome — bug reports, improvements, and new features.

## Development Workflow

This project uses [OpenSpec](https://github.com/fission-ai/openspec) for structured change management. Every new feature or significant change **must start with a proposal** — never implement directly.

### Required Tools

- PHP 8.5, Composer, Node.js
- [OpenSpec CLI](https://github.com/fission-ai/openspec): `npm install -g @fission-ai/openspec`
- [Claude Code](https://claude.ai/code) (recommended — skills are pre-configured in `.claude/`)

### Feature Branch Convention

Every change gets its own branch:

```bash
git checkout -b feat/<change-name>   # e.g. feat/card-filtering
```

Merge commits (`--no-ff`) preserve each change as one node on `main`. No squash, no rebase-merge. No direct push to `main` — always via PR with CI passing. Clean up the feature branch with `--fixup` / `--autosquash` before pushing.

### Workflow per Change

```bash
# 0. Explore (optional)
# /opsx:explore — investigate before proposing
# → Present findings to user, wait for OK

# 1. Create a feature branch
git checkout -b feat/<change-name>

# 2. Propose (generates proposal, specs, design, tasks)
/opsx:propose
git add openspec/ && git commit -m "docs(<change-name>): add proposal, design and tasks"
# → Present proposal summary to user, wait for OK before implementing

# 3. Implement (TDD — tests first)
/opsx:apply

# 4. Verify — checks Completeness, Correctness, Coherence against specs
/opsx:verify
# → Fix all CRITICALs before proceeding

# 5. AI Review
# Spawn laravel-simplifier (or php-library-reviewer) agents
# → Fix critical findings, commit
# → Present change summary + manual review instructions → wait for user OK

# 6. Human review — don't proceed until approved

# 7. Archive the change
/opsx:archive

# 8. Clean up fixup commits and push
git fetch origin && git rebase -i --autosquash origin/main   # no-op if no `fixup!` commits
git push -u origin feat/<change-name>
gh pr create --title "feat(<change-name>): <description>"
# → CI must pass (tests + lint), then merge via GitHub ("Create a merge commit")

# 9. Merge and cleanup
gh pr merge --merge --delete-branch
git checkout main && git pull && git remote prune origin
```

Use the change name as the commit scope on every commit on that branch:

```
docs(card-filtering): add proposal, design and tasks
feat(card-filtering): add color filter to cards endpoint
fix(card-filtering): correct empty result handling
docs(card-filtering): archive change
```

Write `feat` and `fix` messages as user-facing descriptions — they appear in the changelog.

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
