# Creating a project template using another project running with agents

_The original owner brief this template was built from, lightly redacted for
publication (client names and local paths removed). Delete this file when
bootstrapping a real project (`context/guides/new-project-from-template.md`)._

## The reference project

The owner has a production static-website project (Pug + Webpack, deployed to
shared hosting) developed by a **4-agent Claude Code team**:

1. CEO (manager agent)
2. Senior (developer agent)
3. Dev (mid-level developer agent)
4. Runner (junior developer agent)

The owner talks with the CEO. The CEO creates tasks and assigns them to the most
suitable agent according to the complexity of the task.

The reference project has two main folders:

- `_content/` — project-related text files, media, template files, hosting info,
  email info etc. Not tracked by git.
- the repo folder — the real production project, tracked with git.

Its important pieces: a thin-router `CLAUDE.md`, a `context/` docs folder, a
`context/agent_team/` coordination folder, and a research catalog of Claude Code
resources (skills, hooks, MCP servers, CI tooling).

## What was asked

Analyze that project carefully and create:

- similar `context/` folder content
- an agent-team system
- git tracking
- runs inside Docker native engine
- multiple types of testing structures with best practices
- full-stack website using the Laravel framework
- PostgreSQL database
- skills, MCP servers, scripts (like hooks) for web development, design,
  front-end, back-end, database (especially PostgreSQL), testing, Laravel, CI/CD
- other MCPs like Context7
- **Token optimization / session / context management as a first-class concern**:
  project cost is one of the most important subjects in the AI development age.
  Research the best alternatives (e.g. graphify-style knowledge graphs) and
  implement a systematic approach.

The owner uses this as the starting template for new full-stack Laravel website
projects.

---

## Addendum — decisions & features agreed during creation (2026-07-07)

Decisions made after the original brief above, in agreement with the owner
(rationale lives in `context/decisions/`; this list keeps the brief complete):

1. **"Graphify" resolved + token stack chosen (ADR 0005):** graphify = the
   knowledge-graph skill (code + PostgreSQL schema as a queryable graph). Adopted
   stack: native context discipline (thin-router CLAUDE.md, `context/` docs,
   handoff files) + **Laravel Boost MCP** + **Graphify**. Context7 ships in
   `.mcp.json` (graduated at the React pivot); Graphiti/Mem0 deferred.
2. **Agent team:** launched as 2-agent Lead+Dev (ADR 0003), later expanded to the
   full **4-agent CEO → Senior/Dev/Runner** model from the reference project
   (ADR 0008); files-as-mailbox coordination kept.
3. **Local HTTPS virtual host:** `https://examplesite.local` + wildcard
   subdomains (`staging.examplesite.local`) via nginx, auto self-signed certs,
   mkcert upgrade path. `APP_HOST` drives it.
4. **Testing expanded (ADR 0004):** e2e, smoke group, accessibility (axe),
   cross-browser (Chrome/Firefox/WebKit), cross-device/mobile emulation, and
   multi-environment runs (`E2E_BASE_URL` → staging/production smoke) — Pest v4
   browser layer in a dedicated Docker service. Tests run against real
   PostgreSQL, not SQLite (ADR 0002).
5. **TDD is the default workflow (ADR 0006):** red → green → refactor for all
   production code; exemptions declared in reports; the CEO rejects
   implementation without a driving test.
6. **Auth:** first built Breeze-style (Blade), then **superseded by the official
   React starter kit** — see 7.
7. **Stack pivot (ADR 0007):** the template is built on the official **React
   starter kit** — React 19 + Inertia 2 + TypeScript + shadcn/ui, auth via
   **Fortify** (email verification enforced; passkeys/2FA-ready; hardened per
   `context/guides/auth.md`). Chosen over the Livewire kit and over keeping
   Breeze, to align with Laravel's official direction.
8. **Deploy target: VPS — shipped:** production images (php-fpm + Caddy
   auto-TLS), GHCR→SSH pipeline, nightly DB backups, encrypted-env flow
   (`context/guides/deploy.md` is the runbook).
9. **Template stays pristine:** creation work lands directly on `main`, no
   feature/fix spec files for the template's own work (the spec system +
   branch→PR→review lifecycle are for real projects started from it).
