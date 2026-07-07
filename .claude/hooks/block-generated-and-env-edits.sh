#!/usr/bin/env bash
# PreToolUse guardrail (Write|Edit): BLOCK edits to generated dirs and secrets.
#   - vendor/, node_modules/          -> package-manager territory
#   - public/build/, storage/         -> generated / runtime
#   - .env, .env.*                    -> secrets ( .env.example|sample|template allowed )
#   - database/migrations/* on main-merged files is NOT checked here (append-only
#     rule is enforced by review + CI; hooks can't know merge state cheaply).
# Any other path passes through untouched.
set -euo pipefail

payload="$(cat)"
file_path="$(jq -r '.tool_input.file_path // empty' <<<"$payload")"
[ -z "$file_path" ] && exit 0

root="${CLAUDE_PROJECT_DIR:-$(jq -r '.cwd // empty' <<<"$payload")}"
root="${root:-$PWD}"

rel="${file_path#"$root"/}"

deny() {
  jq -n --arg reason "$1" '{
    hookSpecificOutput: {
      hookEventName: "PreToolUse",
      permissionDecision: "deny",
      permissionDecisionReason: $reason
    }
  }'
  exit 0
}

case "$rel" in
  # Committed templates are fine — including per-env ones (.env.production.example).
  .env.example|.env.sample|.env.template|.env.*.example) exit 0 ;;
  .env|.env.*)
    deny "Blocked: $rel holds secrets and is never edited by agents. Change .env.example (committed template) and ask the owner to update .env." ;;
  vendor/*|node_modules/*)
    deny "Blocked: $rel is package-manager output. Fix the dependency via composer/npm or change app code instead." ;;
  public/build/*)
    deny "Blocked: $rel is Vite build output. Edit resources/ and rebuild (docker compose exec node npm run build)." ;;
  storage/*)
    deny "Blocked: $rel is runtime storage (logs/cache/uploads). It is generated — nothing to edit there." ;;
esac

exit 0
