# Coding standards

_Canonical conventions. `CLAUDE.md` links here; hooks in `.claude/hooks/` enforce the
hard rules automatically._

## Naming

- **Folders `under_scores`** (non-Laravel dirs), **doc files `kebab-case.md`**.
  Laravel's own conventions win inside the skeleton (StudlyCase classes,
  `snake_case` DB columns/tables, plural table names, `camelCase` methods).
- Feature/fix specs: `NN-slug.md` — the filename is a **stable name for life**;
  status lives in the file's `Status:` line, never in the filename.
- Branches: `iNNN/<slug>` where `NNN` is the task number from the board
  (e.g. `i012/user-registration`). Milestone tags only (`v1.2.0`).
- Migrations: framework timestamp prefix + descriptive snake_case
  (`2026_07_07_000000_create_orders_table.php`).

## Environment & secrets

- **`.env` is never committed** — only `.env.example` (kept complete: every variable
  the app reads appears there with a safe default or empty value).
- **Never edit** `vendor/`, `node_modules/`, `public/build/`, `storage/` (generated
  or runtime), or `.env*` (secrets). A guardrail hook blocks these edits; the fix
  belongs in `app/`, `resources/`, `config/`, or `.env.example`.
- Config access goes through `config/*.php` files — **never `env()` outside
  `config/`** (breaks config caching).

## Code style & static analysis

- **Pint** (`./vendor/bin/pint`) is the formatter — run before every commit; CI
  checks with `--test`. No style debates: Pint's `laravel` preset decides.
- **Larastan** at level 7 (the kit's default; raise per project, never lower) — `./vendor/bin/phpstan analyse`.
  New code must pass; suppressions need an inline reason.
- Controllers thin, validation in Form Requests, business logic in
  `app/Actions/`/`app/Services/`, N+1s prevented with eager loading (see
  `guides/database.md`).

## Frontend (React + TypeScript)

- Inertia pages in `resources/js/pages/` (kebab-case files, default-export
  components); shared UI from `resources/js/components/` (shadcn/ui primitives in
  `components/ui/` are vendored — extend, don't fork). Tailwind utilities over
  custom CSS.
- **ESLint + Prettier + tsc are part of the gate:** `npm run lint:check`,
  `format:check`, `types:check` (configs ship in the repo — no style debates).
- Routes/URLs in TS come from **Wayfinder** imports — never hand-write URL
  strings; `resources/js/{actions,routes,wayfinder}/` are generated, never edited.
- Assets only via Vite — never hand-edit `public/build/`.
- Accessibility is tested (axe in browser tests): no positive `tabIndex`, links
  need accessible names, meet WCAG AA contrast.

## Files & encoding

- LF line endings everywhere (`.gitattributes` enforces `* text=auto eol=lf`).
- `.editorconfig` is authoritative for indentation (4 spaces PHP, 2 spaces
  JS/CSS/YAML/Blade).

## Versioning & docs hygiene

- SemVer; keep `CHANGELOG.md` per release once the project ships.
- **Every fact has one home** — docs link to the owner doc instead of restating.
  A behavior change updates the affected doc **in the same PR**
  (`ai-interaction.md` owns the workflow rule).
