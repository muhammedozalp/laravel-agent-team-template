# Deploy — VPS runbook & CI/CD

_The template's deploy path is a **single VPS running the production Docker
stack** (owner decision 2026-07-07; Caddy chosen over nginx-in-prod for
automatic Let's Encrypt). Pieces: `docker/prod/Dockerfile` (images),
`docker-compose.prod.yml` (server stack), `scripts/deploy.sh` (release),
`.github/workflows/deploy.yml` (pipeline), `.env.production.example` (env
template)._

## CI (`.github/workflows/ci.yml`) — runs on every PR

1. **Style** — `pint --test` (blocking)
2. **Static** — `phpstan analyse`, Larastan level 7 (blocking)
3. **Frontend** — ESLint + Prettier check + `tsc --noEmit` (blocking)
4. **Tests** — Unit+Feature Pest suites against a `postgres:17` service, config
   identical to local `app_testing` (blocking)
5. **Assets** — `npm ci && npm run build` incl. Wayfinder generation (blocking)
6. **Browser** — Playwright e2e/smoke/a11y (non-blocking until flake-free, ADR 0004)

Also wired: **Dependabot**, **Gitleaks**, and the `main-ci-gate` repo ruleset
requiring all blocking checks on PRs.

## Architecture in one line

GitHub Actions builds two images to GHCR (`-app` = php-fpm with code baked in,
`-web` = Caddy + `public/`), then SSHes to the VPS where `scripts/deploy.sh`
pulls, backs up the DB, migrates, caches, and health-checks `/up`. Caddy
provisions TLS automatically for `APP_DOMAIN`.

## Zero → production (once per project)

1. **VPS** (Hetzner/DO class, Ubuntu LTS): install Docker Engine + compose
   plugin; create a deploy user in the `docker` group; SSH key-only auth;
   enable ufw (22/80/443) + unattended-upgrades.
2. **DNS:** `A` record for `example.com` (and `staging.` if used) → VPS IP.
   Caddy needs the record live before first start to get certificates.
3. **App dir on the server** (`/srv/app`):
   `docker-compose.prod.yml`, `scripts/deploy.sh`, `.env.production.encrypted`
   (all from the repo — a shallow clone of the repo is the easy way).
4. **Production env** (locally):
   ```bash
   cp .env.production.example .env.production      # gitignored
   # fill real values (APP_KEY via: php artisan key:generate --show)
   docker compose exec app php artisan env:encrypt --env=production
   git add .env.production.encrypted && git commit
   ```
   The printed encryption key goes to your **password manager** AND the
   `LARAVEL_ENV_ENCRYPTION_KEY` GitHub secret. Plaintext `.env.production`
   never leaves your machine; the encrypted file in git IS the backup.
5. **GitHub secrets** (repo → Settings → Secrets):

   | Secret | Value |
   |---|---|
   | `DEPLOY_SSH_HOST` | VPS IP/hostname — **the deploy no-ops until this exists** |
   | `DEPLOY_SSH_USER` | deploy user |
   | `DEPLOY_SSH_KEY` | private key for that user |
   | `DEPLOY_APP_DIR` | optional, default `/srv/app` |
   | `LARAVEL_ENV_ENCRYPTION_KEY` | from step 4 |
   | `APP_DOMAIN` | e.g. `example.com` |

6. **First release:** run the Deploy workflow manually with `dry_run=false`
   (or push to `main`). Then create the first admin:
   `docker compose -f docker-compose.prod.yml exec app php artisan app:make-admin you@example.com`.

## Every release after that

Merge to `main` → CI gates → Deploy builds images → `scripts/deploy.sh` on the
server: pull → **pg_dump backup** → `migrate --force` → config/route/view/event
caches → `queue:restart` → `/up` health check (fails the workflow if unhealthy).

Post-deploy smoke from your machine (or add it to the workflow when flake-free):

```bash
E2E_BASE_URL=https://example.com docker compose run --rm -e E2E_BASE_URL browser \
  php artisan test --testsuite=Browser --group=smoke
```

## Backups

- **Automatic:** the `backups` service pg_dumps nightly with rotation
  (7 daily / 4 weekly / 6 monthly) into the `db-backups` volume, plus one dump
  **before every migrate** (deploy.sh).
- **Off-site (do this — a volume on the same disk is not a backup):** cron an
  rclone/rsync of `/var/lib/docker/volumes/app-prod_db-backups` to object
  storage or another host.
- **Restore:** see `database.md` § Operations.

## Rollback

Images are tagged by commit SHA:

```bash
export APP_IMAGE=ghcr.io/<owner>/<repo>-app:<previous-sha>
export WEB_IMAGE=ghcr.io/<owner>/<repo>-web:<previous-sha>
docker compose -f docker-compose.prod.yml up -d
```

Migrations are append-only (ADR/database.md), so old code usually runs on the
new schema. If a migration itself must be undone: restore the pre-migrate dump
taken by deploy.sh, then roll the images back.

## Secrets inventory (where things live — never in git)

| Secret | Home |
|---|---|
| App runtime env (DB/mail/Sentry/APP_KEY) | `.env.production.encrypted` in git; key in password manager + GH secret |
| VPS SSH key, hosting panel, registrar, SMTP account | password manager (+ per-client `_content/` folder, untracked) |
| CI-time values | GitHub Actions secrets (table above) |

## Release checklist (deploy.sh automates the starred items)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, real `APP_KEY`,
      `SESSION_SECURE_COOKIE=true`, `LOG_LEVEL=warning`
- [ ] `SENTRY_DSN` set (error monitoring is inert without it)
- [ ] ★ backup before `migrate --force`
- [ ] ★ config/route/view/event caches rebuilt; `storage:link`; `queue:restart`
- [ ] ★ `/up` healthy
- [ ] Post-deploy: smoke suite against the live URL (above)
- [ ] Post-deploy: rebuild the Graphify graph (`../token-optimization.md`)
