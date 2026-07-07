# `php artisan app:doctor` bootstrap check

**Idea:** one command validating the fresh-clone invariants with green/red
output: /etc/hosts entry for `APP_HOST`, TLS cert present, `app_testing` DB
exists, `APP_KEY` set, queue driver vs. worker running, Playwright browsers
installed. Turns `../guides/new-project-from-template.md`'s manual checklist into
something agents can self-serve.

**Why parked:** pure DX; build after the checklist has burned someone twice.
