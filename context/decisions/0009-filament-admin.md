# 0009 — Filament v5 is the standard admin panel

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

Nearly every client project needs an admin area with a dashboard. Hand-building
admin CRUD in the template's React stack is the most token-expensive work an AI
team does, and it re-litigates solved problems (tables, filters, forms,
notifications). The owner decided admin capability belongs IN the template, not
per-project. Research (2026-07) confirmed Filament v5 (Livewire 4, Tailwind-4
native) supports Laravel 13, ships precompiled assets (no Vite/React conflict —
the panel lives entirely at `/admin`), and both Filament and Laravel Boost ship
first-party AI guidelines for it.

## Decision

**Filament v5 (`filament/filament: ^5.0`)** owns `/admin`; Inertia/React owns the
public site. Wiring (details: `../guides/auth.md` § Admin panel):

- Single `users` table. `User implements FilamentUser`;
  `canAccessPanel() = is_admin && verified`. `is_admin` is **never**
  mass-assignable and only granted via `php artisan app:make-admin <email>`.
- Panel uses the same `web` guard/session as the public site. Because Filament's
  `/admin/login` bypasses Fortify's 2FA, the panel gets **Filament's own MFA**
  (`AppAuthentication`, independent columns — the two flows never collide).
- Ships one resource: **Users** (list, approve, delete — list-only by design;
  registration happens on the public site).
- **Approval gate** (`REQUIRE_ACCOUNT_APPROVAL`, default off): registrations wait
  at an approval notice until approved from the panel; approving emails the user.
- Admin CRUD is tested with Livewire Pest helpers (`tests/Feature/Admin/`) — the
  TDD rule (ADR 0006) fully applies to panel work.

## Consequences

- Every project starts with working user management; new admin screens are a
  `make:filament-resource` + tests away — minutes and few tokens, not days.
- The repo carries Livewire/Alpine alongside React — two frontend paradigms.
  Accepted: the panel is prebuilt UI nobody restyles (do NOT point the
  frontend-design plugin at it), and admins tolerate a distinct backend look.
- `filament:upgrade` runs on composer's `post-autoload-dump` — deploy pipelines
  must run composer scripts or panel assets go stale.
- Custom panel themes (rarely needed) would add a Vite entry — compatible with
  the Tailwind-4 setup, documented per project if wanted.
