#!/usr/bin/env bash
# PreToolUse guardrail (Write|Edit on database/migrations/*): WARN (never block)
# when the migration file already exists on main — merged migrations are
# append-only (context/guides/database.md); write a NEW migration instead.
# New (uncommitted / branch-only) migration files pass silently.
set -euo pipefail

payload="$(cat)"
file_path="$(jq -r '.tool_input.file_path // empty' <<<"$payload")"
[ -z "$file_path" ] && exit 0

root="${CLAUDE_PROJECT_DIR:-$(jq -r '.cwd // empty' <<<"$payload")}"
root="${root:-$PWD}"
rel="${file_path#"$root"/}"

case "$rel" in
  database/migrations/*) ;;
  *) exit 0 ;;
esac

# On main already? Then editing it rewrites shared history.
if git -C "$root" cat-file -e "main:$rel" 2>/dev/null; then
  jq -n --arg f "$rel" '{
    systemMessage: ("WARNING: \($f) is already merged to main. Migrations are append-only (context/guides/database.md) — write a NEW migration instead of editing this one. (Warning only; the edit is NOT blocked.)")
  }'
fi

exit 0
