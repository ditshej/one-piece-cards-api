# Dev Setup Checklist

Reusable checklist for setting up a new Laravel project with Claude Code and OpenSpec.
Based on learnings from the "yohohoho" project, inspired by [freekmurze/dotfiles](https://github.com/freekmurze/dotfiles).

---

## Language Convention

All project artifacts are written in **English**:
- Code, variable names, class names
- Comments and PHPDoc blocks
- OpenSpec artifacts (proposal.md, specs, design.md, tasks.md)
- Git commit messages
- Documentation files (README, docs/)

**Conversation language with Claude remains German.**

---

## 1. Project Init

```bash
composer create-project laravel/laravel project-name
cd project-name
composer require --dev laravel/boost laravel/pail laravel/pint pestphp/pest pestphp/pest-plugin-laravel
```

Configure SQLite in `.env`:

```
DB_CONNECTION=sqlite
```

Create the database file:

```bash
touch database/database.sqlite
```

## 2. Set up OpenSpec

```bash
npm install -g @fission-ai/openspec
openspec init
```

This creates:
- `openspec/config.yaml` — project config
- `openspec/specs/` — long-lived project specs (populated by changes)
- `openspec/changes/` — short-lived work packages
- `.claude/skills/openspec-*` — skills for Claude Code Agents

Fill `openspec/config.yaml` with project context:

```yaml
schema: spec-driven

context: |
  ## Project
  <project description>

  ## Tech Stack
  PHP 8.x, Laravel 12, Pest 4, Pint, Blade + Tailwind 4 + Alpine.js, Vite, SQLite

  ## Coding Standards
  Spatie PHP/Laravel guidelines (see docs/spatie-guidelines.md)
  Key conventions:
    - Happy path last, avoid else, use early returns
    - Only up() in migrations, never down()
    - Plural controller names (PostsController), CRUD methods only
    - Array notation for validation rules
    - Use config() helper, never env() outside config files
    - Typed properties over docblocks, constructor property promotion
    - kebab-case URLs, camelCase route names
    - Self-documenting code over comments

  ## Development Approach
  - TDD: always write tests FIRST, then implement
  - Conventional Commits

rules:
  tasks:
    - Break tasks into chunks of max 2 hours
    - Tests must be listed BEFORE implementation tasks
    - Maximum 15 tasks per change — split into smaller changes if more
  proposal:
    - Always include a "Non-goals" section
    - Keep scope small — one concern per change, not an entire feature layer
```

### OpenSpec Workflow

Each change goes through 4 artifacts:

```
/openspec-propose     → proposal.md (WHY)
                      → specs/*.md  (WHAT — requirements with WHEN/THEN scenarios)
                      → design.md   (HOW — architecture decisions)
                      → tasks.md    (TODO — checkboxes, tests before code)
/openspec-apply-change → Implementation (work through tasks)
/openspec-archive-change → Close change, sync specs to main specs
```

> **Rule 1:** Every new feature ALWAYS starts with `/opsx:propose` — never implement directly, not even in plan mode.

> **Rule 2:** Commit immediately after `/opsx:propose` — before implementing:
> `git add openspec/ && git commit -m "docs(<name>): add proposal, design and tasks"`

## 3. Spatie Guidelines

Copy `docs/spatie-guidelines.md` into the new project. This file contains the Spatie PHP/Laravel Coding Standards, optimized for AI Code Assistants.

Source: [freekmurze/dotfiles](https://github.com/freekmurze/dotfiles/blob/main/config/claude/laravel-php-guidelines.md)

## 4. Conventional Commits

Define the convention in `CLAUDE.md` under `## Conventional Commits`:

- Format: `<type>[optional scope]: <description>`
- Types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`
- Scope optional in parentheses: `feat(auth): add login endpoint`
- Breaking changes: `!` before the colon: `feat!: remove legacy API`
- Description: imperative mood, lowercase, no trailing period
- **OpenSpec changes:** use the change name as scope for every commit on that branch:
  `docs(list-packs): add proposal, design and tasks`
  `feat(list-packs): add packs() and pack() endpoints`
  `refactor(list-packs): apply review feedback`
  `docs(list-packs): archive change`

Reference: [conventionalcommits.org/en/v1.0.0](https://www.conventionalcommits.org/en/v1.0.0/)

## 5. TDD (Test-Driven Development)

Define in `CLAUDE.md` under `## Testing (TDD)`:

- Write tests FIRST, then implement the code
- Pest 4 for all tests, prefer Feature Tests
- Aim for comprehensive test coverage — no feature without tests
- `php artisan test --compact` after every change

### Pre-commit Hook

Store the hook in `.githooks/pre-commit` (committed to the repo):

```sh
#!/bin/sh

echo "Running tests before commit..."

php artisan test --compact

if [ $? -ne 0 ]; then
    echo "Tests failed. Commit blocked."
    exit 1
fi
```

Make it executable:

```bash
chmod +x .githooks/pre-commit
```

Activate by adding to the `setup` script in `composer.json`:

```json
"setup": [
    "...",
    "git config core.hooksPath .githooks"
]
```

Run once on existing projects:

```bash
git config core.hooksPath .githooks
```

### Architecture Test

Create `tests/Feature/ArchTest.php` to enforce that every Artisan Command has a corresponding test file:

```php
it('all artisan commands have a corresponding test file', function () {
    $commandFiles = collect(\Illuminate\Support\Facades\File::allFiles(app_path('Console/Commands')))
        ->filter(fn ($file) => $file->getExtension() === 'php')
        ->map(fn ($file) => $file->getPathname())
        ->toArray();

    if (empty($commandFiles)) {
        expect(true)->toBeTrue(); // no commands to check

        return;
    }

    foreach ($commandFiles as $commandFile) {
        $commandName = basename($commandFile, '.php');
        $testFile = base_path("tests/Feature/{$commandName}Test.php");

        expect(file_exists($testFile))
            ->toBeTrue("Missing test file for command: {$commandName}");
    }
});
```

This test is automatically enforced via the pre-commit hook — a command without a test file will block the commit.

## 6. Git + OpenSpec Feature Branch Flow

Every OpenSpec change gets its own feature branch and lands on `main` as a single merge commit (`--no-ff`). No squash, no rebase-merge. No direct push to `main` — always via PR with CI passing.

### Branch Naming Convention

```
feat/<change-name>      # e.g. feat/import-cards-command
```

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
# → CHECKPOINT: Present change summary + manual review instructions
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

> `--delete-branch` deletes the remote branch on GitHub. `git remote prune origin` removes stale remote-tracking refs locally. The local branch is cleaned up automatically by `gh pr merge`.

### Resulting History on main

```
*   Merge pull request #43 from feat/import-cards-command
|\
| * docs(import-cards-command): archive change
| * refactor(import-cards-command): apply review feedback
| * feat(import-cards-command): add cards:import artisan command
| * docs(import-cards-command): add proposal, design and tasks
|/
*   Merge pull request #42 from feat/pack-and-card-models
|\
| * docs(pack-and-card-models): archive change
| * feat(pack-and-card-models): add Pack and Card models with migrations and factories
| * docs(pack-and-card-models): add proposal, design and tasks
|/
```

Each OpenSpec change becomes one merge-commit node on `main`'s first-parent history. Use `git log --first-parent main` to see just the change-level nodes. Use `git log --graph --oneline` to see the full graph including feature-branch commits.

Each feature follows: Explore → Propose → Implement → Verify → AI Review → Archive → PR → Merge.
Use the change name as commit scope for every commit on that branch.
Multiple commits per phase are fine — commit as often as makes sense (feat, fix, test, refactor, etc.).

### Feature branch hygiene

Because every feature-branch commit lands on `main` via the merge commit's second parent, WIP commits ("wip", "fix typo", "argh") would leak into the permanent history. Use `--fixup` + `--autosquash` to keep the branch clean without manual interactive rebases:

```bash
# During work — instead of a generic "fix" commit, mark the fixup explicitly
git commit --fixup=<target-sha>
# Produces a commit with message "fixup! <target commit subject>"

# Before pushing — collapse all fixup! commits into their targets
git rebase -i --autosquash origin/main
# Git pre-orders the TODO list; you usually just :wq
```

Make `--autosquash` the default so you don't need to remember the flag:

```bash
git config --global rebase.autosquash true
```

Use this only for small corrections (typos, follow-up tweaks). New meaningful commits stay normal. The goal: the commits that reach `main` should each stand on their own.

### Bisecting on `main`

`git bisect` finds the commit that introduced a bug via binary search. With merge commits, a naive bisect may land on an intermediate feature-branch commit (possibly with failing tests that aren't the regression you're hunting). Restrict bisect to the merge-commit nodes on `main`:

```bash
git bisect start --first-parent <bad-ref> <good-ref>
# Or set as default globally:
git config --global bisect.firstParent true
```

With `--first-parent`, each bisect step selects one OpenSpec change as a whole — the granularity that matters for production regressions.

## 7. Set up Deployment

### deploy.sh (local)

Create `deploy.sh` in the project root:

```sh
#!/bin/sh
set -e

if [ ! -f .env.deploy ]; then
    echo "Error: .env.deploy not found. Copy .env.deploy.example and fill in your credentials."
    exit 1
fi

set -a
. .env.deploy
set +a

echo "Building frontend assets..."
npm run build

echo "Uploading build assets..."
rsync -az --delete -e "ssh -p $DEPLOY_PORT" \
    public/build/ \
    $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH/public/build/

echo "Deploying..."
ssh -p $DEPLOY_PORT $DEPLOY_USER@$DEPLOY_HOST -t \
    "cd $DEPLOY_PATH && DEPLOY_PHP=$DEPLOY_PHP DEPLOY_COMPOSER=$DEPLOY_COMPOSER bash ./_deploy.sh"
```

> **Note:** The rsync step is needed when the server has no Node.js. If Node is available, `npm run build` can be run in `_deploy.sh` on the server instead.

### .env.deploy.example (commit this)

```
DEPLOY_USER=user
DEPLOY_HOST=host
DEPLOY_PORT=22
DEPLOY_PATH=/path/on/server
DEPLOY_PHP=/usr/bin/php
DEPLOY_COMPOSER=/usr/bin/composer
```

Add `.env.deploy` itself to `.gitignore` — it contains real credentials.

### _deploy.sh (on the server, in the project root)

```sh
#!/bin/sh
set -e

PHP=${DEPLOY_PHP:-php}
COMPOSER=${DEPLOY_COMPOSER:-composer}

git pull origin main

$PHP $COMPOSER install --no-interaction --optimize-autoloader --no-dev

$PHP artisan migrate --force

$PHP artisan optimize:clear
```

> Adjust `DEPLOY_PHP` and `DEPLOY_COMPOSER` in `.env.deploy` to match your server's paths (`which php` and `which composer` on the server).

### Run Deploy

```bash
./deploy.sh
```

## 8. Laravel Boost / MCP Setup

Laravel Boost provides an MCP server with tools designed for Laravel projects: database queries, schema inspection, log reading, and documentation search.

Register the MCP server in Claude Code:

```bash
claude mcp add laravel-boost -- php artisan mcp:serve
```

This adds the server to `.claude/settings.json`. Verify with:

```bash
claude mcp list
```

The Boost rules (database tools, doc search, Artisan guidance) are injected into Claude's context automatically when the MCP server is active. The project-level `CLAUDE.md` should additionally include skills activation rules and project-specific conventions.

## 9. Global `~/.claude/CLAUDE.md` (one-time, global)

Create `~/.claude/CLAUDE.md` with universal rules that apply to **all** projects. This avoids duplicating them in every project's `CLAUDE.md`.

```markdown
## Language Convention
All project artifacts in English. Conversation with Claude in German.

## Conventional Commits
Format: `<type>[scope]: <description>`
Types: feat, fix, docs, refactor, test, chore, style, perf, build, ci
OpenSpec changes: use change name as scope for every commit on that branch.
Multiple commits per phase are fine (feat, fix, test, refactor, etc.).

## Git Flow
Feature branches: `feat/<change-name>`. Merge into `main` as merge commits (`--no-ff`) — no squash, no rebase-merge. Full history on main.

## TDD
Tests first, then implementation.

## Claude Code Deny Rules
See Section 15 of dev-setup — add these rules to `~/.claude/settings.json`.
```

The project-level `CLAUDE.md` then only needs project-specific rules.

## 10. Extend .gitignore

Add the following:

```
.claude/settings.local.json
.env.deploy
```

## 11. Claude Code Agents (global, one-time)

Set up two agents in `~/.claude/agents/`:

- **laravel-debugger.md** — Diagnoses errors, stack traces, N+1 queries, queue failures
- **laravel-simplifier.md** — Reviews and simplifies code (clarity, redundancy, naming, conventions)

Source: [freekmurze/dotfiles/config/claude/agents/](https://github.com/freekmurze/dotfiles/tree/main/config/claude/agents)

## 12. Git-Delta (global, one-time)

```bash
brew install git-delta
```

Add to `~/.gitconfig`:

```ini
[core]
    pager = delta
[interactive]
    diffFilter = delta --color-only
[delta]
    navigate = true
    side-by-side = true
    line-numbers = true
[merge]
    conflictstyle = diff3
[diff]
    colorMoved = default
```

## 13. Optional: Additional CLI Tools

```bash
brew install eza bat zoxide fzf fnm
```

- `eza` — Better `ls` with icons
- `bat` — Better `cat` with syntax highlighting
- `zoxide` — Smart `cd` (learns directories)
- `fzf` — Fuzzy finder
- `fnm` — Fast Node.js version manager

## 14. Shell Aliases

Create a file `~/.aliases` and source it in `~/.zshrc`:

```bash
# Add to ~/.zshrc:
[ -f ~/.aliases ] && source ~/.aliases
```

Contents of `~/.aliases`:

```bash
# Laravel / PHP
alias a="php artisan"
alias mfs="php artisan migrate:fresh --seed"

# Pest (auto-detect pest vs phpunit)
function p() {
    if [ -f vendor/bin/pest ]; then
        vendor/bin/pest "$@"
    else
        vendor/bin/phpunit "$@"
    fi
}

# Composer
alias ci="composer install"
alias cu="composer update"
alias cr="composer require"
alias cda="composer dump-autoload"

# Claude Code
alias c="claude"
alias cy="claude --dangerously-skip-permissions"

# Git
alias nah="git reset --hard && git clean -df"
```

## 15. Claude Code Deny Rules (global)

Add deny rules to `~/.claude/settings.json`. These apply even in bypass mode (`--dangerously-skip-permissions`) and block destructive commands:

```json
{
  "permissions": {
    "deny": [
      "Bash(git push --force*)",
      "Bash(git push * --force*)",
      "Bash(git reset --hard*)",
      "Bash(git clean -f*)",
      "Bash(git clean -df*)",
      "Bash(git checkout -- .)",
      "Bash(rm -rf *)",
      "Bash(rm -rf /*)",
      "Bash(*db:wipe*)",
      "Bash(*migrate:fresh*--env=prod*)",
      "Bash(*migrate:fresh*--env=production*)",
      "Bash(*DROP DATABASE*)",
      "Bash(*DROP TABLE*)"
    ]
  }
}
```

Reference: [Safety Nets for Claude Code](https://cbox.dk/blog/safety-nets-for-claude-code-skip-permissions)

## 16. Branch Protection (GitHub Ruleset)

After the CI workflow has run at least once, configure a branch ruleset on GitHub (Settings → Rules → Rulesets → New branch ruleset):

1. **Ruleset Name:** `main-protection`
2. **Enforcement status:** Active
3. **Target branches:** Include by pattern → `main`
4. **Branch rules:**
   - **Require a pull request before merging** (Required approvals: 0 for solo projects)
   - **Require status checks to pass** → Add checks: `tests`, `lint`
   - **Allowed merge methods:** Merge commits only (no squash, no rebase-merge)
   - **Require linear history:** OFF — merge commits are incompatible with this setting
5. **Bypass list:** Leave empty (even admins go through PRs)

> **Note:** The status checks `tests` and `lint` only appear in the dropdown after the workflow has run at least once on `main`.

Also configure **Settings → General**:

- Enable **"Automatically delete head branches"** so merged PR branches are cleaned up on GitHub.
- Under **Pull Requests**: enable **"Allow merge commits"**, disable **"Allow squash merging"** and **"Allow rebase merging"**.
- Under **Pull Requests → Default commit message for merge commits**: select **"Pull request title"** so merge commits inherit the PR's conventional-commit subject (e.g. `feat(list-packs): add packs() endpoint`).

---

## Open TODOs

- [ ] **`/new-laravel-project` skill** — Once this dev-setup is stable, create a Claude Code skill that automates Sections 1–6 and 9–10 for new projects (composer create-project, SQLite, OpenSpec init, pre-commit hook, ArchTest, CLAUDE.md boilerplate, .gitignore).
