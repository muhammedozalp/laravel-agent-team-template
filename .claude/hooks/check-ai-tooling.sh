#!/usr/bin/env bash
# SessionStart: warn (never block) when the per-project/per-machine AI tooling
# from context/guides/claude-tooling.md is missing — the pieces that can't ship
# in the repo and are easy to forget on a fresh clone or new machine.
set -euo pipefail

payload="$(cat)"
root="${CLAUDE_PROJECT_DIR:-$(jq -r '.cwd // empty' <<<"$payload")}"
root="${root:-$PWD}"

missing=()

# Graphify: per-machine binary + per-project graph
command -v graphify >/dev/null 2>&1 || [ -x "$HOME/.local/bin/graphify" ] \
    || missing+=("graphify not installed (uv tool install \"graphifyy[postgres]\" && graphify install)")
[ -f "$root/graphify-out/graph.json" ] \
    || missing+=("knowledge graph not built (run /graphify . — rebuild after merges)")

# Boost: per-project skills/guidelines (boost:install --guidelines --skills)
[ -d "$root/.claude/skills/laravel-best-practices" ] \
    || missing+=("Boost skills missing (docker compose exec app php artisan boost:install --guidelines --skills)")

if [ "${#missing[@]}" -gt 0 ]; then
    jq -n --arg msg "AI tooling incomplete (context/guides/claude-tooling.md): $(printf '%s; ' "${missing[@]}")" \
        '{systemMessage: $msg}'
fi

exit 0
