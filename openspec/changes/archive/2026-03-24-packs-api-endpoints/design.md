## Context

Pack and Card models with migrations, factories, and the `cards:import` command are in place. There are no API routes yet — `routes/api.php` doesn't exist and `bootstrap/app.php` only registers web and console routes. The first external consumer (Brook OP15 Deck Simulator) needs HTTP access to pack and card data.

## Goals / Non-Goals

**Goals:**
- Establish the `/api/v1` routing infrastructure for all future API endpoints
- Deliver read-only Packs endpoints (index, show)
- Create reusable Eloquent API Resources (PackResource, CardResource)
- Consistent JSON error responses for 404s

**Non-Goals:**
- Cards filtering/search/pagination endpoints (Change 4)
- Authentication, rate limiting, CORS configuration
- API documentation generation

## Decisions

### 1. Route registration via `withRouting(api:)`

Register `routes/api.php` in `bootstrap/app.php` using the `api:` parameter and `apiPrefix: 'api/v1'`.

**Why:** Laravel's built-in `withRouting(api:)` automatically applies the `api` middleware group (throttle, stateless). The `apiPrefix` parameter sets the prefix globally without needing a `Route::prefix()` wrapper. No custom middleware configuration needed.

**Alternative considered:** Manual `Route::prefix('v1')->group()` inside `api.php` — rejected because `apiPrefix` is cleaner and ensures the `api` middleware group is applied.

### 2. Plural controller names (PacksController)

Following Spatie conventions: `PacksController` with `index` and `show` methods.

**Why:** Project convention (plural controller names, CRUD methods only).

### 3. PackResource wraps CardResource in show

`PackResource` returns cards as a `CardResource::collection()` when the cards relationship is loaded. On `index`, cards are not eager-loaded (only pack metadata). On `show`, cards are eager-loaded and included.

**Why:** Avoids N+1 on show, keeps index response lightweight. The `whenLoaded` helper handles both cases cleanly.

### 4. No pagination on pack index

Return all packs without pagination. OPTCG has ~30 booster packs + ~20 starter decks — well under any practical limit.

**Why:** Simpler for consumers. Pagination adds complexity for no benefit at this dataset size.

### 5. No dedicated middleware or service layer

Controller actions are simple enough (one query each) that a service layer would be over-engineering. No custom middleware needed — Laravel's built-in `api` middleware group suffices.

## Risks / Trade-offs

- **[Pack count growth]** → OPTCG releases ~4 packs per year. Even at 10 years, ~100 packs is fine without pagination. Revisit if needed.
- **[CardResource defined here but cards endpoints come later]** → The resource is needed now for pack show. Change 4 reuses it. No wasted work.
