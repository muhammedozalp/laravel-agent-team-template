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
HTTPS APP_URL · debug-off-in-production · Sentry DSN · real-mailer-in-production.
Lighthouse scoring + external uptime/expiry monitoring: Phase 3 (this guide is
updated when they land).
