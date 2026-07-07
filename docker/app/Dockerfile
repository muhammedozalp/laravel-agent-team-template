# PHP application image — multi-stage (Debian: Playwright browsers need glibc).
#   dev     -> php-fpm for the app/queue services
#   browser -> dev + Node 24 + Playwright system deps, for Pest browser tests
# Production build is a per-project decision, see context/guides/deploy.md.
FROM php:8.4-fpm AS base

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql intl zip bcmath opcache pcntl sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Match the host user so bind-mounted files keep sane ownership.
RUN groupadd -g "$GID" app || true \
    && useradd -m -u "$UID" -g "$GID" app || true

WORKDIR /var/www/html

FROM base AS dev
USER app

FROM base AS browser
# Node for Playwright; system browser deps baked at build (needs root).
# Browser binaries themselves install at runtime into the shared cache volume:
#   docker compose run --rm browser npx playwright install chromium firefox webkit
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && npx -y playwright@1 install-deps chromium firefox webkit \
    && rm -rf /var/lib/apt/lists/*
USER app
# Owned by app so the playwright-cache volume inherits writable ownership.
RUN mkdir -p /home/app/.cache
