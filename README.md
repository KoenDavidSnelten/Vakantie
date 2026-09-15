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

Er draaien twee containers:

| Container | Doet |
| --- | --- |
| `app` | de site zelf: nginx en php-fpm samen, onder supervisord |
| `caddy` | handelt https af, haalt het certificaat op en zet alles door naar `app` |

Alleen `caddy` hangt aan een poort van de Pi. De app is van buitenaf niet
rechtstreeks te bereiken, ook niet vanaf je eigen netwerk.

**Over poort 80.** De provider blokkeert inkomend verkeer op poort 80. De
gebruikelijke manier om een certificaat op te halen (http-01) loopt daarover en
kan dus niet. In plaats daarvan gebruiken we **tls-alpn-01**: Let's Encrypt
bewijst het eigendom van het domein via een speciale TLS-handdruk op poort 443.
Verder is daar niets bijzonders voor nodig behalve dat poort 443 openstaat.

Daar is Caddy voor gekozen en niet nginx: Caddy doet die handdruk zelf, op de
poort waar hij toch al luistert. Er is dus geen aparte certbot-container, geen
cronjob, geen verlengscript, en de site gaat bij het verlengen niet even uit.
Certbot kan tls-alpn-01 niet — dat kent alleen http-01 en dns-01.

De nginx in de `app`-container blijft gewoon staan; die serveert Laravel uit en
staat los van dit alles.

Het image wordt op de Pi zelf gebouwd, dus er is geen registry nodig en geen
gedoe met architecturen.

Het SQLite-bestand staat in het volume `vakantie-db`. Verder schrijft de app
niets naar schijf: avatars worden uit de initialen gegenereerd en een
pistekaart is een link, geen upload. De certificaten staan in `caddy-data`.

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
   - `ACME_CA` — laat op de echte server staan; zie stap 7 voor de testserver

5. **DNS:** maak bij je domeinregistrar een A-record dat je domeinnaam naar het
   publieke IP van je aansluiting wijst. Controleer dat het klopt:

   ```bash
   getent hosts jouwdomein.nl   # het IP hier moet gelijk zijn aan:
   curl -s ifconfig.me
   ```

   (`getent` en niet `dig`: dnsutils staat niet op Raspberry Pi OS Lite.)

   Geven die twee verschillende antwoorden, wacht dan tot de DNS bijgewerkt is
   (kan een uur duren). Blijft `curl ifconfig.me` afwijken van het IP op de
   statuspagina van je router, dan zit je achter CGNAT en werkt dit hele
   recept niet.

6. **Router:** geef de Pi een vast LAN-adres (een DHCP-reservering, niet het
   adres dat hij nu toevallig heeft) en zet poort **443** door naar de Pi,
   extern 443 naar intern 443, TCP.

   Zet daarnaast onder *Onderhoud → Extern beheer* de **WAN**-kolom uit voor
   HTTPS. Staat die aan, dan houdt de router poort 443 voor zijn eigen
   inlogpagina en komt je doorverwijzing er nooit doorheen. Bovendien staat je
   routerbeheer dan open voor het hele internet.

   Poort 80 doorzetten heeft geen zin zolang de provider hem blokkeert.

7. **Eerst een testcertificaat.** Zet in `.env.proxy` de testserver aan:

   ```
   # ACME_CA=https://acme-v02.api.letsencrypt.org/directory
   ACME_CA=https://acme-staging-v02.api.letsencrypt.org/directory
   ```

   Let's Encrypt staat maar een handvol mislukte aanvragen per uur toe, en de
   fouten die je op dit punt maakt — DNS nog niet doorgekomen, poort 443 dicht —
   kosten je anders in één keer je hele budget. De testserver heeft die limiet
   niet.

   ```bash
   docker compose up -d --build
   docker compose logs -f caddy
   ```

   In de logs wil je `certificate obtained successfully` zien. De browser gaat
   klagen dat het certificaat niet vertrouwd wordt — **dat hoort zo**, en het is
   precies het bewijs dat de rest werkt.

8. **Dan het echte certificaat.** Draai `ACME_CA` in `.env.proxy` weer om, en
   gooi het testcertificaat weg — anders blijft Caddy dat gebruiken:

   ```bash
   docker compose down
   docker volume rm vakantie_caddy-data
   docker compose up -d --build
   ```

   Verder is er niets in te stellen. Caddy verlengt vanzelf, ruim voor de
   vervaldatum, zonder de site te onderbreken. Geen cronjob nodig.

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
docker compose logs -f caddy                      # verkeer, en het certificaat
docker compose exec app php artisan tinker        # tinker in de container
docker compose ps                                 # status en healthcheck
```

---

## Over het certificaat

Een certificaat van Let's Encrypt is negentig dagen geldig. Caddy houdt dat zelf
bij en verlengt ruim op tijd, zonder de site te onderbreken en zonder dat er een
cronjob aan te pas komt. Er is niets aan te onderhouden.

De certificaten staan in het volume `caddy-data`. **Gooi dat niet weg** tenzij
je een testcertificaat kwijt wil: bij elke verse start vraagt Caddy anders een
nieuw certificaat aan, en Let's Encrypt heeft daar een weeklimiet op.

Zien wat Caddy met het certificaat doet:

```bash
docker compose logs caddy | grep -i -E "certificate|acme|obtain"
```

Controleren wat er nu daadwerkelijk uitgeserveerd wordt, inclusief de
vervaldatum:

```bash
echo | openssl s_client -connect jouwdomein.nl:443 -servername jouwdomein.nl 2>/dev/null \
  | openssl x509 -noout -issuer -dates
```

Staat er `CN=...STAGING...` bij de issuer, dan draai je nog op het
testcertificaat en moet `ACME_CA` in `.env.proxy` terug naar de echte server.

### Als er iets misgaat

**Caddy blijft het certificaat opnieuw proberen.** Let's Encrypt komt niet bij
poort 443. Controleer de doorverwijzing in de router (extern 443 → intern 443),
en of de **WAN**-kolom bij HTTPS onder *Onderhoud → Extern beheer* uitstaat —
anders houdt de router poort 443 voor zijn eigen inlogpagina.

Test altijd van buiten je eigen netwerk, met mobiel internet en wifi uit. Van
binnenuit beantwoordt de router je vaak zelf en lijkt alles in orde.

Let op het verschil tussen de twee foutmeldingen: een **time-out** betekent dat
je pakketjes ergens stilzwijgend weggegooid worden (een blokkade), terwijl
**connection refused** betekent dat ze wél aankomen maar er niets luistert. Dat
laatste is een doorverwijzing die niet klopt; het eerste zit hoger in de keten.

**`unauthorized` of het verkeerde IP.** Het A-record wijst ergens anders heen.
Vergelijk `getent hosts jouwdomein.nl` met `curl -s ifconfig.me` op de Pi.

**502 Bad Gateway.** Caddy draait, de app niet. Kijk met
`docker compose ps` en `docker compose logs app`.

**De site laadt zonder opmaak.** Dan wordt er `http://` in de HTML gezet en
blokkeert de browser de stylesheets als mixed content. Controleer of
`APP_URL` in `.env.docker` met `https://` begint en of `trustProxies` in
[`bootstrap/app.php`](bootstrap/app.php) staat. Na een wijziging in
`.env.docker` is `docker compose up -d --force-recreate app` nodig: de
configuratie wordt bij het opstarten gecachet.

**Je komt niet voorbij het inlogscherm.** `SESSION_SECURE_COOKIE=true` terwijl
je de site over `http://` benadert. Gebruik `https://`.

**Een tweede domeinnaam erbij, bijvoorbeeld `www`.** Zet hem in
[`docker/Caddyfile`](docker/Caddyfile) achter de eerste, gescheiden door een
komma: `{$DOMAIN}, www.jouwdomein.nl {`. Caddy vraagt er dan vanzelf een
certificaat voor aan. Zorg wel eerst dat er een A-record voor bestaat.

### Terug naar http, tijdelijk

Draai je de app even zonder Caddy ervoor, zet dan `SESSION_SECURE_COOKIE=false`
in `.env.docker` en haal `trustProxies` weg. Die middleware-regel hoort er
alleen in zolang er echt een proxy voor staat: op een container die zelf aan
poort 80 hangt zou hij iedereen toestaan `X-Forwarded-For` te vervalsen en zo
zijn IP-adres te verbergen.
