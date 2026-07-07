# 0004 — Pest testing pyramid; Pint + Larastan + Pest as the merge gate

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

The template promises "multiple type testing structures with best practices."
Laravel ships PHPUnit by default; Pest is the modern Laravel-community standard with
a friendlier syntax, architecture testing, and (since v4) first-class browser tests.

## Decision

Four layers, cheapest first (details: `../guides/testing.md`):

1. **Static** — Pint (`laravel` preset) + Larastan level 6.
2. **Unit** — `tests/Unit`, pure logic, no framework boot where possible.
3. **Feature** — `tests/Feature`, HTTP + DB against Postgres (`RefreshDatabase`).
4. **Browser** — `tests/Browser`, Pest v4 browser testing (Playwright-based,
   dedicated `browser` Docker service): e2e flows, a **smoke** group, accessibility
   checks, cross-browser (Chromium/Firefox/WebKit) and mobile-device emulation.
   The same smoke group runs against staging/production via `E2E_BASE_URL`.

Plus **arch tests** (`tests/Arch.php`, pest-plugin-arch) encoding conventions as
executable rules (e.g. no `env()` outside `config/`, no `dd()`/`dump()` committed).
All of static+unit+feature block merges (CI + pre-merge gate); browser tests run on
PRs touching user-facing flows and stay non-blocking until flake-free.

## Consequences

- Conventions are enforced by tests, not review comments — cheaper for an AI team.
- Browser layer needs Playwright in the `node` container / CI image.
- Contributors write Pest, not PHPUnit, style; the two coexist if needed.

## Amendment (2026-07-07, with the ADR 0007 stack pivot)

Facts that changed after acceptance (kept here rather than silently editing the
decision): Larastan runs at **level 7** (the React kit's default — floor raised,
never lowered); arch rules live in **`tests/Unit/ArchTest.php`**; Playwright
lives in the dedicated **`browser`** service image (not `node`); the frontend
gate (ESLint/Prettier/tsc) and **HTML validation** (`npm run html:check`,
html-validate against rendered pages) joined the static layer.
