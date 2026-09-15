#!/bin/sh
# Verlengt het certificaat. Hoort op een cronjob te staan; zie de README.
#
# Certbot controleert zelf of er iets te doen valt en stopt meteen als het
# certificaat nog meer dan dertig dagen geldig is. Dit script mag dus gerust
# elke nacht draaien.
#
# De proxy gaat er even af omdat certbot poort 443 nodig heeft voor de
# tls-alpn-01 controle. Dat kost een seconde of tien, en alleen 's nachts.
set -eu

# Absoluut maken: cron start zonder bruikbare werkmap.
cd "$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

. ./.env.proxy

echo "=== $(date '+%Y-%m-%d %H:%M:%S') verlenging gestart ==="

docker compose stop proxy

# Wat er hierna ook misgaat, de site moet weer in de lucht komen. `up -d` en
# niet `start`, zodat de container ook opnieuw wordt aangemaakt als hij
# tussendoor verdwenen is.
trap 'docker compose up -d proxy' EXIT INT TERM

docker compose run --rm -p 443:443 --entrypoint certbot certbot renew --quiet

echo "=== klaar ==="
