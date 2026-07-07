# Deploy & CI/CD

_CI is fully wired; **deployment is a per-project decision** — this guide gives the
CI contract and the two recommended deploy paths. When a project picks one, replace
the "Deploy options" section with the real runbook (the reference project's
`deploy.md` shows the level of detail to aim for)._

## CI (`.github/workflows/ci.yml`) — runs on every PR

1. **Style** — `pint --test` (blocking)
2. **Static** — `phpstan analyse`, Larastan level 7 (blocking)
3. **Frontend** — ESLint + Prettier check + `tsc --noEmit` (blocking)
4. **Tests** — Unit+Feature Pest suites against a `postgres:17` service, config
   identical to local `app_testing` (blocking)
5. **Assets** — `npm ci && npm run build` incl. Wayfinder generation (blocking —
   a broken Vite build must not reach `main`)
6. **Browser** — Playwright e2e/smoke/a11y (non-blocking until flake-free, ADR 0004)

Also wired: **Dependabot** (weekly composer + npm + actions bumps) and **Gitleaks**
(secret scanning on every push — critical before a repo ever goes public).

Branch rule: PRs merge only when CI is green (enable branch protection when the
GitHub plan allows; until then the rule is enforced by the team charter).

## Deploy options (pick per project, then document the choice in an ADR)

**Option A — VPS + Docker (recommended default).** The dev compose file has a
production sibling: build the `app` image with `--target` optimizations, run
`php artisan migrate --force` on release, nginx in front, Postgres either as a
container with volumes+backups or a managed instance. Works on Hetzner/DO; pairs
well with Coolify or plain `docker compose` + a GitHub Actions SSH deploy step.

**Option B — Managed PHP platform** (Forge/Vapor/Ploi): fastest to production,
platform handles TLS/queues/scheduler; costs monthly and constrains the stack.

Shared-hosting FTP (the reference project's method) is **not** suitable here — a
Laravel app needs a process manager, writable `storage/`, and migrations on release.

## Release checklist (any option)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, real `APP_KEY`,
      `SESSION_SECURE_COOKIE=true`, `LOG_LEVEL=warning`
- [ ] `SENTRY_DSN` set (error monitoring is inert without it)
- [ ] `php artisan config:cache route:cache view:cache event:cache`
- [ ] `php artisan migrate --force` gated on backup taken
- [ ] Queue worker + scheduler (`schedule:run` cron or `schedule:work`) supervised
- [ ] `storage/` writable, `public/storage` link, logs shipped somewhere durable
- [ ] Post-deploy: run the smoke suite against the deployed URL
      (`E2E_BASE_URL=https://<env> docker compose run --rm -e E2E_BASE_URL browser \
       php artisan test --testsuite=Browser --group=smoke` — `testing.md`)
- [ ] Post-deploy: rebuild the Graphify graph (`../token-optimization.md`)
