# Dev Setup Checklist

Reusable checklist for setting up a new Laravel project with Claude Code and OpenSpec.
Based on learnings from the "yohohoho" project, inspired by [freekmurze/dotfiles](https://github.com/freekmurze/dotfiles).

---

## 1. Project Init

```bash
composer create-project laravel/laravel project-name
cd project-name
composer require --dev laravel/boost laravel/pail laravel/pint pestphp/pest pestphp/pest-plugin-laravel
```

## 2. OpenSpec einrichten

```bash
npm install -g @fission-ai/openspec
openspec init
```

Das erstellt:
- `openspec/config.yaml` — Projekt-Config
- `openspec/specs/` — Langlebige Projekt-Specs (werden durch Changes befüllt)
- `openspec/changes/` — Kurzlebige Arbeitspakete
- `.claude/skills/openspec-*` — Skills für Claude Code Agents

`openspec/config.yaml` mit Projekt-Context befüllen:

```yaml
schema: spec-driven

context: |
  ## Project
  <Projekt-Beschreibung>

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
  proposal:
    - Always include a "Non-goals" section
```

### OpenSpec Workflow

Jeder Change durchläuft 4 Artifacts:

```
/openspec-propose     → proposal.md (WARUM)
                      → specs/*.md  (WAS — Requirements mit WHEN/THEN Scenarios)
                      → design.md   (WIE — Architektur-Entscheide)
                      → tasks.md    (TODO — Checkboxen, Tests vor Code)
/openspec-apply-change → Implementieren (Tasks abarbeiten)
/openspec-archive-change → Abschliessen, Specs in Haupt-Specs syncen
```

## 3. Spatie Guidelines

Kopiere `docs/spatie-guidelines.md` ins neue Projekt. Diese Datei enthält die Spatie PHP/Laravel Coding Standards, optimiert für AI Code Assistants.

Quelle: [freekmurze/dotfiles](https://github.com/freekmurze/dotfiles/blob/main/config/claude/laravel-php-guidelines.md)

## 4. Conventional Commits

In `CLAUDE.md` unter `## Git Commits` die Convention festlegen:

- Format: `<type>[optional scope]: <description>`
- Types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`
- Scope optional in Klammern: `feat(auth): add login endpoint`
- Breaking changes: `!` vor dem Doppelpunkt: `feat!: remove legacy API`
- Description: Imperativ, Kleinbuchstaben, kein Punkt am Ende

Referenz: [conventionalcommits.org/en/v1.0.0](https://www.conventionalcommits.org/en/v1.0.0/)

## 5. TDD (Test-Driven Development)

In `CLAUDE.md` unter `## Testing (TDD)` festlegen:

- Tests ZUERST schreiben, dann den Code implementieren
- Pest 4 für alle Tests, bevorzugt Feature Tests
- Umfassende Test-Coverage anstreben — kein Feature ohne Tests
- `php artisan test --compact` nach jeder Änderung

## 6. Git + OpenSpec Feature Branch Flow

Jede OpenSpec-Change bekommt einen eigenen Feature-Branch. Kein Squash-Merge — die volle History bleibt auf `main` erhalten.

### Branch-Namenskonvention

```
feat/<change-name>      # z.B. feat/import-cards-command
```

### Workflow pro Change

```bash
# 1. Branch erstellen
git checkout -b feat/<change-name>

# 2. OpenSpec Change erstellen & planen
openspec new change "<change-name>"
# → proposal.md, specs/, design.md, tasks.md erstellen
# → Commit: "docs: add openspec change <change-name>"

# 3. Implementation (TDD)
# /opsx:apply — Tasks abarbeiten

# 4. Code Review
# a) laravel-simplifier Agent — automatisches Review
# b) Findings fixen, dann committen
# c) Agent gibt Code-Übersicht (Architektur, Dateien, Tests)
#    + manuelle Testanleitung falls UI/Endpunkte betroffen
# d) User reviewt selbst (PhpStorm, GitHub PR, oder git diff main...HEAD)
# → Erst nach User-OK weitermachen!
# → Commit(s): "feat: ...", "refactor: ...", etc.

# Feature-Branch aktuell halten: Rebase statt Merge
git fetch origin && git rebase origin/main

# 5. Archivierung
# /opsx:archive — Change abschliessen, Specs mergen
# → Commit: "docs: archive <change-name> change"

# 6. Merge nach main (kein Squash!)
git checkout main
git merge feat/<change-name>
git push
git branch -d feat/<change-name>
```

### Resultierende History auf main

```
* docs: archive import-cards-command change
* refactor: apply simplifier findings
* feat: add cards:import artisan command
* docs: add openspec change import-cards-command
* docs: archive pack-and-card-models change
* feat: add Pack and Card models with migrations and factories
* docs: add openspec change pack-and-card-models
```

Jedes Feature hat 3-4 Commits: Planung → Implementation → Review (optional) → Archivierung.

## 7. Deployment einrichten

### deploy.sh (lokal)

Erstelle `deploy.sh` im Projekt-Root:

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
ssh -p $DEPLOY_PORT $DEPLOY_USER@$DEPLOY_HOST -t "cd $DEPLOY_PATH && bash ./_deploy.sh"
```

> **Hinweis:** Der rsync-Schritt ist nötig wenn der Server kein Node.js hat. Wenn Node verfügbar ist, kann stattdessen `npm run build` im `_deploy.sh` auf dem Server ausgeführt werden.

### .env.deploy.example (committen)

```
DEPLOY_USER=user
DEPLOY_HOST=host
DEPLOY_PORT=22
DEPLOY_PATH=/path/on/server
```

`.env.deploy` selbst ins `.gitignore` eintragen — enthält echte Credentials.

### _deploy.sh (auf dem Server, im Projekt-Root)

```sh
#!/bin/sh
set -e

PHP=/usr/bin/php

git pull origin main

$PHP /usr/bin/composer install --no-interaction --optimize-autoloader --no-dev

$PHP artisan migrate --force

$PHP artisan optimize:clear
```

> PHP-Pfad je nach Server anpassen (`which php` auf dem Server).

### Deploy ausführen

```bash
./deploy.sh
```

## 8. .gitignore ergänzen

Folgendes hinzufügen:

```
.claude/settings.local.json
```

## 9. Claude Code Agents (global, einmalig)

Zwei Agents in `~/.claude/agents/` einrichten:

- **laravel-debugger.md** — Diagnostiziert Errors, Stack Traces, N+1 Queries, Queue-Failures
- **laravel-simplifier.md** — Reviewt und vereinfacht Code (Klarheit, Redundanz, Naming, Konventionen)

Quelle: [freekmurze/dotfiles/config/claude/agents/](https://github.com/freekmurze/dotfiles/tree/main/config/claude/agents)

## 10. Git-Delta (global, einmalig)

```bash
brew install git-delta
```

In `~/.gitconfig` hinzufügen:

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

## 11. Optional: Weitere CLI-Tools

```bash
brew install eza bat zoxide fzf fnm
```

- `eza` — Besseres `ls` mit Icons
- `bat` — Besseres `cat` mit Syntax-Highlighting
- `zoxide` — Smartes `cd` (lernt Verzeichnisse)
- `fzf` — Fuzzy-Finder
- `fnm` — Schneller Node.js Version Manager

## 12. Shell Aliases

Eigene Datei `~/.aliases` anlegen und in `~/.zshrc` sourcen:

```bash
# In ~/.zshrc einfügen:
[ -f ~/.aliases ] && source ~/.aliases
```

Inhalt von `~/.aliases`:

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

## 13. Claude Code Deny Rules (global)

In `~/.claude/settings.json` Deny Rules hinzufügen. Diese greifen auch im Bypass-Modus (`--dangerously-skip-permissions`) und blocken destruktive Befehle:

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

Referenz: [Safety Nets for Claude Code](https://cbox.dk/blog/safety-nets-for-claude-code-skip-permissions)
