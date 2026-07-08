# Launch & maintenance checklists (`/admin/checklists`)

_Developer-only Filament page (ADR 0011): grouped/tabbed go-live and ops
checklists with per-project state. Client admins cannot see it._

## Using it

- **Access:** `php artisan app:make-admin you@example.com --developer` (the
  seeded `admin@example.com` is a developer locally).
- **Tabs** = groups from `config/checklists.php`; each shows `checked/total`.
- **Manual items:** click the circle — who/when is recorded. Click again to
  uncheck.
- **Automated items** (badge `auto`): run via the **Run automated checks**
  button, and **weekly (Mon 06:00)** via the scheduler. Green shows the probe
  detail; red shows what to fix. A probe that WAS passing and starts failing
  emails every developer (`ChecklistProbeFailed`). CLI:
  `php artisan app:run-checklist-probes`.

## Extending per project

- **Add items:** append to a group in `config/checklists.php` — `key` (stable,
  never rename), `label`, optional `description`. State rows appear lazily.
- **Add a probe:** implement `App\Checklists\Probe` in `app/Checklists/Probes/`
  (cheap + side-effect free), reference it from the item's `probe` key. The
  definitions test (`ChecklistsTest`) verifies every probe class resolves.
- **Remove groups** that don't apply to a project rather than leaving them
  forever-unchecked — an unfinishable checklist teaches people to ignore it.

## Shipped probes

health (`/up`) · robots-per-environment · sitemap non-empty · security headers ·
HTTPS APP_URL · debug-off-in-production · Sentry DSN · real-mailer-in-production ·
Lighthouse report freshness + scores.

## Lighthouse (Phase 3)

`docker compose run --rm browser npm run lighthouse` audits the app
(`LIGHTHOUSE_URL` overrides the target — point it at production before
launch). Playwright launches its Chromium; the Lighthouse CLI attaches over
the CDP port and writes `storage/app/private/lighthouse.json`. The
`auto.lighthouse` probe then checks the report is fresher than
`checklists.lighthouse.max_age_days` (30) and every category score ≥
`min_score` (80). Gotcha (cost us a night): while the Vite dev server runs,
`public/hot` points every asset at it — unreachable from the audit browser, so
nothing paints and Lighthouse dies with **NO_FCP**. The script moves the hot
file aside for the audit and restores it. Locally expect a low SEO score (the
non-production robots response correctly blocks indexing) and a throttled
performance score — judge both against production.

## Uptime + SSL expiry (Phase 3)

`.github/workflows/uptime.yml` checks `/up` and certificate expiry every 30
minutes **from outside the server** (an in-app probe can't report the app
being down). No-ops until you set repo variable `MONITOR_URL`; pushes alerts
to `ntfy.sh/<NTFY_TOPIC secret>` — subscribe to that topic in the ntfy app.
