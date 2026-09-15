#!/bin/sh
# Eenmalig uitvoeren op de Pi, bij het allereerste opzetten van https.
# Daarna verlengt docker/renew-cert.sh het certificaat via een cronjob.
#
# We gebruiken de tls-alpn-01 controle en niet de gebruikelijke http-01: die
# laatste loopt over poort 80 en die wordt door de provider geblokkeerd.
# tls-alpn-01 doet alles over poort 443, met een speciale TLS-handdruk.
#
# Certbot moet poort 443 daarvoor zelf in handen hebben, dus de proxy gaat er
# even af. Bij deze eerste keer draait die toch nog niet.
#
#   ./docker/init-letsencrypt.sh --staging    # eerst testen
#   ./docker/init-letsencrypt.sh              # daarna echt
set -eu

cd "$(dirname "$0")/.."

if [ ! -f .env.proxy ]; then
    echo "Er is geen .env.proxy. Maak hem eerst aan:" >&2
    echo "  cp .env.proxy.example .env.proxy" >&2
    exit 1
fi

. ./.env.proxy

: "${DOMAIN:?DOMAIN staat niet in .env.proxy}"
: "${LETSENCRYPT_EMAIL:?LETSENCRYPT_EMAIL staat niet in .env.proxy}"

# Let's Encrypt staat maar vijf mislukte aanvragen per uur toe per domein.
# Draai daarom eerst met --staging: dat gebruikt een testserver zonder die
# limiet. Het certificaat dat je dan krijgt vertrouwt geen enkele browser,
# maar je weet wel of je DNS en poort 443 kloppen.
STAGING=""
if [ "${1:-}" = "--staging" ]; then
    STAGING="--staging"
    echo "== Testmodus: het certificaat wordt straks niet vertrouwd =="
fi

if docker compose run --rm --entrypoint sh certbot \
       -c "[ -f '/etc/letsencrypt/renewal/$DOMAIN.conf' ]" >/dev/null 2>&1; then
    echo "Er staat al een certificaat voor $DOMAIN." >&2
    echo "Dit script is alleen voor de eerste keer; verlengen doet de cronjob." >&2
    echo "Nakijken met:" >&2
    echo "  docker compose run --rm --entrypoint certbot certbot certificates" >&2
    exit 1
fi

echo "== 1/3 Proxy stoppen zodat certbot poort 443 kan gebruiken =="
docker compose stop proxy 2>/dev/null || true

echo "== 2/3 Certificaat aanvragen via tls-alpn-01 =="
docker compose run --rm -p 443:443 --entrypoint certbot certbot certonly \
    --standalone \
    --preferred-challenges tls-alpn-01 \
    $STAGING \
    -d "$DOMAIN" \
    --email "$LETSENCRYPT_EMAIL" \
    --agree-tos \
    --no-eff-email \
    --non-interactive

echo "== 3/3 Alles starten =="
docker compose up -d

echo
echo "Klaar. Controleren met:"
echo "  curl -I https://$DOMAIN"
echo
echo "Vergeet de cronjob voor het verlengen niet; zie de README."
