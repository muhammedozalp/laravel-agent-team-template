---
name: add-crud-module
description: Add a complete CRUD module (model, migration, factory, policy, form requests, controller, Inertia React pages, tests) to this Laravel app following the project conventions. Use when a task asks to add a new resource/entity/module (e.g. "add Products", "we need Testimonials CRUD").
---

# Add a CRUD module

End-to-end recipe for one new resource. Non-negotiables first (all from
`context/`): branch `iNNN/<slug>` → one PR (real projects); everything through
`docker compose exec`; migrations append-only; tests are part of the module, not
an extra; `.env*`/`vendor/`/`public/build/` and the generated
`resources/js/{actions,routes,wayfinder}/` never edited by hand.

## Inputs you need

- The spec (`context/feature/NN-<slug>.md`) — fields, relations, validation rules,
  who may do what (authorization), and which pages/routes the client actually needs.
  If any of these are missing: STOP and flag in your REPORT.

## Steps

1. **Schema question first, cheaply:** check the current schema via Boost's schema
   tool (or `/graphify`) before designing — reuse existing tables/conventions
   (`context/guides/database.md` owns naming: snake_case plural, timestampsTz,
   explicit FKs).
2. **Generate the skeleton** (one artisan call keeps names consistent):
   ```bash
   docker compose exec app php artisan make:model Thing -mfsc --policy --resource --requests --pest
   ```
   (migration, factory, seeder, resource controller, policy, Store/Update requests,
   Pest test.)
3. **Migration:** columns per spec + indexes for every column you will query.
   `down()` must work. Run `docker compose exec app php artisan migrate` and check
   `migrate:status`. Fill the **factory** now (realistic fakes + states) — TDD
   needs it before any behavior exists.
4. **TDD loop — one route/behavior at a time (ADR 0006).** For each action the
   spec needs (index, show, store, …):
   - **Red:** write the feature test first — for page routes assert the Inertia
     component + props (`AssertableInertia::component('things/index')` etc.);
     for mutations assert status/redirect + DB effect + authorization
     (guest / wrong user forbidden). Run it; it must fail because the behavior
     is missing.
   - **Green:** implement the minimum: route (`Route::resource(..., only: [...])`),
     thin controller returning `Inertia::render('things/index', [...])`, model
     bits it needs (`$fillable`/`#[Fillable]`, casts, relations, scopes — no query
     logic in controllers), policy method (`$this->authorize(...)`), validation
     rules in the Store/Update Form Request.
   - **Refactor:** extract Actions/scopes, dedupe, rerun suite.
5. **Pages (React):** `resources/js/pages/things/{index,show,form}.tsx` using the
   existing layout + shadcn/ui components; URLs via **Wayfinder** imports, never
   hand-written strings; add TS types to `resources/js/types/`. Run
   `npm run lint:check && npm run types:check`. (Pure markup is a declared TDD
   exemption; behavior in components is not.)
6. **Seeder:** add to `DatabaseSeeder` so the local site stays demo-ready.
7. **Gate:** run the full pre-merge gate (`context/ai-interaction.md`); add a
   browser/smoke test only if the spec marks this flow as critical (a11y assert
   on new user-facing pages).

## Definition of done

Gate green (pint, phpstan, eslint, tsc, full pest suite, vite build) · every route
tested incl. authorization · seeder produces visible demo data · spec Steps ticked +
Resolution drafted · `context/current-feature.md` history line added · one PR.
