## 1. Config Change

- [x] 1.1 In `config/scramble.php`, change `'theme' => 'light'` to `'theme' => 'system'`

## 2. Tests

- [x] 2.1 Write a feature test asserting that `GET /docs/api` returns HTTP 200 (docs remain accessible after the config change)
- [x] 2.2 Write a feature test asserting that the docs response HTML contains `data-theme` attribute set dynamically (i.e. the system-theme script block is present in the rendered HTML)

## 3. Verification

- [x] 3.1 Run the affected tests: `php artisan test --compact --filter=DocsTest`
- [x] 3.2 Run Pint to ensure code style: `vendor/bin/pint --dirty --format agent`
