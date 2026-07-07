# 0011 — Developer-only launch & maintenance checklists in the admin panel

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

The reference project queued (but never built) a "checklists" initiative:
tiered go-live/ops checklists consolidating its research on a11y, SEO, deploy
safety, hosting/domain/email migration. Markdown checklists go stale and have
no per-project state; launches fail on exactly the forgotten items (Search
Console, SPF/DKIM/DMARC, restore-tested backups, registrar auto-renew).

## Decision

A **Filament page at `/admin/checklists`, visible only to the developer tier**:

- **`is_developer`** boolean (third boolean tier after `is_admin`; never
  mass-assignable; granted only via `app:make-admin --developer`). Client
  admins never see the page — `Checklists::canAccess()`.
- **Definitions in code, state in DB:** `config/checklists.php` defines 8
  grouped tabs (~53 items: Automated, Frontend, Backend & Security, Deploy &
  CI, Hosting & Domain, Email, SEO & Visibility, Legal, Client Handover —
  consolidated from the reference project's research). `checklist_items` rows
  hold per-project state (checked_at/by audit trail, probe results).
- **Auto items** are `Probe` classes (`app/Checklists/Probes/`) run by
  `ChecklistRunner` — from the page button and a **weekly schedule**; a
  previously-passing probe going red **notifies developer users by mail**
  (`ChecklistProbeFailed`). Manual items toggle with who/when recorded.

## Consequences

- Template updates add items to every project (config), without touching
  project state (DB). Keys are stable identifiers — never renamed casually.
- Probes must stay cheap and side-effect free; heavier scoring (Lighthouse) is
  a separate probe wired to the browser container (Phase 3).
- The uptime/SSL-expiry monitor stays EXTERNAL (Actions cron + ntfy, Phase 3) —
  an in-app probe can't report the app being down.
