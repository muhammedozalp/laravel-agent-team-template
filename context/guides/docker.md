# Docker environment

_Everything runs in containers (ADR `../decisions/0001-docker-native-dev.md`). No
PHP, Composer, Node, or psql on the host — ever._

## Services (`docker-compose.yml`)

| Service | Image / build | Purpose | Host port |
|---|---|---|---|
| `app` | `docker/app/Dockerfile` (php:8.4-fpm **Debian** — Playwright needs glibc; + pdo_pgsql, redis, intl, sockets, …) | PHP-FPM + artisan/composer entry point | — |
| `web` | nginx alpine (`docker/nginx/templates/`) | serves the app: HTTPS vhost + plain HTTP | **443**, 8080 |
| `db` | postgres:17-alpine | databases `app` + `app_testing` | 54320 |
| `redis` | redis:7-alpine | cache, session, queue | — |
| `node` | app image `browser` target (**PHP + Node** — the Wayfinder Vite plugin shells out to `php artisan`) | `npm install` + Vite dev server | 5173 |
| `mailpit` | axllent/mailpit | catches all outbound mail (UI) | 8025 |
| `queue` | app image, `artisan queue:listen` | queue worker — **always on** (`QUEUE_CONNECTION=redis` would otherwise swallow jobs silently in dev) | — |
| `scheduler` | app image, `artisan schedule:work` | runs scheduled tasks — **always on** | — |
| `browser` | app image `browser` target (+ Node 24 + Playwright deps) | Pest browser tests (e2e/smoke/a11y, cross-browser) — **profile `testing`** | — |

## Daily commands

```bash
docker compose up -d                    # start (first run builds + npm installs)
docker compose logs -f queue scheduler  # watch jobs + scheduled tasks
docker compose exec app php artisan …   # any artisan command
docker compose exec app composer …      # any composer command
docker compose exec node npm …          # any npm command
docker compose exec db psql -U app app  # psql into the dev DB
docker compose logs -f app              # tail PHP logs
docker compose down                     # stop (data volumes survive)
docker compose down -v                  # stop AND WIPE databases — ask the owner first

# Browser tests (profile `testing`) — browsers install once into a cache volume:
docker compose run --rm browser npx playwright install chromium firefox webkit
docker compose run --rm browser php artisan test --testsuite=Browser
```

App: <https://examplesite.local> (or <http://localhost:8080>) ·
Mailpit: <http://localhost:8025> · Vite HMR: :5173.

## Local virtual host (HTTPS)

`APP_HOST` in `.env` (default `examplesite.local`) drives an nginx HTTPS vhost that
answers for the host **and every subdomain** (`staging.examplesite.local`, …).

1. One-time, on the host: `echo "127.0.0.1 examplesite.local staging.examplesite.local" | sudo tee -a /etc/hosts`
2. `docker compose up -d` — first start auto-generates a **self-signed** cert
   (browser warning is expected) into `docker/nginx/certs/` (git-ignored).
3. Optional, trusted green-lock cert via [mkcert](https://github.com/FiloSottile/mkcert):
   ```bash
   mkcert -install
   mkcert -cert-file docker/nginx/certs/examplesite.local.pem \
          -key-file  docker/nginx/certs/examplesite.local-key.pem \
          examplesite.local "*.examplesite.local"
   docker compose restart web
   ```
   The certs dir may be root-owned (created by the container) — `sudo chown
   -R $USER:$USER docker/nginx/certs` first if mkcert gets "permission
   denied". If the browser still shows "Not secure" afterwards, **trust
   DevTools (F12 → Security tab), not the badge**: with the Vite dev server
   running, `public/hot` makes the page load assets over plain HTTP (real
   mixed content); with it stopped and cert/TLS green, a stale badge is
   usually browser state or an extension injecting content — verify in a
   Guest profile. Production is unaffected either way (real CA certs).

Renaming the host for a real project = change `APP_HOST`/`APP_URL` in `.env`, redo
the `/etc/hosts` line, delete `docker/nginx/certs/*.pem`, restart `web`. Containers
resolve the vhost names internally via compose network aliases (so browser tests can
target `E2E_BASE_URL=https://examplesite.local` too — note the self-signed warning
caveat in `testing.md`).

## First run on a fresh clone

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

(Scripted, with the AI-tooling installs, in `new-project-from-template.md`.)

## Gotchas

- The `db` container creates `app_testing` via `docker/postgres/init-testing-db.sh`
  **only on first volume init** — after `down -v`, it recreates automatically; if you
  added the script to an existing volume, run it manually.
- File permissions: the `app` image runs as UID 1000 to match a default Linux user;
  adjust `docker/app/Dockerfile` args if your host UID differs.
- Vite in Docker binds `--host 0.0.0.0`; if HMR doesn't connect, check port 5173
  isn't taken on the host.
