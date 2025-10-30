# GitHub Actions

## Manuelles Deployment

Dieses Repository nutzt einen **manuellen Deployment-Workflow**, der über das GitHub UI gestartet wird.

### Wie nutze ich die manuelle Deployment-Action?

1. **Gehe zum Actions Tab:**
    - Öffne dein GitHub Repository
    - Klicke auf den Tab **"Actions"**

2. **Starte den Workflow:**
    - Wähle links **"Deploy to Production"**
    - Klicke auf **"Run workflow"** (rechts)
    - Wähle die Umgebung (production/staging)
    - Klicke auf den grünen **"Run workflow"** Button

3. **Beobachte den Fortschritt:**
    - Der Workflow startet und zeigt jeden Schritt live an
    - Bei Erfolg: ✅ Grünes Häkchen
    - Bei Fehler: ❌ Rotes X (Logs anschauen für Details)

### Voraussetzungen

Die folgenden **GitHub Secrets** müssen konfiguriert sein:

| Secret            | Beschreibung            | Beispiel                           |
| ----------------- | ----------------------- | ---------------------------------- |
| `SSH_HOST`        | Server IP oder Domain   | `123.456.78.90`                    |
| `SSH_USERNAME`    | SSH Username            | `deploy` oder `www-data`           |
| `SSH_PRIVATE_KEY` | Privater SSH Key        | Kompletter Inhalt der Key-Datei    |
| `REMOTE_PATH`     | App-Pfad auf dem Server | `/var/www/livewire-planning-poker` |
| `SSH_PORT`        | SSH Port (optional)     | `22` (Standard)                    |

### Was macht die Action?

Die Action führt folgende Schritte aus:

1. ✅ Code vom `main` Branch auf dem Server pullen
2. ✅ Composer Dependencies installieren
3. ✅ NPM Dependencies installieren
4. ✅ Assets kompilieren (`npm run build`)
5. ✅ Datenbank-Migrationen ausführen
6. ✅ Caches clearen und optimieren
7. ✅ Reverb-Server neu starten

### Workflow-Datei

Die Workflow-Konfiguration findest du hier:

- `.github/workflows/deploy.yml`

### Automatisches Deployment aktivieren (optional)

Wenn du **automatisches** Deployment bei jedem Push aktivieren möchtest, ändere in `.github/workflows/deploy.yml`:

```yaml
on:
    workflow_dispatch: # Manuell
    push: # Automatisch bei Push
        branches:
            - main
```

**Hinweis:** Für Production empfehlen wir manuelle Deployments, um volle Kontrolle zu behalten.

### Troubleshooting

**Problem:** Action schlägt fehl mit "Permission denied"

- **Lösung:** Prüfe, ob der SSH Key korrekt in den Secrets hinterlegt ist

**Problem:** "Host key verification failed"

- **Lösung:** Der Workflow fügt den Host automatisch zu known_hosts hinzu. Falls es weiterhin fehlschlägt, prüfe die SSH_HOST Variable

**Problem:** "pm2 command not found"

- **Lösung:** PM2 muss auf dem Server installiert sein: `npm install -g pm2`

Weitere Details findest du in [DEPLOYMENT.md](../DEPLOYMENT.md).
