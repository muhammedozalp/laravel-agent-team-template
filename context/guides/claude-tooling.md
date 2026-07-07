# Claude Code tooling — what ships, what to install, what to skip

_The template's AI-tooling stack (researched 2026-07). Repo-level pieces work for
every contributor automatically; per-machine pieces are one-time installs._

## Ships in the repo (nothing to do)

| Piece | Where | What it gives agents |
|---|---|---|
| Skills: `add-crud-module`, `new-task`, `session-handoff` | `.claude/skills/` | repeatable recipes, loaded only when triggered |
| Guardrail hooks (block generated/env edits; warn on main-commit, merged-migration edits) | `.claude/hooks/` | hard rails; see its README |
| Permissions allowlist | `.claude/settings.json` | no prompts for the standard docker/git/gh commands |
| Plugins auto-enabled project-wide | `.claude/settings.json` → `enabledPlugins` | `frontend-design` (public React site only — **not** the Filament panel), `playwright` (interactive browser driving for e2e debugging), `laravel` (Laravel's official plugin: `laravel-simplifier` agent + `starter-kit-upgrade` skill) |
| MCP servers | `.mcp.json` | **laravel-boost** (dockerized `php artisan boost:mcp`) + **context7** (needs `CONTEXT7_API_KEY` env for >1k req/mo) |

## Once per project (bootstrap — see `new-project-from-template.md`)

```bash
docker compose exec app php artisan boost:install     # guidelines + skills for the detected stack
docker compose exec app php artisan boost:update --discover   # re-run after adding packages (picks up Filament's own AI guidelines)
```

Boost is the highest-value tool here: schema/routes/logs/tinker via MCP + a
hosted **Search Docs** tool covering Laravel 10–13, Filament 2–5, Livewire,
Inertia, Pest, Tailwind — version-pinned to what's installed. Keep Boost's
generated guideline files out of the hand-written `CLAUDE.md` router (they live
in `.ai/`/`AGENTS.md`; gitignore regenerated output per Laravel docs).

## Once per machine

```bash
uv tool install "graphifyy[postgres]" && graphify install   # knowledge graph (token policy)
```

## Deliberately skipped (documented so nobody re-litigates)

- **Postgres MCP** — Boost's Database Query/Schema tools already query through
  the app's own connection inside Docker. If a raw SQL channel is ever needed:
  `crystaldba/postgres-mcp` with `--access-mode=restricted`.
- **Filament docs MCPs / community Filament agents** — redundant next to Boost's
  Search Docs + Context7.
- **code-review plugin** — Claude Code's built-in `/code-review` covers it.
- **php-lsp plugin** — optional; add per machine if you want code intelligence.
- **Filament Blueprint (paid)** — noted as the "if you buy one thing" option:
  planning guidelines + a `filament-security-audit` skill.
