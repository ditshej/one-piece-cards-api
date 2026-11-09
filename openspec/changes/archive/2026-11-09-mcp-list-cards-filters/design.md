## Decisions

- Schema: list filters use `anyOf` of the scalar type and an array of it, so a single value and a list are both valid. Boolean filters use `boolean()`.
- Validation: `handle()` validates `limit` (`integer|between:1,100`) via `$request->validate()`; a `ValidationException` is turned into an error response by the MCP server.
- No filter logic in the tool: `$request->all()` goes to `Card::applyFilters()`, which already normalises scalars to arrays.
- Key parity test: the keys of `CardsIndexRequest::rules()` (without `.*` rules), with `pack` → `pack_label` and `per_page` → `limit`, must equal the schema keys.
