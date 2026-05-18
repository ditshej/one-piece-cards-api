## Coding Style

### Collections over Loops

- Prefer `Collection` methods and array functions (`array_map`, `array_filter`, `array_reduce`) over `foreach` and other loops
- Laravel's Illuminate Collection is the go-to for transforming, filtering, and aggregating data
- Use `collect()` to wrap arrays when chaining multiple operations
- Only fall back to loops when performance-critical or when a loop is genuinely clearer

### Spatie PHP/Laravel Conventions

- Happy path last, avoid `else`, use early returns
- Only `up()` in migrations, never `down()`
- Plural controller names (`PostsController`), CRUD methods only
- Array notation for validation rules
- Use `config()` helper, never `env()` outside config files
- Typed properties over docblocks, constructor property promotion
- kebab-case URLs, camelCase route names
- Self-documenting code over comments
