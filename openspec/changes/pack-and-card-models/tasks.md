## 1. Test Setup

- [x] 1.1 Enable RefreshDatabase in tests/Pest.php (uncomment line 17)

## 2. Pack

- [x] 2.1 Write Pack model tests (string PK, fillable attributes, hasMany cards relationship)
- [x] 2.2 Create packs migration (string id PK, name string, timestamps, only up())
- [x] 2.3 Create Pack model (string key, #[Fillable], cards() relationship)
- [x] 2.4 Create PackFactory with realistic OPTCG pack data

## 3. Card

- [x] 3.1 Write Card model tests (string PK, fillable attributes, belongsTo pack, JSON array casts, nullable fields)
- [x] 3.2 Create cards migration (string id PK, pack_id FK, all attributes from spec, JSON columns, only up())
- [x] 3.3 Create Card model (string key, #[Fillable], casts() for arrays, pack() relationship)
- [x] 3.4 Create CardFactory with realistic OPTCG card data and automatic pack association

## 4. Verify

- [x] 4.1 Run Pint to fix code style
- [x] 4.2 Run full test suite and confirm all tests pass
