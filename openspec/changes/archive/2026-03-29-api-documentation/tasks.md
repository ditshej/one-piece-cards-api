## 1. Install & configure package

- [x] 1.1 Install `composer require dedoc/scramble`
- [x] 1.2 Publish Scramble config: `php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider"`
- [x] 1.3 Adjust `config/scramble.php`: title ("One Piece Cards API"), version, API path (`api/v1`)

## 2. Verification

- [x] 2.1 `GET /docs/api.json` returns valid OpenAPI 3.1 JSON with all 4 endpoints
- [x] 2.2 `GET /docs/api` returns Stoplight Elements UI
- [x] 2.3 Cards endpoint contains query parameters (color, category, cost, pack, search) in the spec
- [x] 2.4 Response schemas for Pack and Card are correct (all fields from the Eloquent Resources)

## 3. Tests

- [x] 3.1 Feature test: `GET /docs/api.json` returns 200
- [x] 3.2 Feature test: `GET /docs/api` returns 200
