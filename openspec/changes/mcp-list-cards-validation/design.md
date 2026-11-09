## Decisions

- One rule source: `CardsIndexRequest::filterRules()` (static) holds the rules; `rules()` returns them. The tool calls `$request->validate()` with the same rules after renaming `pack_label` to `pack` and `limit` to `per_page`.
- Scalars are wrapped into lists before validation (`CardsIndexRequest::wrapListFilters()`), shared with REST. Boolean strings are not coerced in MCP: `"false"` is a validation error.
- Colors: `in:Red,Blue,Green,Purple,Black,Yellow` on `color.*` and `color_not.*`.
