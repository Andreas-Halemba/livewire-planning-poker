# Laravel Planning Poker - Deployment Guide für Ubuntu Server

Dieser Guide führt durch das komplette Deployment einer Laravel Planning Poker App auf einem Ubuntu Server.

## Übersicht

Die App benötigt:

- PHP 8.2+ mit erforderlichen Extensions
- Composer
- Node.js & npm
- MySQL/MariaDB oder PostgreSQL
- Nginx (oder Apache)
- Redis (optional, für besseres Caching/Scaling)
- Supervisor oder PM2 für Reverb WebSocket Server
- SSL-Zertifikat (Let's Encrypt empfohlen)

---

## 1. Server-Vorbereitung

### 1.1 System aktualisieren

```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Benutzer für die App erstellen (optional, empfohlen)

```bash
sudo adduser --system --group --home /var/www planning-poker
```

---

## 2. PHP Installation

### 2.1 PHP Repository hinzufügen

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### 2.2 PHP 8.2 und benötigte Extensions installieren

```bash
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
    php8.2-redis php8.2-opcache
```

### 2.3 PHP Konfiguration optimieren

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Wichtige Einstellungen für Production:

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
```

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

---

## 3. MySQL/MariaDB Installation

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### Datenbank und Benutzer erstellen

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE planning_poker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'planning_poker'@'localhost' IDENTIFIED BY 'DEIN_STARKES_PASSWORT';
GRANT ALL PRIVILEGES ON planning_poker.* TO 'planning_poker'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Redis Installation (empfohlen)

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

---

## 5. Composer Installation

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

---

## 6. Node.js Installation

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 7. Nginx Installation & Konfiguration

### 7.1 Nginx installieren

```bash
sudo apt install -y nginx
```

### 7.2 Nginx Konfiguration erstellen

```bash
sudo nano /etc/nginx/sites-available/planning-poker
```

Ersetze `yourdomain.com` mit deiner Domain:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;

    # Redirect HTTP to HTTPS (nach SSL-Setup)
    # return 301 https://$server_name$request_uri;

    root /var/www/planning-poker/public;
    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Laravel Reverb WebSocket Proxy
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
}
```

Aktiviere die Site:

```bash
sudo ln -s /etc/nginx/sites-available/planning-poker /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 8. Application Deployment

### 8.1 Code auf Server kopieren

Option A: Via Git (empfohlen)

```bash
cd /var/www
sudo git clone https://github.com/yourusername/livewire-planning-poker.git planning-poker
sudo chown -R planning-poker:planning-poker /var/www/planning-poker
cd planning-poker
```

Option B: Via rsync/scp von lokalem Rechner

```bash
# Von lokalem Rechner aus:
rsync -avz --exclude 'node_modules' --exclude 'vendor' \
    ./ user@server:/var/www/planning-poker/
```

### 8.2 Composer Dependencies installieren

```bash
cd /var/www/planning-poker
composer install --optimize-autoloader --no-dev
```

### 8.3 Node.js Dependencies & Build

```bash
npm install
npm run build
```

### 8.4 Environment Datei erstellen

```bash
cp .env.example .env
nano .env
```

Wichtige Produktionseinstellungen:

```env
APP_NAME="Planning Poker"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=planning_poker
DB_USERNAME=planning_poker
DB_PASSWORD=DEIN_STARKES_PASSWORT

BROADCAST_DRIVER=reverb
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=sync
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Laravel Reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=yourdomain.com
REVERB_PORT=8080
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 8.5 App Key generieren & Reverb Credentials

```bash
php artisan key:generate
php artisan reverb:install
```

Die `reverb:install` Command generiert automatisch die `REVERB_APP_ID`, `REVERB_APP_KEY` und `REVERB_APP_SECRET` und schreibt sie in die `.env` Datei.

### 8.6 Datenbank Migrationen ausführen

```bash
php artisan migrate --force
```

### 8.7 Storage Link erstellen

```bash
php artisan storage:link
```

### 8.8 Optimierungen für Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 9. Laravel Reverb WebSocket Server

### Option A: Supervisor (empfohlen für Ubuntu)

#### Supervisor installieren

```bash
sudo apt install -y supervisor
```

#### Supervisor Config erstellen

```bash
sudo nano /etc/supervisor/conf.d/reverb.conf
```

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/planning-poker/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/planning-poker/storage/logs/reverb.log
stopwaitsecs=3600
```

#### Supervisor aktivieren

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb:*
sudo supervisorctl status
```

### Option B: PM2

Siehe `ecosystem.config.js` im Projekt für PM2-Konfiguration.

```bash
npm install -g pm2
cd /var/www/planning-poker
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

---

## 10. SSL-Zertifikat mit Let's Encrypt

### 10.1 Certbot installieren

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 10.2 SSL-Zertifikat erstellen

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Certbot konfiguriert Nginx automatisch für HTTPS.

### 10.3 Auto-Renewal testen

```bash
sudo certbot renew --dry-run
```

---

## 11. Firewall konfigurieren

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

**Hinweis:** Reverb läuft lokal auf Port 8080, muss nicht öffentlich erreichbar sein.

---

## 12. Berechtigungen setzen

```bash
cd /var/www/planning-poker
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 13. Finale Checks

### 13.1 Nginx Status

```bash
sudo systemctl status nginx
```

### 13.2 PHP-FPM Status

```bash
sudo systemctl status php8.2-fpm
```

### 13.3 Reverb Status

```bash
sudo supervisorctl status reverb:*
# oder bei PM2:
pm2 status
```

### 13.4 App testen

Öffne im Browser: `https://yourdomain.com`

---

## 14. Monitoring & Maintenance

### 14.1 Logs überwachen

```bash
# Laravel Logs
tail -f /var/www/planning-poker/storage/logs/laravel.log

# Reverb Logs
tail -f /var/www/planning-poker/storage/logs/reverb.log

# Nginx Error Logs
sudo tail -f /var/log/nginx/error.log
```

### 14.2 Regelmäßige Wartung

Erstelle ein Cron-Job für Laravel Scheduler (falls verwendet):

```bash
sudo crontab -e -u www-data
```

Füge hinzu:

```
* * * * * cd /var/www/planning-poker && php artisan schedule:run >> /dev/null 2>&1
```

---

## 15. Deployment-Workflow (für Updates)

### 15.1 Deployment Script erstellen

```bash
cd /var/www/planning-poker
nano deploy.sh
```

```bash
#!/bin/bash

echo "🚀 Starting deployment..."

# Backup aktuelles Release (optional)
# cp -r . ../backup-$(date +%Y%m%d-%H%M%S)

# Code aktualisieren
git pull origin main

# Dependencies aktualisieren
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Laravel Optimierungen
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Migrationen ausführen (nur bei Schema-Änderungen)
# php artisan migrate --force

# Reverb neu starten
sudo supervisorctl restart reverb:*
# oder: pm2 restart reverb

echo "✅ Deployment completed!"
```

```bash
chmod +x deploy.sh
```

### 15.2 Deployment ausführen

```bash
./deploy.sh
```

---

## 16. Troubleshooting

### Problem: 502 Bad Gateway

**Lösung:**

- PHP-FPM Status prüfen: `sudo systemctl status php8.2-fpm`
- Nginx Config testen: `sudo nginx -t`
- Socket-Pfad prüfen: `ls -la /var/run/php/php8.2-fpm.sock`

### Problem: WebSocket-Verbindung schlägt fehl

**Lösung:**

- Reverb läuft? `sudo supervisorctl status reverb:*`
- Port 8080 erreichbar? `curl http://localhost:8080/reverb/health`
- Nginx WebSocket-Proxy korrekt? Siehe Abschnitt 7.2
- `.env` Variablen korrekt? `VITE_*` Variablen müssen nach `npm run build` neu gebaut werden

### Problem: Fehlende Berechtigungen

**Lösung:**

```bash
sudo chown -R www-data:www-data /var/www/planning-poker/storage
sudo chmod -R 775 /var/www/planning-poker/storage
```

### Problem: Composer Memory Limit

**Lösung:**

```bash
php -d memory_limit=512M /usr/local/bin/composer install
```

---

## 17. Sicherheits-Checkliste

- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_ENV=production` in `.env`
- [ ] Starke Datenbank-Passwörter
- [ ] SSL-Zertifikat aktiviert
- [ ] Firewall konfiguriert (nur 22, 80, 443)
- [ ] Nginx Security Headers (optional)
- [ ] Regelmäßige Updates: `sudo apt update && sudo apt upgrade`
- [ ] Backups der Datenbank einrichten

---

## Nächste Schritte

Nach erfolgreichem Deployment:

1. Erstelle einen Admin-User (falls nötig)
2. Teste die WebSocket-Verbindungen (Browser Console prüfen)
3. Stelle sicher, dass E-Mails funktionieren
4. Richte Backups ein (z.B. mit `mysqldump` + Cron)
5. Monitor die Logs in den ersten Tagen

---

## Support & Ressourcen

- [Laravel Deployment Docs](https://laravel.com/docs/deployment)
- [Laravel Reverb Docs](https://laravel.com/docs/reverb)
- [Nginx Documentation](https://nginx.org/en/docs/)
- Projekt-spezifisch: Siehe `WEBSOCKET.md` für erweiterte Reverb-Konfiguration
