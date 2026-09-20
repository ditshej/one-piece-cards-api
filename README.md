# One Piece Cards API

![CI](https://github.com/ditshej/one-piece-cards-api/actions/workflows/ci.yml/badge.svg)

Laravel application that builds a REST API for One Piece TCG card data. Card data is fetched from the official Bandai card list via [vegapull](https://github.com/Coko7/vegapull) and stored in a local SQLite database. Covers all sets published on the Bandai card list.

- **Live API:** `https://op-cards-api.ditshej.ch/api/v1`
- **API Docs:** `https://op-cards-api.ditshej.ch/docs/api`

---

## Installation

**Requirements:** PHP 8.4, Composer, Node.js, [vegapull](https://github.com/Coko7/vegapull)

```bash
# 1. Clone and install dependencies
git clone <repo-url> && cd one-piece-cards-api
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Run migrations
php artisan migrate

# 4. Fetch card data from Bandai via vegapull (may take a few minutes)
php artisan cards:fetch

# 5. Issue yourself an API token
php artisan token:create "My App" "me@example.com"
```

> **vegapull** must be installed and available as `vega` on your `$PATH`.
> See [github.com/Coko7/vegapull](https://github.com/Coko7/vegapull) for installation instructions.
> The binary name can be changed via `VEGAPULL_BINARY` in `.env`.

---

## Data Import

| Command | Description |
|---------|-------------|
| `php artisan cards:fetch` | Fetch from Bandai via vegapull and import |
| `php artisan cards:import <path>` | Import from existing vegapull JSON files |
| `php artisan cards:sync` | Upload local card JSON to production and run `cards:import` there |

---

## Resolving Card Lists

`cards:resolve` turns a list of bare card numbers into full card data, read from the local
database — no network access, no API token.

```bash
php artisan cards:resolve deck.txt                  # from a file, as JSON
php artisan cards:resolve deck.txt --format=markdown # readable, for pasting into a chat
pbpaste | php artisan cards:resolve                  # straight from the clipboard
php artisan cards:resolve                            # paste, then press Ctrl-D
php artisan cards:resolve - --deck > deck-data.json  # explicit stdin, with deck checks
```

**Input** — one entry per line, in either format; blank lines are ignored and the trailing
card name is optional and informational:

```
4xST01-011
4 OP17-113 Streusen
```

**Output** — `--format=json` (default) yields `leader`, `cards`, `totals` and `warnings`;
`--format=markdown` yields a leader block, a card table and the effect and trigger text per
card. Each entry carries quantity, id, name, category, colors, cost, power, counter, types,
effect, trigger, rarity and card_set.

**Errors abort:** malformed lines, quantities below 1, duplicate card IDs, and card IDs
missing from the database (all reported together).

**`--deck`** additionally checks deck composition — exactly one leader, 50 main deck cards,
at most four copies of a card — and reports violations as warnings without changing the exit
status. Warnings go to stderr, so redirecting stdout to a file yields the document alone.

### Pasting a list into the terminal

Run the command with no file argument and it waits for the list on standard input, which is
where a paste lands:

```
$ php artisan cards:resolve --format=markdown
Paste the deck list, then press Ctrl-D.
1 OP13-004 Sabo
4xST01-011
^D
# Resolved Cards
...
```

The hint appears immediately so the command does not look stuck, and it goes to stderr, so it
stays out of a redirected file. Ctrl-D only takes effect at the start of a line: if the pasted
text does not end in a newline, press Enter first, or Ctrl-D twice.

### Straight back into the clipboard

Because the document goes to stdout and nothing else does, the shell can take it from there
(`pbcopy` on macOS, `xclip -selection clipboard` or `wl-copy` elsewhere):

```bash
pbpaste | php artisan cards:resolve | pbcopy             # clipboard in, clipboard out
pbpaste | php artisan cards:resolve | tee >(pbcopy)      # …and show it at the same time
php artisan cards:resolve | pbcopy                       # paste by hand, result to clipboard
```

The last one combines both: standard input stays the keyboard while standard output goes into
the pipe, so you paste the list, press Ctrl-D, and the document lands in the clipboard while
the terminal shows only the hint and any warnings.

Warnings still appear in the terminal either way, because they go to stderr.

A shell function saves the typing and the `cd`:

```bash
# in ~/.zshrc
deckdata() {
  pbpaste | php /path/to/one-piece-cards-api/artisan cards:resolve "$@" | pbcopy \
    && echo "Card data copied to the clipboard."
}
```

Then `deckdata` resolves whatever is in the clipboard, and `deckdata --format=markdown --deck`
does the same with deck checks and readable output.

---

## MCP Server

The API exposes an [MCP](https://modelcontextprotocol.io) server at `/mcp` for use with AI assistants (e.g. Claude). Available tools mirror the REST API:

| Tool | Description |
|------|-------------|
| `list-packs` | List all card packs |
| `get-pack` | Get a pack with its cards |
| `list-cards` | List cards with filters |
| `get-card` | Get a single card |

Authentication uses the same Bearer token as the REST API.

---

## Authentication

All endpoints require a Bearer token:

```
Authorization: Bearer <your-token>
```

Tokens are issued per consuming application. To request access to the live API, contact the owner.

---

## Endpoints

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/v1/packs` | List all card packs |
| `GET` | `/api/v1/packs/{id}` | Get a pack with its cards |
| `GET` | `/api/v1/cards` | List cards (filterable, paginated) |
| `GET` | `/api/v1/cards/{id}` | Get a single card |

### Card filters

| Parameter | Description |
|-----------|-------------|
| `color` | Filter by color (e.g. `red`, `blue`) |
| `category` | Filter by category (e.g. `Character`) |
| `cost` | Filter by cost value |
| `pack_id` | Filter by pack |
| `search` | Full-text search in effect text |

Full reference: [`/docs/api`](https://op-cards-api.ditshej.ch/docs/api)

---

## Deployment

Requires `.env.deploy` with SSH credentials (copy from `.env.deploy.example`):

```bash
cp .env.deploy.example .env.deploy
```

| Variable | Description |
|----------|-------------|
| `DEPLOY_USER` | SSH username |
| `DEPLOY_HOST` | Server hostname or IP |
| `DEPLOY_PORT` | SSH port (default: `22`) |
| `DEPLOY_PATH` | Absolute path to the project on the server |
| `DEPLOY_PHP` | Path to PHP binary on the server (e.g. `/opt/php83/bin/php`) |

Then deploy:

```bash
./deploy.sh
```

Builds frontend assets, uploads them via rsync, and runs `_deploy.sh` on the server (git pull, composer install, migrate, optimize).

### Card Sync

`cards:sync` uploads the local vegapull card JSON to production and runs `cards:import` there — it never transfers the SQLite database, so production auth/session/token data is untouched. Configure it via the following variables in the local `.env` (not `.env.deploy` — the command runs locally and reads `config('import.*')`):

| Variable | Description |
|----------|-------------|
| `SYNC_HOST` | SSH hostname or IP of the production server |
| `SYNC_USER` | SSH username |
| `SYNC_PORT` | SSH port (default: `22`) |
| `SYNC_PATH` | Absolute path to the project on the server |
| `SYNC_PHP` | Path to PHP binary on the server (default: `php`, e.g. `/opt/php83/bin/php` in production) |

```bash
php artisan cards:sync            # sync existing local JSON
php artisan cards:sync --fetch    # refresh from Bandai first, then sync
```

---

## Token Management

Issue a token on the production server via SSH (run locally):

```bash
./create-token.sh "App Name" "email@example.com"
```

The plaintext token is printed once — store it securely. The script reads SSH credentials from `.env.deploy`.

To list all tokens:

```bash
# Via SSH wrapper (run locally)
./create-token.sh --list

# Directly on any environment
php artisan token:list
```

To revoke a token:

```bash
# Via SSH wrapper (run locally)
./create-token.sh --revoke "App Name"

# Directly on any environment
php artisan token:revoke "App Name"
```
