## Context

The API root currently serves the default Laravel 13 welcome page. The page has a polished two-panel layout with animations and dark mode support — this visual structure is worth keeping. Only the content needs to change.

The right panel contains two SVGs: the "Laravel" wordmark (complex vector paths) and a "13" number rendered in five layered groups with CSS blend modes creating a fan-out animation. Both need to be replaced with project-specific equivalents.

## Goals / Non-Goals

**Goals:**
- Replace left panel text with API-relevant content
- Replace Laravel wordmark SVG with "One Piece TCG" as an SVG text element
- Replace "13" fan-effect SVG with "API" using SVG text elements in the same five-group structure
- Preserve all existing Tailwind classes, animations, dark mode, and layout

**Non-Goals:**
- No new routes, controllers, or tests
- No changes to the API itself
- No design system changes

## Decisions

### SVG text elements over regenerated paths

**Decision:** Use SVG `<text>` elements for both "One Piece TCG" and "API" instead of converting font glyphs to vector paths.

**Rationale:** Generating accurate SVG paths from a font requires tooling (e.g., opentype.js, Inkscape). SVG `<text>` with `font-family="Instrument Sans"` (already loaded via Bunny Fonts) achieves the same visual result with far less complexity.

**Trade-off:** The fan-effect on "API" uses filled text layers instead of the original mask/stroke technique. The blend-mode stacking still creates an interesting layered effect — different from "13" but cohesive with the page style.

### "One Piece TCG" wordmark positioning

The original Laravel wordmark SVG uses `viewBox="0 0 438 104"`. A single centered `<text>` element at `x="219" y="78"` with `font-size="52"` and `font-weight="600"` fits cleanly within this viewport.

### "API" fan-effect structure

The original "13" SVG has five `<g>` elements, each rendering the same letter shapes at increasing x-offsets (0, 26, 51, 77, 102px) with different colors and blend modes. The same five-group structure is preserved with `<text>` elements. Font size ~220px, anchored at `x="30" y="320"` within `viewBox="0 0 440 392"`.

## Risks / Trade-offs

- **Font loading delay** → "One Piece TCG" text may flash in system font before Instrument Sans loads. Mitigation: `font-display: swap` is already handled by Bunny Fonts; acceptable for a landing page.
- **Text scaling on mobile** → SVG `<text>` scales with the viewBox; behavior matches the original. No additional concern.

## Open Questions

*(none)*
