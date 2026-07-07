# Production images — build context is the repo root:
#   docker build -f docker/prod/Dockerfile --target prod-app -t app .
#   docker build -f docker/prod/Dockerfile --target prod-web -t web .
# prod-app = php-fpm with the app baked in (no bind mounts, opcache locked).
# prod-web = Caddy serving public/ + fastcgi to prod-app (auto Let's Encrypt).
FROM php:8.4-fpm AS phpbase

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql intl zip bcmath opcache pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------- build stage
FROM phpbase AS build

RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .

# .dockerignore strips runtime dirs from the context — artisan needs them.
RUN mkdir -p storage/framework/cache/data storage/framework/sessions \
        storage/framework/testing storage/framework/views storage/logs \
        bootstrap/cache

# Composer scripts run (package:discover + filament:upgrade publish assets)…
RUN composer install --no-dev --prefer-dist --no-progress --optimize-autoloader

# …then the frontend build (Wayfinder shells out to artisan; throwaway key).
RUN cp .env.example .env \
    && php artisan key:generate \
    && npm ci \
    && npm run build \
    && rm -f .env \
    && rm -rf node_modules tests .git docker

# --------------------------------------------------------------- app runtime
FROM phpbase AS prod-app

COPY docker/prod/php.prod.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY --from=build --chown=www-data:www-data /app /var/www/html

WORKDIR /var/www/html
USER www-data

# Caches are (re)built at release time by scripts/deploy.sh — they depend on
# the real .env, which is not baked into the image.

# --------------------------------------------------------------- web runtime
FROM caddy:2-alpine AS prod-web

COPY docker/prod/Caddyfile /etc/caddy/Caddyfile
COPY --from=build /app/public /srv/public
