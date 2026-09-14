# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# 1. Frontend-assets bouwen
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

# app/ hoort hierbij: tailwind.config.js scant './app/**/*.php' mee, dus
# zonder die map worden klassen die in PHP staan uit de build gefilterd.
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY app ./app

RUN npm run build

# ---------------------------------------------------------------------------
# 2. PHP-dependencies installeren
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
# Geen --prefer-dist: dist is toch al de standaard, maar de vlag expliciet
# meegeven zet de terugval op git-source uit. Precies die terugval wil je
# als GitHub even een 504 teruggeeft.
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-interaction

# ---------------------------------------------------------------------------
# 3. Runtime
# ---------------------------------------------------------------------------
FROM php:8.4-fpm-alpine AS runtime

# pdo_sqlite en sqlite3 zitten al in het officiële php-image.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions opcache pcntl \
 && apk add --no-cache nginx supervisor \
 && rm -f /etc/nginx/http.d/default.conf

COPY docker/php.ini          /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/www.conf         /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx.conf       /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor       ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# /var/lib/nginx staat erbij omdat nginx zijn workers als www-data draait en
# daar zijn tijdelijke bestanden wegschrijft.
RUN mkdir -p database/sqlite \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
 && chown -R www-data:www-data storage bootstrap/cache database /var/lib/nginx

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD wget -qO- http://127.0.0.1/up >/dev/null 2>&1 || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
