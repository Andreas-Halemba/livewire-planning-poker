# Deployment Guide

## Option 1: Shell-Skript (Einfach & Schnell)

**Für lokale Entwicklung.** Server-Daten werden in einer lokalen Config-Datei gespeichert, die nicht ins Repository committed wird.

### Setup

1. **Deployment-Skript ausführbar machen:**

```bash
chmod +x deploy.sh
```

2. **Server-Konfiguration anlegen:**

Kopiere die Beispiel-Konfiguration und passe sie an:

```bash
cp deploy.config.example deploy.config
```

3. **Server-Daten in `deploy.config` eintragen:**

Bearbeite `deploy.config` (diese Datei ist gitignored und wird nicht committed):

```bash
# Server configuration
SERVERS[production]="dein-user@deine-server-ip.com"
SERVERS[staging]="dein-user@dein-staging-server.com"  # optional

# Remote paths
PATHS[production]="/var/www/livewire-planning-poker"
PATHS[staging]="/var/www/livewire-planning-poker-staging"  # optional
```

4. **SSH-Key Setup (falls noch nicht vorhanden):**

```bash
# Lokalen SSH-Key generieren (falls noch keiner existiert)
ssh-keygen -t ed25519 -C "deployment@planning-poker"

# Public Key auf Server kopieren
ssh-copy-id dein-user@deine-server-ip.com
```

### Verwendung

```bash
# Deployment auf Production
./deploy.sh production

# Deployment auf Staging (falls konfiguriert)
./deploy.sh staging
```

Das Skript macht automatisch:

- ✅ Git Push zum Repository
- ✅ Git Pull auf dem Server
- ✅ Composer Dependencies installieren
- ✅ NPM Dependencies installieren
- ✅ Assets kompilieren (`npm run build`)
- ✅ Datenbank-Migrationen ausführen
- ✅ Cache clearen
- ✅ Laravel optimieren
- ✅ Reverb-Server neu starten (PM2)

---

## Option 2: GitHub Actions (Automatisch)

**Für CI/CD.** Nutzt GitHub Secrets, keine lokale Config-Datei nötig.

### Setup

1. **GitHub Secrets konfigurieren:**

Gehe zu deinem GitHub Repository → **Settings** → **Secrets and variables** → **Actions**

Erstelle folgende Secrets:

- `SSH_HOST`: Deine Server-IP oder Domain (z.B. `123.456.78.90` oder `dein-server.de`)
- `SSH_USERNAME`: SSH-Username (z.B. `www-data` oder `deploy`)
- `SSH_PRIVATE_KEY`: Dein privater SSH-Key (gesamter Inhalt der privaten Key-Datei)
- `REMOTE_PATH`: Pfad zur Anwendung auf dem Server (z.B. `/var/www/livewire-planning-poker`)
- `SSH_PORT`: SSH-Port (optional, Standard: 22 wenn nicht gesetzt)

2. **SSH-Key für GitHub Actions erstellen:**

```bash
# Auf deinem lokalen Rechner
ssh-keygen -t ed25519 -C "github-actions@planning-poker" -f ~/.ssh/github_actions

# Public Key auf Server kopieren
ssh-copy-id -i ~/.ssh/github_actions.pub dein-user@deine-server-ip.com

# Private Key in GitHub Secrets eintragen (gesamten Inhalt kopieren)
cat ~/.ssh/github_actions
```

3. **Branch-Trigger anpassen (optional):**

In `.github/workflows/deploy.yml` kannst du festlegen, bei welchen Branches automatisch deployed wird:

```yaml
on:
    push:
        branches:
            - main # Deployment bei Push auf main
            - production # Deployment bei Push auf production
```

### Verwendung

Nach dem Setup wird **automatisch deployed**, sobald du pushst:

```bash
git push origin async-voting
```

GitHub Actions führt dann automatisch das Deployment durch. Du kannst den Status im Tab "Actions" deines Repositories sehen.

---

## Server-Anforderungen

Dein Server sollte folgendes installiert haben:

- PHP 8.2+
- Composer
- Node.js & NPM
- Git
- PM2 (für Reverb)
- MySQL/PostgreSQL

---

## Manuelle Schritte (falls etwas schief geht)

Falls das automatische Deployment fehlschlägt, kannst du diese Schritte manuell ausführen:

```bash
# SSH auf Server
ssh dein-user@deine-server-ip.com

# In Projekt-Verzeichnis wechseln
cd /var/www/planning-poker

# Code aktualisieren
git pull origin main

# Dependencies installieren
composer install --no-dev --optimize-autoloader
npm ci --production=false

# Assets kompilieren
npm run build

# Migrationen ausführen
php artisan migrate --force

# Caches clearen und optimieren
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reverb neu starten
pm2 restart reverb

# Status prüfen
pm2 status
pm2 logs reverb
```

---

## Troubleshooting

### Deployment schlägt fehl

1. **SSH-Verbindung prüfen:**

```bash
ssh -v dein-user@deine-server-ip.com
```

2. **Berechtigungen prüfen:**

```bash
# Auf dem Server
ls -la /var/www/planning-poker
# Der User sollte Schreibrechte haben
```

3. **Git-Status prüfen:**

```bash
# Auf dem Server
cd /var/www/planning-poker
git status
git log
```

### Assets werden nicht aktualisiert

```bash
# Auf dem Server
cd /var/www/planning-poker
npm run build
php artisan view:clear
```

### Reverb startet nicht

```bash
# PM2 Logs prüfen
pm2 logs reverb --lines 100

# Reverb manuell neu starten
pm2 delete reverb
pm2 start ecosystem.config.js
pm2 save
```

---

## Rollback

Falls nach dem Deployment Probleme auftreten:

```bash
# Auf dem Server
cd /var/www/planning-poker

# Zum vorherigen Commit zurück
git log --oneline -10  # Letzten guten Commit finden
git reset --hard <commit-hash>

# Dependencies neu installieren
composer install --no-dev --optimize-autoloader
npm ci --production=false
npm run build

# Cache clearen
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# Reverb neu starten
pm2 restart reverb
```

---

## Best Practices

1. **Immer erst auf Staging testen** (falls vorhanden)
2. **Backup vor Deployment:**

```bash
# Auf dem Server
cd /var/www
tar -czf planning-poker-backup-$(date +%Y%m%d-%H%M%S).tar.gz planning-poker/
mysqldump -u user -p database > backup-$(date +%Y%m%d-%H%M%S).sql
```

3. **Monitoring aktivieren:**

```bash
# PM2 Monitoring
pm2 monitor

# Laravel Logs
tail -f /var/www/planning-poker/storage/logs/laravel.log
```

4. **Health-Check nach Deployment:**
    - ✅ Webseite lädt
    - ✅ WebSocket-Verbindung funktioniert
    - ✅ Login funktioniert
    - ✅ Voting funktioniert
    - ✅ Keine Fehler in Browser-Console
    - ✅ Keine Fehler in Laravel-Logs

---

## Empfehlung

**Für den Start:** Verwende das Shell-Skript (`deploy.sh`), es ist einfacher zu debuggen und gibt dir mehr Kontrolle.

**Für später:** Wechsle zu GitHub Actions, wenn du vollautomatisches Deployment möchtest.
