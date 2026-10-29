# One Piece Cards API – Project Knowledge

Project-specific notes. The `<laravel-boost-guidelines>` block below is generated: never edit it by hand. `php artisan boost:update` (also run by `composer update`) rewrites only that block and keeps this section. To change a rule inside the block, edit `.ai/guidelines/*.md` and run `php artisan boost:update`.

## Purpose

- Laravel REST API (`/api/v1`) and MCP server (`/mcp`) for One Piece TCG card data, live at `https://cards.optcg.ch`. API docs at `/docs/api`, landing page at `/`.
- Card data comes from the official Bandai card list through [vegapull](https://github.com/Coko7/vegapull) and is stored in SQLite.
- Consumers are the maintainer's own apps and MCP clients; each gets its own Sanctum token.
- `docs/plan.md`: the (completed) move from `op-cards.ditshej.ch` to `cards.optcg.ch`. `docs/deployment.md`: server setup, deploy, checks, troubleshooting.

## Stack

- PHP: code must run on **PHP 8.3** (production shell `/opt/php83/bin/php`, Plesk domain PHP 8.3, CI 8.3, `composer.json` `^8.3`). Local dev may use a newer PHP; do not use 8.4+ only syntax or functions.
- Laravel 13, Sanctum 4 (API tokens), `laravel/mcp` (MCP server), `dedoc/scramble` (OpenAPI docs at `/docs/api`).
- SQLite only (`database/database.sqlite`). Holds card data, tokens, sessions, cache and queue.
- Frontend: Blade landing page and Scramble docs only; Vite 8 + Tailwind CSS 4. No JS framework.
- Tests: Pest 4. Formatter: Pint. Changelog: git-cliff (`cliff.toml`) from Conventional Commits.

## Commands

- Setup: `composer run setup` (install, `.env`, key, migrate, npm build, sets `core.hooksPath .githooks`).
- Card data locally: `php artisan cards:fetch` (needs `vega` on `$PATH`, or `VEGAPULL_BINARY`), or `php artisan cards:import <path>`.
- Local token: `php artisan token:create "App" "app@example.com"`; also `token:list`, `token:revoke`.
- Run locally: `composer run dev` (serve, queue, pail, vite).
- Tests: `php artisan test --compact` (filter with `--filter=`). No browser tests exist.
- Format: `vendor/bin/pint --dirty`. CI runs `vendor/bin/pint --test`.
- Build: `npm run build` (also done by `./deploy.sh`; `public/build` is gitignored).

## Structure and conventions

- Routes: REST in `routes/api.php` (`auth:sanctum`, prefix `v1`, names `v1.*`), MCP in `routes/ai.php`, landing page in `routes/web.php`, scheduler in `routes/console.php`.
- API: `PacksController` and `CardsController` (index/show only), `CardsIndexRequest` for filters, `CardResource` / `PackResource` for output.
- MCP: `app/Mcp/Servers/CardsServer.php` with tools `list-packs`, `get-pack`, `list-cards`, `get-card`. Tools mirror the REST endpoints; keep REST and MCP in sync when filters or fields change.
- Card filters live in `Card::scopeApplyFilters()` and `Card::ARRAY_FILTER_PARAMS`. `CardsController` and `ListCardsTool` both use them. Multi-value, `_not`, `_min`/`_max` and `has_*` variants exist; add new filters there.
- `Card` primary key is the string card id (e.g. `OP01-001`), not auto-increment. Alt arts are separate rows (id suffix `_p<n>`, e.g. `OP01-001_p1`) with `alt_art_variant` set.
- Import config: `config/import.php` (vegapull paths, `IMPORT_SCHEDULE_ENABLED`, `SYNC_*`). Raw JSON lives in `storage/vegapull` (gitignored). `cards:import` upserts packs and cards.
- Artisan commands in `app/Console/Commands`; every command needs `tests/Feature/Commands/<CommandName>Test.php` (enforced by `tests/Feature/ArchTest.php`).
- Arch test forbids `dd`, `dump` and `env()` in `app/`. Read settings through `config()`.
- Feature tests use `LazilyRefreshDatabase` with in-memory SQLite. vegapull and SSH are faked with `Process::fake()`; fixtures in `tests/Fixtures/vegapull/json`.
- Workflow: every feature starts with an OpenSpec change (`openspec/`, `/opsx:*` commands); branch `feat/<change-name>`, change name as commit scope. Docs-only or test-value-only changes need no OpenSpec change; use a `docs/<topic>` branch.
- Archived OpenSpec changes (`openspec/changes/archive/`) are history; never rewrite them.
- `feat` and `fix` commit messages are user-facing: they go into `CHANGELOG.md`.

## Deploy and hosting

- Metanet Plesk server, site `cards.optcg.ch`, checkout `/cards.optcg.ch` (chroot), document root `cards.optcg.ch/public`. Details in `docs/deployment.md`.
- Deploy is manual and only by the maintainer from their Mac: `./deploy.sh` (build, rsync `public/build`, SSH runs `_deploy.sh`: branch and clean-tree guard, `git pull --ff-only`, `composer install --no-dev`, `migrate --force`, `optimize:clear`). There is no CI deploy.
- Card data reaches production only through `php artisan cards:sync` from the Mac (uploads JSON, runs `cards:import` there). It reads `SYNC_*` from the local `.env`, not from `.env.deploy`.
- API tokens on production: `./create-token.sh` (SSH, reads `.env.deploy`). The plaintext token is shown once.
- No scheduler on production: `IMPORT_SCHEDULE_ENABLED=false` and no Plesk cron for `schedule:run`. Do not add one without the maintainer's decision.
- Never automatic or by an agent: deploys, `cards:sync`, token commands against production, any SSH/Plesk/DNS change, edits to files on the server (the next deploy aborts on a dirty tree).
- The production SQLite database is never copied between environments, in either direction. It holds the live tokens.
- `op-cards.ditshej.ch` is shut down. Do not reintroduce it in docs or code (the wildcard `*.ditshej.ch` still resolves, but serves no app).

## Data protection and test data

- Public repository. Never commit `.env`, `.env.deploy`, SSH hosts, users, ports, server credentials, tokens, `database/*.sqlite` or vegapull dumps.
- Docs use placeholders (`<ssh host>`, `<test token>`); `.env.example` and `.env.deploy.example` keep placeholders only.
- Test data is invented or comes from `tests/Fixtures`. No real tokens or e-mail addresses in tests (use `example.com`).
- Card data is public Bandai data; user data is limited to token owner names and e-mails, which stay on the server.

## Pitfalls

- Plain `php` in the server shell is an older version; always use `/opt/php83/bin/php` (`DEPLOY_PHP`, `SYNC_PHP`).
- `SyncCardsCommand` and `create-token.sh` run on the Mac and reach the server over SSH; they do not run on the server.
- `cards:fetch` takes minutes and needs network access to Bandai; tests must never call the real `vega` binary.
- The weekly `cards:import` job still exists in `routes/console.php` and `schedule:list` shows it, but it is off by config. Leave the code as is.
- Event cards: a missing cost is normalised to `0` at import; `cost=0` is a valid filter value.
- `public/.well-known/` on the server is needed for Let's Encrypt renewal and is excluded from git there.
- `docs/dev-setup.md` is a generic setup checklist for new Laravel projects, not this project's setup. It is tracked but listed in `.gitignore`, so local edits to it are not picked up by `git add`.
- Never commit files under `.github/workflows/`; propose workflow changes in the PR description instead.

<laravel-boost-guidelines>
=== .ai/coding-style rules ===

## Coding Style

### Collections over Loops

- Prefer `Collection` methods and array functions (`array_map`, `array_filter`, `array_reduce`) over `foreach` and other loops
- Laravel's Illuminate Collection is the go-to for transforming, filtering, and aggregating data
- Use `collect()` to wrap arrays when chaining multiple operations
- Only fall back to loops when performance-critical or when a loop is genuinely clearer

### Spatie PHP/Laravel Conventions

- Happy path last, avoid `else`, use early returns
- Only `up()` in migrations, never `down()`
- Plural controller names (`PostsController`), CRUD methods only
- Array notation for validation rules
- Use `config()` helper, never `env()` outside config files
- Typed properties over docblocks, constructor property promotion
- kebab-case URLs, camelCase route names
- Self-documenting code over comments

=== .ai/commits rules ===

## Conventional Commits

Format: `<type>[optional scope]: <description>`

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`

- Scope optional in parentheses: `feat(auth): add login endpoint`
- Breaking changes: `!` before the colon: `feat!: remove legacy API`
- Description: imperative mood, lowercase, no trailing period
- **OpenSpec changes:** use the change name as scope for every commit on that branch:
  `docs(list-packs): add proposal, design and tasks`
  `feat(list-packs): add packs() and pack() endpoints`
  `refactor(list-packs): apply review feedback`
  `docs(list-packs): archive change`

=== .ai/openspec-flow rules ===

## Git + OpenSpec Feature Branch Flow

**Rule:** Every new feature or significant change ALWAYS starts with `/opsx:propose` — never implement directly, not even in Plan Mode. Only after the propose commit may `/opsx:apply` begin.

Every OpenSpec change gets its own feature branch and lands on `main` as a single merge commit (`--no-ff`). No squash, no rebase-merge. No direct push to `main` — always via PR with CI passing.

### Branch Naming Convention

```
feat/<change-name>      # e.g. feat/card-management

```

> **Autonomous mode (AGENT_MISSION):** When working through a full roadmap autonomously, per-change CHECKPOINTs are skipped. Instead, after the session is complete, the agent must produce a mandatory stop — presenting a full summary of all changes and optionally opening a GitHub PR for review.

### Workflow per Change

```bash

# 0. Explore (optional)

# /opsx:explore — investigate ideas and requirements before proposing

# → CHECKPOINT: Present findings to user → wait for OK before proposing

# 1. Create branch

git checkout -b feat/<change-name>

# 2. Propose

openspec new change "<change-name>"

# /opsx:propose — create proposal.md, specs/, design.md, tasks.md

# → Commit: "docs(<change-name>): add proposal, design and tasks"

# → CHECKPOINT: Present proposal summary → wait for OK before implementing

# 3. Implementation (TDD)

# /opsx:apply — work through tasks

# → Commit(s): "feat(<change-name>): ...", "test(<change-name>): ...", etc.

# 4. Verify

# /opsx:verify — checks Completeness, Correctness, Coherence against specs

# → Fix all CRITICALs before proceeding

# 5. AI Review

# laravel-simplifier Agent — automated review (spawn parallel subagents)

# → Fix critical findings, commit: "refactor(<change-name>): apply review feedback"

# → CHECKPOINT: Present change summary:

#     - What changed (architecture, new/modified files)

#     - Test results (N passed)

#     - How to review manually (git diff, which pages/endpoints to test)

#   → Wait for user OK before archiving

# 6. Archiving

# /opsx:archive — close change, merge specs

# → Commit: "docs(<change-name>): archive change"

# 7. Clean up fixup commits and push

git fetch origin && git rebase -i --autosquash origin/main   # collapses `fixup!` commits; no-op otherwise

git push -u origin feat/<change-name>
gh pr create --title "feat(<change-name>): <description>"

# → CI must pass (tests + lint), then merge via GitHub ("Create a merge commit")

# 8. Merge and cleanup

gh pr merge --merge --delete-branch
git checkout main && git pull && git remote prune origin
```

### Resulting History on main

```
*   Merge pull request #43 from feat/list-packs
|\
| * docs(list-packs): archive change
| * refactor(list-packs): apply review feedback
| * feat(list-packs): add packs() and pack() endpoints
| * docs(list-packs): add proposal, design and tasks
|/
*   Merge pull request #42 from feat/prev-change
```

Use `git log --first-parent main` to see only the merge commits (one per change).

Each feature follows: Planning → Implementation → Verify → Review → Archiving.
Use the change name as commit scope for every commit on that branch.
Multiple commits per phase are fine — commit as often as makes sense (feat, fix, test, refactor, etc.).

=== .ai/tdd rules ===

## TDD

- Write tests FIRST, then implement
- No feature without tests
- Pre-commit hook blocks commits when tests fail

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/mcp (MCP) - v0
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/boost (BOOST) - v2
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `mcp-development` — Use this skill for Laravel MCP development only. Trigger when creating or editing MCP tools, resources, prompts, or servers in Laravel projects. Covers: artisan make:mcp-* generators, mcp:inspector, routes/ai.php, Tool/Resource/Prompt classes, schema validation, shouldRegister(), OAuth setup, URI templates, read-only attributes, and MCP debugging. Do not use for non-Laravel MCP projects or generic AI features without MCP.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
