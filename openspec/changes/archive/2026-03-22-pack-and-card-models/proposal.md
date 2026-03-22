## Why

The API has no database layer yet. Pack and Card models with their migrations and factories are the foundation that every other feature (import, API endpoints, seeding) depends on. This is the first implementation step.

## What Changes

- Create Pack model with string primary key, `hasMany` cards relationship
- Create Card model with string primary key, `belongsTo` pack relationship, JSON casts for array fields
- Create migrations for both tables (packs first, then cards with foreign key)
- Create factories for both models with realistic OPTCG data
- Enable `RefreshDatabase` in Pest.php for feature tests

## Capabilities

### New Capabilities

_(none — both capabilities already exist as specs)_

### Modified Capabilities

- `packs`: Adding model definition, migration schema, factory, and relationship to cards
- `cards`: Adding model definition, migration schema, factory, relationship to pack, and JSON casts for colors/attributes/types

## Impact

- **Database:** Two new tables (`packs`, `cards`) with foreign key constraint
- **Models:** Two new Eloquent models following Laravel 13 conventions (`#[Fillable]` attributes)
- **Tests:** `RefreshDatabase` enabled globally for feature tests
- **No API impact** — this change is purely the data layer

### Non-goals

- No API endpoints, controllers, or routes in this change
- No import command or business logic
- No seeders with real data (only factories for testing)
