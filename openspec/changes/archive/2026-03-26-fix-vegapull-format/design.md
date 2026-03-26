## Context

Real vegapull output (v1.2.1) differs from assumed format:

**packs.json** (object keyed by ID):
```json
{"569101": {"id": "569101", "raw_title": "BOOSTER PACK -ROMANCE DAWN- [OP-01]", "title_parts": {"prefix": "BOOSTER PACK", "title": "ROMANCE DAWN", "label": "OP-01"}}}
```

**cards_{id}.json** (array):
```json
[{"id": "OP01-001", "pack_id": "569101", "name": "Roronoa Zoro", "rarity": "Leader", "category": "Leader", "img_url": "../images/cardlist/card/OP01-001.png?260325", "img_full_url": "https://en.onepiece-cardgame.com/images/cardlist/card/OP01-001.png?260325", "cost": 5, "attributes": ["Slash"], "power": 5000, "counter": null, "colors": ["Red"], "block_number": 1, "types": ["Supernovas", "Straw Hat Crew"], "effect": "...", "trigger": null}]
```

**Directory structure:** Output goes to `{path}/json/` subdirectory.

## Goals / Non-Goals

**Goals:**
- Make `cards:fetch` work non-interactively with real vegapull
- Make `cards:import` correctly parse real vegapull JSON
- Keep card IDs as-is (e.g. `OP01-001`) — these are stable across vegapull versions

**Non-Goals:**
- Changing the Pack primary key strategy (keep vegapull's numeric IDs)
- Normalizing pack names to title case

## Decisions

### 1. Replace `vega pull all` with packs + cards loop

`vega pull all` requires TTY. Instead:
1. `vega pull packs` — downloads `packs.json`
2. For each pack ID: `vega pull cards {id}` — downloads `cards_{id}.json`

Both commands work non-interactively. Pass `--language english` to avoid the interactive language prompt.

### 2. Add `label` column to packs table

Store vegapull's `title_parts.label` (e.g. `OP-01`) as a separate column. This is the human-readable identifier consumers expect. The primary key remains vegapull's numeric ID (`569101`).

**Alternative:** Use the label as PK — rejected because vegapull uses numeric IDs everywhere and some packs have no label (e.g. "Promotion card").

### 3. Read packs.json for pack metadata in import

The import command reads `packs.json` first to get pack names and labels, then processes card files. Cards reference packs by numeric ID.

### 4. Use `img_full_url` instead of `img_url`

The real `img_url` is a relative path. `img_full_url` is the absolute URL. Map `img_full_url` to the existing `img_url` database column.

### 5. Adjust path to include `json/` subdirectory

Vegapull outputs to `{output}/json/`. The import command looks for files in `{vegapull_path}/json/` instead of `{vegapull_path}/`.

## Risks / Trade-offs

- **[Breaking change for existing data]** → Fresh import required. Since there's no production data yet, this is acceptable.
- **[Pack IDs change from labels to numbers]** → API consumers that hardcode `OP01` will need to use the `label` field instead. Since no consumers exist yet, no migration needed.
- **[Packs without labels]** → Some packs (e.g. "Promotion card", "Other Product Card") have `null` labels. The `label` column must be nullable.
