## 1. API Infrastructure

- [x] 1.1 Write tests for API route registration (`/api/v1/packs` responds, `/api/packs` returns 404)
- [x] 1.2 Create `routes/api.php` with `/v1` prefix group
- [x] 1.3 Register API routes in `bootstrap/app.php` via `withRouting(api:, apiPrefix:)`

## 2. Eloquent API Resources

- [x] 2.1 Write tests for CardResource output (all card fields present)
- [x] 2.2 Create `CardResource` with all card fields
- [x] 2.3 Write tests for PackResource output (id, name, conditional cards)
- [x] 2.4 Create `PackResource` with `id`, `name`, and `whenLoaded('cards')` via CardResource

## 3. PacksController

- [x] 3.1 Write tests for `GET /api/v1/packs` (index: returns all packs, empty state)
- [x] 3.2 Write tests for `GET /api/v1/packs/{id}` (show: pack with cards, 404 for missing pack)
- [x] 3.3 Create `PacksController` with `index` (all packs ordered by id) and `show` (pack with eager-loaded cards)
- [x] 3.4 Register routes in `routes/api.php`

## 4. Finalize

- [x] 4.1 Run `vendor/bin/pint --dirty --format agent`
- [x] 4.2 Run `php artisan test --compact` — all tests green
