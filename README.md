# Vakantie

Planner voor een wintersportvakantie met een vaste groep vrienden: data prikken,
skigebieden en hotels verzamelen en erop stemmen, vervoer en auto's indelen, en
een gezamenlijke paklijst bijhouden. Plus wat drankspellen voor onderweg.

Laravel 12 · SQLite · Tailwind + Alpine via Vite · Nederlandstalig.

---

## Lokaal draaien

Vereist PHP 8.2+, Composer en Node 22+.

```bash
composer setup     # dependencies, .env, APP_KEY, migraties, npm build
composer dev:win   # Windows: php artisan serve + queue listener
composer dev       # macOS/Linux: idem, plus vite en pail
```

In een tweede terminal, voor hot reloading van de frontend:

```bash
npm run dev
```

Open je de dev-server vanaf een ander apparaat in je netwerk? Zet dan
`VITE_HMR_HOST` op het LAN-adres van je werkstation.

Tests:

```bash
composer test
```

---

## Draaien op een Raspberry Pi met Docker

Er draaien drie containers:

| Container | Doet |
| --- | --- |
| `app` | de site zelf: nginx en php-fpm samen, onder supervisord |
| `proxy` | handelt https af en zet alles door naar `app` |
| `certbot` | haalt het certificaat op en verlengt het elke paar maanden |

Alleen `proxy` hangt aan een poort van de Pi. De app is van buitenaf niet
rechtstreeks te bereiken, ook niet vanaf je eigen netwerk.

Het image wordt op de Pi zelf gebouwd, dus er is geen registry nodig en geen
gedoe met architecturen.

Het SQLite-bestand staat in het volume `vakantie-db`. Verder schrijft de app
niets naar schijf: avatars worden uit de initialen gegenereerd en een
pistekaart is een link, geen upload. De certificaten staan in `certbot-conf`.

### Eenmalig instellen

1. **Controleer dat de Pi 64-bits draait.** `uname -m` moet `aarch64` geven.
   Zo niet, herinstalleer met 64-bits Raspberry Pi OS Lite — de images die we
   gebruiken bestaan niet voor 32-bits.

2. **Draai bij voorkeur vanaf SSD of USB-stick.** SQLite plus Docker schrijft
   genoeg om een SD-kaart binnen afzienbare tijd op te maken.

3. **Docker installeren:**

   ```bash
   curl -fsSL https://get.docker.com | sh
   sudo usermod -aG docker $USER
   # uitloggen en opnieuw inloggen
   ```

4. **Project ophalen en configureren:**

   ```bash
   sudo mkdir -p /srv/vakantie && sudo chown $USER /srv/vakantie
   git clone <repo-url> /srv/vakantie
   cd /srv/vakantie

   cp .env.docker.example .env.docker
   cp .env.proxy.example  .env.proxy
   chmod 600 .env.docker .env.proxy
   ```

   Vul in `.env.docker` minstens in:

   - `APP_KEY` — genereer met
     `docker compose run --rm --entrypoint php app artisan key:generate --show --no-ansi`
     (`--no-ansi` is nodig, anders zitten er kleurcodes in de sleutel)
   - `REGISTRATION_CODE` — zonder deze code kan iedereen die `/register` vindt een account maken
   - `APP_URL` — `https://` plus je domeinnaam

   En in `.env.proxy`:

   - `DOMAIN` — dezelfde domeinnaam, maar kaal: zonder `https://` en zonder slash
   - `LETSENCRYPT_EMAIL` — hier komt de waarschuwing binnen als een verlenging mislukt

5. **DNS:** maak bij je domeinregistrar een A-record dat je domeinnaam naar het
   publieke IP van je aansluiting wijst. Controleer dat het klopt:

   ```bash
   dig +short jouwdomein.nl     # moet gelijk zijn aan:
   curl -s ifconfig.me
   ```

   Geven die twee verschillende antwoorden, wacht dan tot de DNS bijgewerkt is
   (kan een uur duren). Blijft `curl ifconfig.me` afwijken van het IP op de
   statuspagina van je router, dan zit je achter CGNAT en werkt dit hele
   recept niet — dan is een Cloudflare Tunnel de weg.

6. **Router:** geef de Pi een vast LAN-adres en zet poort **80 én 443** door
   naar de Pi. Poort 80 moet open blijven: certbot verlengt het certificaat
   daarover, en zonder die poort verloopt het na negentig dagen.

7. **Starten en het certificaat ophalen:**

   ```bash
   ./docker/init-letsencrypt.sh --staging   # eerst met een testcertificaat
   ```

   Doe deze stap altijd eerst met `--staging`. Let's Encrypt staat maar vijf
   mislukte aanvragen per uur toe, en de fouten die je op dit punt maakt — DNS
   nog niet doorgekomen, poort 80 dicht — kosten je anders in één keer je
   hele budget. De browser zal klagen over het certificaat, dat hoort.

   Gaat het goed, dan het echte werk:

   ```bash
   ./docker/init-letsencrypt.sh
   docker compose up -d --build
   ```

   Vanaf nu verlengt de certbot-container vanzelf. Dit script draai je nooit
   meer.

### Een wijziging uitrollen

Op je werkstation:

```bash
git add -A
git commit -m "Beschrijf wat je veranderd hebt"
git push
```

Op de Pi:

```bash
cd /srv/vakantie && git pull && docker compose up -d --build
```

Migraties draaien automatisch bij het opstarten van de container, dus een
aparte stap daarvoor is er niet.

`--build` is altijd nodig, ook voor een wijziging van één regel Blade: de
code zit in het image gebakken en wordt niet vanaf schijf ingeladen. Docker
hergebruikt wel zijn cache, dus zolang `composer.json` en `package.json`
ongemoeid blijven duurt het minder dan een minuut.

Controleren of het goed ging:

```bash
docker compose ps          # STATUS terug op "healthy"
docker compose logs -f app # meekijken, Ctrl+C om te stoppen
```

### Terugdraaien na een mislukte uitrol

```bash
cd /srv/vakantie
git log --oneline -5       # zoek de commit vóór de kapotte
git checkout <hash>
docker compose up -d --build
```

Terug naar de laatste versie met `git checkout master && git pull`.

Let op bij migraties: de code terugdraaien draait een migratie die al
gelopen heeft *niet* terug. Zat er een migratie in de uitrol, maak dan
eerst een back-up (zie hieronder).

### Bestaande data meenemen

Een verse start begint met een lege database. Om je huidige `database.sqlite`
over te zetten:

```bash
docker compose cp database/database.sqlite app:/var/www/html/database/sqlite/
docker compose restart app
```

### Back-ups

Het volume `vakantie-db` is de volledige staat van de app. Gebruik
`VACUUM INTO` en geen gewone `cp`: een kopie kan midden in een schrijfactie
vallen en levert dan een kapot bestand op.

```bash
docker compose exec -T app php -r '
  $db = new SQLite3(getenv("DB_DATABASE"));
  $stmt = $db->prepare("VACUUM INTO ?");
  $stmt->bindValue(1, "/tmp/backup.sqlite");
  $stmt->execute();
'
docker compose cp app:/tmp/backup.sqlite ./vakantie-$(date +%F).sqlite
docker compose exec -T app rm /tmp/backup.sqlite
```

Controleren of een back-up heel is:

```bash
sqlite3 vakantie-2026-09-14.sqlite "pragma integrity_check;"
```

### Handige commando's

```bash
docker compose logs -f app                        # logs van de app volgen
docker compose logs -f proxy                      # verkeer dat binnenkomt
docker compose logs certbot                       # verlengingen
docker compose exec app php artisan tinker        # tinker in de container
docker compose ps                                 # status en healthcheck
```

---

## Over het certificaat

Een certificaat van Let's Encrypt is negentig dagen geldig. De
certbot-container probeert twee keer per dag te verlengen en doet niets
zolang er nog meer dan dertig dagen over zijn. De proxy herlaadt zichzelf elke
zes uur, zodat een vers certificaat vanzelf in gebruik genomen wordt. Er is
dus geen cronjob en geen onderhoud.

Controleren hoe lang je certificaat nog geldig is:

```bash
docker compose run --rm --entrypoint certbot certbot certificates
```

Een verlenging droogzwemmen zonder er een echte aanvraag aan te wagen:

```bash
docker compose run --rm --entrypoint certbot certbot renew --dry-run
```

### Als er iets misgaat

**`Timeout during connect` bij het aanvragen.** Let's Encrypt kan poort 80 niet
bereiken. Test vanaf een verbinding buiten je huis — mobiel internet met wifi
uit — met `curl -I http://jouwdomein.nl`. Meestal is het de poortdoorverwijzing
op de router, soms blokkeert de provider poort 80.

**`unauthorized` of het verkeerde IP.** Het A-record wijst ergens anders heen.
Vergelijk `dig +short jouwdomein.nl` met `curl -s ifconfig.me` op de Pi.

**502 Bad Gateway.** De proxy draait, de app niet. Kijk met
`docker compose ps` en `docker compose logs app`.

**De site laadt zonder opmaak.** Dan wordt er `http://` in de HTML gezet en
blokkeert de browser de stylesheets als mixed content. Controleer of
`APP_URL` in `.env.docker` met `https://` begint en of `trustProxies` in
[`bootstrap/app.php`](bootstrap/app.php) staat. Na een wijziging in
`.env.docker` is `docker compose up -d --force-recreate app` nodig: de
configuratie wordt bij het opstarten gecachet.

**Je komt niet voorbij het inlogscherm.** `SESSION_SECURE_COOKIE=true` terwijl
je de site over `http://` benadert. Gebruik `https://`.

**Een tweede domeinnaam erbij, bijvoorbeeld `www`.** Zet hem achter
`server_name` in [`docker/proxy.conf.template`](docker/proxy.conf.template) en
vraag het certificaat opnieuw aan met een extra `-d www.jouwdomein.nl`.

### Terug naar http, tijdelijk

Draai je de app even zonder proxy, zet dan `SESSION_SECURE_COOKIE=false` in
`.env.docker` en haal `trustProxies` weg. Die middleware-regel hoort er alleen
in zolang er echt een proxy voor staat: op een container die zelf aan poort 80
hangt zou hij iedereen toestaan `X-Forwarded-For` te vervalsen en zo zijn
IP-adres te verbergen.
