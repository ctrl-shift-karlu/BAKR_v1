---
name: awesome-design-md
description: Library of 84 reference DESIGN.md design systems (Stripe, Linear, Vercel, Notion, Apple, Airbnb, etc.) from VoltAgent/awesome-design-md. Use when creating or refining BAKR's own DESIGN.md, choosing a visual direction, or when the user asks to make a page look like a known brand's design language.
---

# Awesome DESIGN.md library

Source: https://github.com/VoltAgent/awesome-design-md (MIT, see LICENSE). Snapshot taken 2026-11-09.

Each folder in `designs/<site>/DESIGN.md` describes one product's design system in plain markdown: colours, typography, spacing, components, motion and tone.

## How to use

1. List the options by running Glob on `.claude/skills/awesome-design-md/designs/*/DESIGN.md`.
2. Read only the 1–3 files that fit the request. Each file is large, so don't load them all.
3. Use them as **reference**, not as a copy. BAKR is a DNSC student scholarship site, so adapt the structure (token naming, component rules, motion rules) to BAKR's existing palette and fonts (Poppins + Source Serif 4, Bootstrap 5, `public/css/bakr-core.css`).
4. Write the result to `DESIGN.md` at the project root, the conventional location agents look for.

## Good starting references for BAKR

- `stripe`, `wise`, `mercury`: trustworthy, finance/forms-heavy UI
- `notion`, `linear.app`: calm information density, dashboards
- `airbnb`, `intercom`: friendly, approachable consumer tone

Never reproduce another brand's logo, name or trademarked assets.
