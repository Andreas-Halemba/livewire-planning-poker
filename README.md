<p align="center"><img src="https://raw.githubusercontent.com/Andreas-Halemba/livewire-planning-poker/main/resources/images/logo-cards.png" height="200" alt="Planning Poker Logo"></p>

## Configuration

These environment variables should be set accordingly

```env
DB_DATABASE=planning_poker
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=6000
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

MAIL_MAILER=

# Jira Integration (optional)
JIRA_HOST=https://your-jira-instance.atlassian.net
JIRA_USER=your-email@example.com
JIRA_PASS=your-api-token
```

## Setup

Install dependencies

```bash
cp .env.example .env
```

```bash
composer install
```

```bash
npm install
```

Build the assets

```bash
npm run build
```

generate an encryption key

```bash
php artisan key:generate
```

## Start app

if you have valet installed, what i recommend

```bash
valet link
```

or run

```bash
php artisan serve
```

## Services

If you want to use the mail verification feature make sure to start a mailpit

```bash
docker run -d --restart unless-stopped --name=mailpit -p 8025:8025 -p 1025:1025 axllent/mailpit
```

For the websocket server, this project uses **Laravel Reverb** - Laravel's official WebSocket server.

### Starting Reverb Locally

To start the Reverb server locally:

```bash
php artisan reverb:start --host=127.0.0.1 --port=6000
```

For better local development experience, you can run it in the background:

```bash
php artisan reverb:start --host=127.0.0.1 --port=6000 &
```

**Note:** The Reverb server must be running for real-time features like voting to work.

## Jira Integration

This application supports importing issues from Jira into your planning poker sessions.

### Configuration

To enable Jira integration, add the following environment variables to your `.env` file:

```env
JIRA_HOST=https://your-jira-instance.atlassian.net
JIRA_USER=your-email@example.com
JIRA_PASS=your-api-token
```

**Note:** You need to use an API token, not your regular password. You can generate one in your [Atlassian Account Settings](https://id.atlassian.com/manage-profile/security/api-tokens).

### Usage

1. As a Product Owner, navigate to your session management page
2. Enter your Jira Project Key (e.g., "SAN")
3. Select the status of tickets you want to import (e.g., "In Estimation")
4. Click "Load Tickets" to fetch all matching tickets from Jira
5. A modal will open showing all available tickets
6. Select the tickets you want to import using the checkboxes
7. Use "Select All" to quickly select/deselect all tickets
8. Click "Import Selected" to import the chosen tickets
9. Tickets that were already imported will be marked and disabled

### Testing the Jira Integration

To test the Jira integration:

1. Make sure you have valid Jira credentials configured in your `.env` file
2. Create a session or navigate to an existing one
3. Look for the "Import from Jira" card
4. Enter a valid project key and status
5. Click "Load Tickets" - you should see a modal with tickets
6. Select some tickets and import them
7. Verify that the tickets appear in your session's issue list

**Note:** Make sure your Jira instance is accessible and you have permission to view the tickets in the specified project and status.

### Production Deployment

**For production deployment**, see [WEBSOCKET.md](WEBSOCKET.md) for detailed instructions using:

-   **Supervisor** (recommended for Linux servers)
-   **PM2** (Node.js process manager)
-   **systemd** (system service manager)

### Testing the Connection

After starting the Reverb server, open your browser console and check for WebSocket connection errors. The connection should be established to `ws://127.0.0.1:6000`.

## Code style and quality

### formats the code

```bash
npx prettier --write .
```

in order to test only use

```bash
npx prettier --check .
```

### static code check

```bash
php vendor/bin/phpstan
```

### Run tests

```bash
php artisan test
```

### run PHP code style check

```bash
php vendor/bin/pint --test
```

in order to autofix run

```bash
php vendor/bin/pint
```

### pre commit hook

install captainhook via

```bash
php ./vendor/bin/captainhook install -f
```

## License

The Planning Poker App is under the [MIT license](https://opensource.org/licenses/MIT).
