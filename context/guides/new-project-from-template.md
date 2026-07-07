# Start a new project from this template

_The template → real-project bootstrap. Do these once, in order, in a fresh copy._

## 1. Copy & re-identify

```bash
cp -r project_template_with_two_agents/ <client>_<project>/
cd <client>_<project>/
rm -rf .git && git init -b main
```

- `composer.json` / `package.json`: set the real `name`.
- `.env.example`: set `APP_NAME`, `APP_HOST` + `APP_URL` (the local vhost, e.g.
  `client-site.local` / `https://client-site.local`); then `cp .env.example .env`.
- Add the vhost to `/etc/hosts` (see `docker.md` § Local virtual host).
- Delete `info.md` (template-creation brief — not part of projects).

## 2. Boot the stack

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan test        # green suite = healthy template
```

## 3. Install the AI tooling (once per project)

```bash
docker compose exec app php artisan boost:install               # Boost guidelines + skills
docker compose exec app php artisan boost:update --discover     # picks up Filament's AI guidelines
uv tool install "graphifyy[postgres]" && graphify install       # once per machine
# then in a Claude session:  /graphify .
```

Then create the first admin and check the panel:

```bash
docker compose exec app php artisan db:seed        # demo users incl. admin@example.com
# or promote a real account:
docker compose exec app php artisan app:make-admin you@example.com
# → https://<your-host>/admin  (set REQUIRE_ACCOUNT_APPROVAL in .env if wanted)
```

If Boost's MCP tools don't appear in Claude Code, see ADR 0005's note
(project-scope `.mcp.json` issue → register at user scope).

## 4. Make the docs true

- `context/project-overview.md`: replace "What it is" with the real product; adjust
  the stack table if the project deviates (each deviation = new ADR).
- `context/current-feature.md`: reset Active/History to the new project.
  (`feature/` and `fix/` ship empty — spec format examples: `context/templates/`.)
- `CLAUDE.md`: update the one-paragraph description at the top. Nothing else should
  need touching — that's the point of the thin router.
- Copy `context/agent_team/templates/board.template.md` → `agent_team/board.md`
  and `log.template.md` → `agent_team/log-dev.md` (both stay git-ignored).

## 5. First session ritual

- Launch **CEO** from the parent workspace; **Senior/Dev/Runner** from the repo
  root as needed (approve hooks via `/hooks` on each agent's first launch —
  project hooks are not auto-trusted). Small projects can start with CEO + Dev
  only and add the others later.
- CEO writes feature `01-…` from the client brief and routes it; work begins per
  `../ai-interaction.md`.

## 6. Remote & protection

```bash
gh repo create <org>/<project> --private --source . --push
```

Enable branch protection on `main` when the plan allows; add `Dependabot` alerts;
set the deploy secrets only when a deploy path is chosen (`deploy.md`).
