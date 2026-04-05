# Implementation Roadmap

## Overview

5 sequential OpenSpec changes to build the One Piece TCG Card API.
Each change follows: `/opsx:propose` → `/opsx:apply` → commit → `/opsx:archive`

## Dependency Graph

```
Change 1: pack-and-card-models
    ├──→ Change 2: import-cards-command
    └──→ Change 3: packs-api-endpoints
              └──→ Change 4: cards-api-endpoints
                        └──→ Change 5: seeder-schedule-polish
```

## Changes

### ~~1. `pack-and-card-models`~~ ✅

Foundation layer. Models, migrations, factories for Pack and Card.

- **Specs:** `packs` (modified), `cards` (modified)
- **Scope:** Enable RefreshDatabase in Pest.php. Create Pack model (string PK, has many cards), Card model (string PK, belongs to pack, JSON casts for colors/attributes/types). Factories for both.
- **Conventions:** `#[Fillable]` attributes (Laravel 13), `casts()` method, `$incrementing = false`, only `up()` in migrations.

### ~~2. `import-cards-command`~~ ✅

Artisan command `cards:import` to ingest vegapull JSON files.

- **Specs:** `import` (modified)
- **Scope:** Read JSON files from a path, upsert packs and cards. Idempotent. Display summary of imported/updated counts.
- **Depends on:** Change 1

### ~~3. `packs-api-endpoints`~~ ✅

API infrastructure + Packs CRUD endpoints.

- **Specs:** `api` (modified), `packs` (modified)
- **Scope:** Create `routes/api.php` with `/v1` prefix. Register in `bootstrap/app.php`. PackResource, CardResource. PacksController (index, show). 404 handling.
- **Depends on:** Change 1

### ~~4. `cards-api-endpoints`~~ ✅

Cards CRUD with filtering, search, and pagination.

- **Specs:** `api` (modified), `cards` (modified)
- **Scope:** CardsController (index with pagination, show). Filters: color (`whereJsonContains`), category, cost, pack. Search: effect text (`LIKE`). Combined filters.
- **Depends on:** Change 3

### ~~5. `seeder-schedule-polish`~~ ✅

Operational polish: seeder, scheduled import, architecture tests.

- **Specs:** `import` (modified)
- **Scope:** DatabaseSeeder with factories. Optional weekly scheduled import (config-gated). Architecture tests (no `env()` outside config, no `dd()`).
- **Depends on:** Change 4

---

## Phase 2: Deployment & Data Pipeline

3 changes to bring the API live and populate it with real card data.

```
Change 6: vegapull-fetch-command (local, independent)
Change 7: metanet-deployment (server setup)
    └──→ Change 8: data-sync-command (requires running deployment)
```

### ~~6. `vegapull-fetch-command`~~ ✅

Artisan command `cards:fetch` that runs vegapull and imports directly.

- **Specs:** `import` (modified — requirement "Vegapull Integration" already defined)
- **Scope:** New command `cards:fetch`: checks if `vega` binary is available, runs `vega pull all`, calls `cards:import`. Config key `import.vegapull_binary`.
- **Depends on:** Change 2

### ~~7. `metanet-deployment`~~ ✅

Deploy Laravel app to Metanet shared hosting.

- **Specs:** new spec `deployment`
- **Scope:** Explore server environment (PHP version, paths, web root). Deploy scripts (`deploy.sh` local, `_deploy.sh` remote): git pull, composer install, artisan optimize. Analogous to `statamic-ferienspasswil`.
- **Depends on:** Changes 1-5 (complete API)

### ~~8. `data-sync-command`~~ ✅

Artisan command `cards:sync` that uploads the local SQLite DB to Metanet.

- **Specs:** `import` (modified) or new spec `deployment`
- **Scope:** New command `cards:sync`: optionally run `cards:fetch` (`--fetch`), copy SQLite DB to Metanet via SCP, clear remote cache. SSH config from `.env`.
- **Depends on:** Change 7
