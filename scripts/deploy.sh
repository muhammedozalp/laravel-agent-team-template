#!/usr/bin/env bash
# Release script — runs ON THE VPS from the app directory (e.g. /srv/app).
# Called by .github/workflows/deploy.yml over SSH; safe to run by hand.
# Expects: docker-compose.prod.yml, scripts/, .env.production.encrypted, and
# LARAVEL_ENV_ENCRYPTION_KEY exported (deploy.md § Secrets).
set -euo pipefail

COMPOSE="docker compose -f docker-compose.prod.yml"

echo "==> Pulling images"
$COMPOSE pull --quiet

echo "==> Decrypting environment"
# env:decrypt needs the encrypted file next to a Laravel app — the app image has one.
docker run --rm \
    -v "$PWD/.env.production.encrypted:/var/www/html/.env.production.encrypted:ro" \
    -e LARAVEL_ENV_ENCRYPTION_KEY="${LARAVEL_ENV_ENCRYPTION_KEY:?export LARAVEL_ENV_ENCRYPTION_KEY first}" \
    "${APP_IMAGE:?export APP_IMAGE (ghcr.io/...-app:TAG)}" \
    sh -c 'php artisan env:decrypt --env=production --force >/dev/null && cat .env.production' > .env
chmod 600 .env

echo "==> Backup before migrate"
$COMPOSE up -d db
$COMPOSE run --rm backups /backup.sh

echo "==> Starting stack"
$COMPOSE up -d --remove-orphans

echo "==> Migrations + caches"
$COMPOSE exec -T app php artisan migrate --force
$COMPOSE exec -T app php artisan config:cache
$COMPOSE exec -T app php artisan route:cache
$COMPOSE exec -T app php artisan view:cache
$COMPOSE exec -T app php artisan event:cache
$COMPOSE exec -T app php artisan storage:link || true
$COMPOSE exec -T app php artisan queue:restart

echo "==> Health check"
for i in $(seq 1 20); do
    if curl -fsS "https://${APP_DOMAIN:?export APP_DOMAIN}/up" >/dev/null 2>&1; then
        echo "OK: https://${APP_DOMAIN}/up"
        exit 0
    fi
    sleep 3
done

echo "FAILED: /up never came healthy — check: $COMPOSE logs app web" >&2
exit 1
