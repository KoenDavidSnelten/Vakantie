#!/bin/sh
set -e

DB_FILE="${DB_DATABASE:-/var/www/html/database/sqlite/database.sqlite}"
DB_DIR="$(dirname "$DB_FILE")"

# Bij de allereerste start is het volume leeg. SQLite heeft niet alleen
# schrijfrechten op het bestand nodig maar ook op de map eromheen, voor
# zijn -wal en -journal bestanden.
mkdir -p "$DB_DIR"
[ -f "$DB_FILE" ] || touch "$DB_FILE"
chown -R www-data:www-data "$DB_DIR"

php artisan migrate --force

# Cachen op runtime en niet tijdens de build: de configuratie hangt af van
# de omgeving waarin de container draait, niet van die waarin het image
# gebouwd is.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# De artisan-commando's hierboven draaien als root; php-fpm draait als
# www-data en moet de weggeschreven caches kunnen lezen en vervangen.
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
