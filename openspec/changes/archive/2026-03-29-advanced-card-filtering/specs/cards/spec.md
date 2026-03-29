## MODIFIED Requirements

### Requirement: Card searchability (delta)

**MODIFIED**: The `pack` filter now accepts a pack **label** (e.g., `OP-15`) instead of an internal pack ID. The filter performs a join on the `packs` table to resolve the label.

All new filter parameters from the `card-filtering` spec are added to the `GET /api/v1/cards` endpoint.
