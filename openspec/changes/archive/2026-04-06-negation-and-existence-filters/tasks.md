## 1. Tests (TDD — write first)

- [x] 1.1 Add test: `?color_not[]=Red` excludes Red cards
- [x] 1.2 Add test: `?rarity_not[]=C&rarity_not[]=UC` excludes C and UC cards
- [x] 1.3 Add test: `?card_set_not[]=OP01` excludes OP01 cards
- [x] 1.4 Add test: `?category_not[]=Leader` excludes Leader cards
- [x] 1.5 Add test: `?category_not[]=Invalid` returns 422
- [x] 1.6 Add test: `?type_not[]=Navy` excludes Navy type cards
- [x] 1.7 Add test: `?attribute_not[]=Slash` excludes cards with Slash attribute
- [x] 1.8 Add test: `?attribute_not[]=Slash&attribute_not[]=Strike` excludes cards with either attribute
- [x] 1.9 Add test: `?keyword_not[]=Blocker` excludes cards with [Blocker] in effect or trigger
- [x] 1.10 Add test: `?cost_not[]=9&cost_not[]=10` excludes cards with those costs
- [x] 1.11 Add test: `?cost_not[]=abc` returns 422
- [x] 1.12 Add test: `?power_not[]=9000` excludes cards with that power
- [x] 1.13 Add test: `?power_not[]=abc` returns 422
- [x] 1.14 Add test: `?counter[]=1000` returns only cards with counter 1000
- [x] 1.15 Add test: `?counter[]=1000&counter[]=2000` returns cards with either counter value
- [x] 1.16 Add test: `?counter[]=abc` returns 422
- [x] 1.17 Add test: `?counter_not[]=2000` excludes cards with counter 2000
- [x] 1.18 Add test: `?has_trigger=true` returns only cards with a trigger
- [x] 1.19 Add test: `?has_trigger=false` returns only cards without a trigger
- [x] 1.20 Add test: `?has_effect=true` returns only cards with an effect
- [x] 1.21 Add test: `?has_effect=false` returns only cards without an effect
- [x] 1.22 Add test: `?has_counter=true` returns only cards with a counter
- [x] 1.23 Add test: `?has_counter=false` returns only cards without a counter

## 2. Implementation

- [x] 2.1 Extend `prepareForValidation()` in `CardsIndexRequest` — add `counter`, `color_not`, `rarity_not`, `card_set_not`, `category_not`, `type_not`, `attribute_not`, `keyword_not`, `cost_not`, `power_not`, `counter_not` to the normalization loop
- [x] 2.2 Add validation rules for all `_not[]` params and `counter[]` — mirror positive counterparts; `category_not.*` gets `in:Character,Event,Leader,Stage`; `cost_not.*`, `power_not.*`, `counter.*`, `counter_not.*` get `integer`
- [x] 2.3 Add validation rules for `has_trigger`, `has_effect`, `has_counter` — `nullable`, `boolean`
- [x] 2.4 Extend normalization loop in `scopeApplyFilters` — add all `_not[]` params
- [x] 2.5 Add `color_not` filter — `whereJsonDoesntContain` loop (AND)
- [x] 2.6 Add `rarity_not` filter — `whereNotIn`
- [x] 2.7 Add `card_set_not` filter — `whereNotIn`
- [x] 2.8 Add `category_not` filter — `whereNotIn`
- [x] 2.9 Add `type_not` filter — `whereJsonDoesntContain` loop (AND)
- [x] 2.10 Add `attribute_not` filter — `whereJsonDoesntContain` loop (AND)
- [x] 2.11 Add `keyword_not` filter — `where NOT LIKE` loop (AND, both effect and trigger)
- [x] 2.12 Add `cost_not` filter — `whereNotIn`
- [x] 2.13 Add `power_not` filter — `whereNotIn`
- [x] 2.14 Add `counter` filter — `whereIn`
- [x] 2.15 Add `counter_not` filter — `whereNotIn`
- [x] 2.16 Add `has_trigger` filter — `whereNull` / `whereNotNull`
- [x] 2.17 Add `has_effect` filter — `whereNull` / `whereNotNull`
- [x] 2.18 Add `has_counter` filter — `whereNull` / `whereNotNull`
