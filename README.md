# One Piece Cards API

Laravel application that builds a REST API for One Piece TCG card data. Card data is fetched from the official Bandai card list via [vegapull](https://github.com/Coko7/vegapull) and stored in a local SQLite database. Covers all sets published on the Bandai card list.

**Live API:** `https://op-cards-api.ditshej.ch/api/v1`
**API Docs:** `https://op-cards-api.ditshej.ch/docs/api`

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
| `php artisan cards:sync` | Upload local SQLite DB to production server |

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

Then deploy:

```bash
./deploy.sh
```

Builds frontend assets, uploads them via rsync, and runs `_deploy.sh` on the server (git pull, composer install, migrate, optimize).

---

## Token Management

Issue a token on the production server via SSH (run locally):

```bash
./create-token.sh "App Name" "email@example.com"
```

The plaintext token is printed once — store it securely. The script reads SSH credentials from `.env.deploy`.

To revoke a token, use Tinker on the server:

```bash
ssh -p $DEPLOY_PORT $DEPLOY_USER@$DEPLOY_HOST "cd $DEPLOY_PATH && php artisan tinker"
# Laravel\Sanctum\PersonalAccessToken::where('name', 'App Name')->delete();
```
