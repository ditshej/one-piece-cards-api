## 1. Tests

- [ ] 1.1 Write tests for `GET /api/v1/cards` (index: paginated list, empty state, pagination metadata)
- [ ] 1.2 Write tests for card filters (color, category, cost, pack, combined filters)
- [ ] 1.3 Write tests for card search (effect text, trigger text)
- [ ] 1.4 Write tests for `GET /api/v1/cards/{id}` (show: card details, 404 for missing card)

## 2. CardsController

- [ ] 2.1 Create `CardsController` with `index` (paginated, filterable, searchable) and `show`
- [ ] 2.2 Register card routes in `routes/api.php`

## 3. Finalize

- [ ] 3.1 Run `vendor/bin/pint --dirty --format agent`
- [ ] 3.2 Run `php artisan test --compact` — all tests green
