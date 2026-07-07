#!/usr/bin/env bash
# HTML validation of rendered pages (offline W3C-style checks via html-validate).
#   docker compose exec node npm run html:check
# Add routes as the project grows; point HTML_CHECK_BASE at a deployed URL to
# validate staging/production instead (like E2E_BASE_URL for browser tests).
set -euo pipefail

BASE="${HTML_CHECK_BASE:-http://web}"
ROUTES=(/ /login /register)

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT

for route in "${ROUTES[@]}"; do
    name="$(echo "${route#/}" | tr '/' '_')"
    name="${name:-home}"
    echo "fetching $BASE$route"
    curl -fsSL "$BASE$route" -o "$tmp/$name.html" || { echo "FAILED to fetch $route" >&2; exit 1; }
done

# Explicit config: html-validate resolves config relative to the validated
# files (which live in /tmp), not the cwd.
npx html-validate --config .htmlvalidate.json "$tmp"/*.html
echo "HTML OK: ${ROUTES[*]}"
