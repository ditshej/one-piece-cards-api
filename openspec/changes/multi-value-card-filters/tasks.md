## 1. Tests (TDD — write first)

- [ ] 1.1 Add test: `GET /api/v1/cards?cost[]=3&cost[]=5` returns only cards with cost 3 or 5
- [ ] 1.2 Add test: `GET /api/v1/cards?cost[]=5` returns same result as `?cost=5`
- [ ] 1.3 Add test: `GET /api/v1/cards?cost[]=abc` returns 422

## 2. Implementation

- [ ] 2.1 Add `prepareForValidation()` to `CardsIndexRequest` — normalise scalar `cost` to single-element array
- [ ] 2.2 Update validation rules: `'cost' => ['nullable', 'array']`, `'cost.*' => ['integer']`
- [ ] 2.3 Update `Card::scopeApplyFilters()` — replace `where('cost', ...)` with `whereIn('cost', ...)`
