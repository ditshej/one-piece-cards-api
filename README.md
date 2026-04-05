# One Piece Cards API

REST API for One Piece TCG card data — packs, cards, and filters. Covers all sets including OP15+, sourced from the official Bandai card list via [vegapull](https://github.com/Coko7/vegapull).

**Live API:** `https://op-cards-api.ditshej.ch/api/v1`
**API Docs:** `https://op-cards-api.ditshej.ch/docs/api`

---

## Authentication

All endpoints require a Bearer token:

```
Authorization: Bearer <your-token>
```

Tokens are issued per consuming application. To request access, contact the owner.

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
./deploy.sh
```

Builds frontend assets, uploads them via rsync, and runs `_deploy.sh` on the server (git pull, composer install, migrate, optimize).

---

## Token Management

Issue a token for a new consuming app from your local machine:

```bash
./create-token.sh "App Name" "email@example.com"
```

The plaintext token is printed once — store it securely. The script reads SSH credentials from `.env.deploy`.

To revoke a token, use Tinker on the server:

```bash
ssh -p $PORT $USER@$HOST -t "cd /path && php artisan tinker"
# Laravel\Sanctum\PersonalAccessToken::where('name', 'App Name')->delete();
```
