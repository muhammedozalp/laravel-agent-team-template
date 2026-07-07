# Testing strategy

_Four layers, cheapest first (ADR `../decisions/0004-testing-pyramid.md`). The
pre-merge gate (`../ai-interaction.md`) runs static + unit + feature; browser tests
run when a task touches user-facing flows. **All production code is written
test-first** (TDD — ADR `../decisions/0006-tdd-workflow.md`)._

## TDD — how we write code (red → green → refactor)

1. **Red:** take the next behavior from the spec's Steps and write the smallest
   test that names it (usually a Feature test: route → status + effect). Run it —
   it must fail, and fail **for the expected reason** (a missing route failing on
   404 is red; failing on a typo in the test is not).
2. **Green:** write the minimum production code to pass. Resist implementing ahead
   of the tests.
3. **Refactor:** with the suite green, clean up (naming, extraction to
   Actions/scopes, dedupe) — no behavior change, suite stays green. Then back to 1.

Working agreements:

- One red test at a time; commits land at green (a commit with a failing suite
  never pushes).
- The test asserts behavior (status, DB row, mail queued), not implementation
  details — TDD'd tests must survive the refactor step by definition.
- Exempt (declare in the REPORT): pure config/wiring, no-behavior Blade/styling,
  generated scaffolding, spikes (whose code is re-driven by tests before the PR).
- Bug fix = the strictest TDD: the failing regression test IS the reproduction
  (`../templates/fix-spec.md`).

## Layers

| Layer | Where | Runs against | Command |
|---|---|---|---|
| Static — style | whole repo | — | `docker compose exec app ./vendor/bin/pint --test` |
| Static — analysis | `app/`, `bootstrap/app.php`, `config/`, `database/`, `routes/` (not `tests/` — Pest `$this` inference) | `phpstan.neon` (Larastan, **level 7**) | `docker compose exec app ./vendor/bin/phpstan analyse` |
| Arch rules | `tests/Unit/ArchTest.php` | conventions as code | included in `artisan test` |
| Unit | `tests/Unit/` | pure logic | `docker compose exec app php artisan test --testsuite=Unit` |
| Feature | `tests/Feature/` | HTTP + **Postgres** (`app_testing`, `RefreshDatabase`) | `docker compose exec app php artisan test --testsuite=Feature` |
| Browser | `tests/Browser/` | Pest v4 browser tests (Playwright, dedicated `browser` service) | `docker compose run --rm browser php artisan test --testsuite=Browser` |

Full suite: `docker compose exec app php artisan test` (add `--parallel` when it
grows slow).

## Browser layer — e2e, smoke, a11y, cross-browser, cross-device, multi-env

One-time setup (browsers cache in a Docker volume) + build assets — browser tests
run against **production-built assets**, never the Vite dev server (see
`tests/Pest.php`), so build after frontend changes before running the suite:

```bash
docker compose run --rm browser npx playwright install chromium firefox webkit
docker compose exec node npm run build
```

| Need | How | Example |
|---|---|---|
| **E2E flows** | plain browser tests in `tests/Browser/` | `visit(e2e_url('/checkout'))->click(...)` |
| **Smoke suite** | tag critical-path tests `->group('smoke')` | `--group=smoke` (see `SmokeTest.php`; `assertNoSmoke()` = no console logs / JS errors) |
| **Accessibility** | `assertNoAccessibilityIssues(level: 1)` (0 critical … 3 minor) | `SmokeTest.php`, grouped `a11y` |
| **Cross-browser** | same tests, `--browser` flag: `chrome` (default) / `firefox` / `safari` (WebKit) | `docker compose run --rm browser ./vendor/bin/pest --testsuite=Browser --browser firefox` |
| **Cross-device / mobile** | device emulation: `->on()->mobile()`, `->iPhone15Pro()`, `->pixel8()`, `->iPadPro()`, … | `ResponsiveTest.php` |
| **Staging / production** | `E2E_BASE_URL=https://staging.example.com` + the smoke group | `E2E_BASE_URL=… docker compose run --rm -e E2E_BASE_URL browser php artisan test --testsuite=Browser --group=smoke` |

Notes:

- With `E2E_BASE_URL` empty (default) tests run the local app self-contained
  (Pest boots its own server against the test DB). With it set, tests hit the real
  deployed environment — **smoke group only**: never run destructive/DB-refreshing
  tests against staging/production, and use a trusted cert (mkcert) if targeting
  the local `https://…local` vhost, since self-signed certs fail the browser.
- Keep the matrix small on purpose: every page × browser × device combination
  multiplies runtime — cover the money flows, not the sitemap.
- Assertions that read computed styles (axe contrast scans) race CSS entrance
  animations — wrap the page in `freeze_motion(visit(...))` (helper in
  `tests/Pest.php`) before asserting.

## Rules of the pyramid

- **Every bug fix starts with a failing regression test** (see
  `../templates/fix-spec.md`).
- Feature tests are the workhorse: one per route/behavior, assert status + effect
  (DB row, mail queued, event fired) — not implementation details. For Inertia
  pages assert the component + props (`AssertableInertia`), not HTML.
- The starter kit's shipped tests are PHPUnit-style classes — they run unchanged
  under the Pest runner. Write NEW tests in Pest style; both coexist.
- Unit tests only for logic that deserves isolation (money, dates, rules) — don't
  unit-test what a feature test already covers.
- Browser tests are expensive: **critical flows only** (signup, checkout, the thing
  the client sells). Non-blocking in CI until flake-free.
- Arch tests encode conventions (`tests/Arch.php`): no `dd()`/`dump()`/`ray()`
  committed, no `env()` outside `config/`, Actions are invokable, etc. Extend it
  when a review comment repeats — turn the comment into a rule.

## Factories & seeders

- Every model gets a factory; states for meaningful variants
  (`Order::factory()->paid()`).
- `DatabaseSeeder` must always produce a demo-ready local site; seeders are dev/CI
  tooling, never run in production deploys.

## Postgres specifics

Tests hit real Postgres (ADR 0002): JSONB columns, constraints, and raw expressions
are testable — write tests that would catch a SQLite/Postgres divergence rather than
avoiding the features (details: `database.md`).
