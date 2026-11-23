---
name: dev-services
description: Check, start, and verify all local services and dev processes required to run livewire-planning-poker locally with Laravel Herd (PHP, MySQL, Redis, Laravel Reverb, Vite/assets, and mail). Use when starting local development, before testing the UI or WebSockets/broadcasting, when hitting 502 Bad Gateway / BroadcastException / connection refused errors, or when the user asks to start or check the app's services.
---

# Local Dev Services & Startup (`livewire-planning-poker`)

This project runs locally on **Laravel Herd** (never start `php artisan serve`). Configuration is declared in `herd.yml`.

## Required Stack

| Component | Source / Config | Default Endpoint | Why Required |
| --- | --- | --- | --- |
| **Site & PHP** | `herd.yml` (`php: '8.5'`, `secured: true`) | `https://livewire-planning-poker.test` | Nginx + PHP-FPM served by Herd |
| **MySQL** | `herd.yml` (`port: ${DB_PORT}`) | `127.0.0.1:3306` (`planning_poker`) | Primary database |
| **Redis** | `herd.yml` (`port: ${REDIS_PORT}`) | `127.0.0.1:6379` | `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis` |
| **Laravel Reverb** | `herd.yml` (`port: 8082`) | `https://reverb.herd.test:443` → `:8082` | `BROADCAST_DRIVER=reverb` (live voting & presence) |
| **Frontend Assets** | `vite.config.js` | `public/build/manifest.json` or Vite HMR | Tailwind CSS + DaisyUI + Echo JS bundle |
| **Herd Mail** *(optional)* | `.env` (`MAIL_MAILER=smtp`) | `127.0.0.1:2525` | Capturing local auth/verification emails |

> **Known pitfall:** Reverb may be installed in Herd with `autoStart: false`, leaving it `stopped` after a reboot or Herd restart. When Reverb is stopped, any broadcast throws `BroadcastException` with `502 Bad Gateway` from `reverb.herd.test`.

## Workflow

Prefer **Herd MCP tools** when available; otherwise use the equivalent **`herd` CLI** commands.

### 1. Inspect Site, Services & Dev Processes

- **Herd MCP:** Call in parallel:
  - `herd.site({ action: "info" })`
  - `herd.services({ action: "list" })`
  - `herd.processes({ action: "list" })`
- **CLI fallback:**
  ```bash
  herd status
  herd site:info
  herd services:list
  herd processes
  ```

### 2. Ensure Site Setup & Dependencies

1. If the site is not linked, not secured, or not on the PHP version declared in `herd.yml`:
   - Run `herd init` (or `herd link --secure` + `herd isolate <version>`).
2. If `.env` is missing:
   - Copy `.env.example` to `.env`, run `herd php artisan key:generate`, and verify `DB_*`, `REDIS_*`, `REVERB_*`, and `VITE_REVERB_*` match the Herd services.
3. If `vendor/` or `node_modules/` is missing:
   - Run `herd composer install` and `npm ci`.

### 3. Start Any Stopped Required Services

Check `mysql`, `redis`, and `reverb` from step 1. For every required service that is not `running`:

- **Herd MCP:** `herd.services({ action: "start", service: "<id-or-name>" })`
- **CLI fallback:** `herd services:start "<name-or-id>"` (e.g. `herd services:start Reverb`)

If a required service from `herd.yml` is not installed yet, run `herd init` (or `herd services:create <type> --port=<port>`).

### 4. Ensure Frontend Assets Are Ready

Choose based on what the user needs:
- **Interactive dev / HMR:** Start Vite via Herd so it stays managed in the background:
  - **Herd MCP:** `herd.processes({ action: "start", process: "vite" })`
  - **CLI fallback:** `herd processes:start vite`
- **Quick smoke test / static assets only:**
  - If `public/hot` exists while Vite is **not** running, either start Vite or remove the stale `public/hot` file so Laravel serves compiled assets instead of hanging on a dead HMR port.
  - If `public/build/manifest.json` is missing or stale (e.g. after `VITE_REVERB_*` or JS/CSS changes), run `npm run build`.

### 5. Verify End-to-End Health

Run quick checks to confirm the app is actually reachable and healthy:

```bash
# 1. App responds on expected PHP version (expect HTTP 200)
curl -skI https://livewire-planning-poker.test/login | head -n 6

# 2. Reverb proxy is reachable (expect HTTP 404/426, NEVER 502 Bad Gateway)
curl -skI https://reverb.herd.test | head -n 5

# 3. Database connection & migrations
herd php artisan migrate:status
```

If migrations are pending, inform the user (or run `herd php artisan migrate` if asked).

### 6. Report Status

Return a concise status table covering:
- **Site & PHP** (URL + active PHP version)
- **MySQL**, **Redis**, **Reverb** (port + status, noting anything you started)
- **Vite / Assets** (`running` HMR vs built `public/build`)
- **Any issues or reminders** (e.g., suggest enabling Auto-Start for Reverb in Herd if it was stopped)
