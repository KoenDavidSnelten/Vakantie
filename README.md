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

De app draait als één container: nginx en php-fpm samen, onder supervisord.
Het image wordt op de Pi zelf gebouwd, dus er is geen registry nodig en geen
gedoe met architecturen.

Er is precies één volume, met daarin het SQLite-bestand. Verder schrijft de
app niets naar schijf: avatars worden uit de initialen gegenereerd en een
pistekaart is een link, geen upload.

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
   chmod 600 .env.docker
   ```

   Vul in `.env.docker` minstens in:

   - `APP_KEY` — genereer met
     `docker compose run --rm --entrypoint php app artisan key:generate --show --no-ansi`
     (`--no-ansi` is nodig, anders zitten er kleurcodes in de sleutel)
   - `REGISTRATION_CODE` — zonder deze code kan iedereen die `/register` vindt een account maken
   - `APP_URL` — het adres waarop je de site opent

5. **Starten:**

   ```bash
   docker compose up -d --build
   ```

6. **Router:** geef de Pi een vast LAN-adres en zet poort 80 door als je de
   site van buitenaf wil bereiken.

### Een wijziging uitrollen

```bash
cd /srv/vakantie
git pull
docker compose up -d --build
```

Migraties draaien automatisch bij het opstarten van de container.

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
docker compose logs -f app                        # logs volgen
docker compose exec app php artisan tinker        # tinker in de container
docker compose ps                                 # status en healthcheck
```

---

## Later: HTTPS ervoor zetten

Zet je de site echt op het internet, dan komt er een reverse proxy voor die
TLS afhandelt. In `compose.yml` wordt `ports:` op de app dan `expose: 80`,
en er komt een `caddy`-service bij die 80 en 443 pakt en doorzet naar
`app:80`.

Twee dingen moeten tegelijk mee, anders wijst de site naar `http://` en
breekt hij op mixed content:

- `bootstrap/app.php` krijgt `$middleware->trustProxies(at: '*')`
- `.env.docker` krijgt `SESSION_SECURE_COOKIE=true` en `APP_URL=https://...`

Die middleware-regel hoort er pas in zodra er werkelijk een proxy voor staat.
Op een container die zelf direct aan poort 80 hangt zou hij iedereen
toestaan `X-Forwarded-For` te vervalsen.
