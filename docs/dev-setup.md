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
npm install -g @fission-ai/openspec@latest
openspec init --tools claude
```

`openspec/config.yaml` mit Projekt-Context befüllen:

```yaml
schema: spec-driven

context: |
  Tech stack: PHP 8.x, Laravel 13, Pest 4, Pint
  Coding standards: Spatie PHP/Laravel guidelines (see docs/spatie-guidelines.md)
  Key conventions:
    - Happy path last, avoid else, use early returns
    - Only up() in migrations, never down()
    - Plural controller names (PostsController), CRUD methods only
    - Array notation for validation rules
    - Use config() helper, never env() outside config files
    - Typed properties over docblocks, constructor property promotion
    - kebab-case URLs, camelCase route names
    - Self-documenting code over comments
```

Initiale Specs unter `openspec/specs/` anlegen (pro Domain ein Ordner mit `spec.md`).

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

## 5. .gitignore ergänzen

Folgendes hinzufügen:

```
.claude/settings.local.json
```

## 6. Claude Code Agents (global, einmalig)

Zwei Agents in `~/.claude/agents/` einrichten:

- **laravel-debugger.md** — Diagnostiziert Errors, Stack Traces, N+1 Queries, Queue-Failures
- **laravel-simplifier.md** — Reviewt und vereinfacht Code (Klarheit, Redundanz, Naming, Konventionen)

Quelle: [freekmurze/dotfiles/config/claude/agents/](https://github.com/freekmurze/dotfiles/tree/main/config/claude/agents)

## 7. Git-Delta (global, einmalig)

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

## 8. Optional: Weitere CLI-Tools

```bash
brew install eza bat zoxide fzf fnm
```

- `eza` — Besseres `ls` mit Icons
- `bat` — Besseres `cat` mit Syntax-Highlighting
- `zoxide` — Smartes `cd` (lernt Verzeichnisse)
- `fzf` — Fuzzy-Finder
- `fnm` — Schneller Node.js Version Manager

## 9. Shell Aliases

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

## 10. Claude Code Deny Rules (global)

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
