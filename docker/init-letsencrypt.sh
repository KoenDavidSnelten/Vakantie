#!/bin/sh
# Eenmalig uitvoeren op de Pi, bij het allereerste opzetten van https.
# Daarna verlengt de certbot-container het certificaat vanzelf en hoef je dit
# script nooit meer aan te raken.
#
# Waarom dit nodig is: nginx weigert te starten als het certificaat waar zijn
# config naar wijst niet bestaat, en certbot kan geen certificaat aanvragen
# zolang nginx niet draait om de challenge uit te serveren. Dit script breekt
# die knoop door eerst een wegwerpcertificaat te maken.
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
# maar je weet wel of je DNS en poortdoorverwijzing kloppen.
STAGING=""
if [ "${1:-}" = "--staging" ]; then
    STAGING="--staging"
    echo "== Testmodus: het certificaat wordt straks niet vertrouwd =="
fi

LIVE="/etc/letsencrypt/live/$DOMAIN"

# Stap 3 hieronder gooit alles weg wat er voor dit domein staat. Dat is precies
# de bedoeling bij een eerste keer, maar het zou een werkend certificaat
# vernietigen als je dit script per ongeluk nog eens draait.
if docker compose run --rm --entrypoint sh certbot \
       -c "[ -f '/etc/letsencrypt/renewal/$DOMAIN.conf' ]" >/dev/null 2>&1; then
    echo "Er staat al een certificaat voor $DOMAIN." >&2
    echo "Dit script is alleen voor de eerste keer; verlengen gaat vanzelf." >&2
    echo "Nakijken met:" >&2
    echo "  docker compose run --rm --entrypoint certbot certbot certificates" >&2
    echo "Wil je het echt opnieuw aanvragen, verwijder dan eerst met:" >&2
    echo "  docker compose run --rm --entrypoint certbot certbot delete --cert-name $DOMAIN" >&2
    exit 1
fi

echo "== 1/5 Wegwerpcertificaat maken zodat nginx kan starten =="
docker compose run --rm --entrypoint sh certbot -c "
    mkdir -p '$LIVE'
    openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
        -keyout '$LIVE/privkey.pem' \
        -out '$LIVE/fullchain.pem' \
        -subj '/CN=$DOMAIN'
"

echo "== 2/5 Proxy starten =="
docker compose up -d proxy

# nginx heeft het wegwerpcertificaat inmiddels ingelezen en blijft draaien,
# ook nu de bestanden verdwijnen. Weghalen moet, anders ziet certbot er een
# bestaande uitgifte in en weigert hij een nieuwe aan te maken.
echo "== 3/5 Wegwerpcertificaat opruimen =="
docker compose run --rm --entrypoint sh certbot -c "
    rm -rf '$LIVE' \
           '/etc/letsencrypt/archive/$DOMAIN' \
           '/etc/letsencrypt/renewal/$DOMAIN.conf'
"

echo "== 4/5 Echt certificaat aanvragen =="
docker compose run --rm --entrypoint certbot certbot certonly \
    --webroot -w /var/www/certbot \
    $STAGING \
    -d "$DOMAIN" \
    --email "$LETSENCRYPT_EMAIL" \
    --agree-tos \
    --no-eff-email \
    --non-interactive

echo "== 5/5 Alles starten en nginx het nieuwe certificaat laten oppakken =="
docker compose up -d
docker compose exec proxy nginx -s reload

echo
echo "Klaar. Controleren met:"
echo "  curl -I https://$DOMAIN"
