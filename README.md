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

## Session Archiving

- Run `php artisan migrate` after pulling the latest changes to add the `archived_at` column on `sessions`.
- Product Owners can archive a voting session via the `Archive` button inside the **Your own Sessions** card.
- Archived sessions disappear from the active list and are surfaced inside the new **Archived Sessions** block on the dashboard, where you can still open or delete them later.
- The `View` action opens a read-only overview showing all issues (title, story points, Jira link + description accordion). Direct hits on `/sessions/{code}/voting` for archived sessions redirect there automatically.
- Nur Owner sehen auf der read-only Seite einen Button „Session reaktivieren“, der das Archiv zurücksetzt und sofort wieder auf die aktive Voting-Ansicht führt.
- Ebenfalls im Dashboard-Archivblock steht Ownern ein „Reactivate“-Button zur Verfügung, um Sessions ohne Umweg wieder zu öffnen.

## Services

If you want to use the mail verification feature make sure to start a mailpit

```bash
docker run -d --restart unless-stopped --name=mailpit -p 8025:8025 -p 1025:1025 axllent/mailpit
```

For the websocket server, this project uses **Laravel Reverb** - Laravel's official WebSocket server.

### Starting Reverb Locally

**Option 1: Direct command (simple)**

```bash
php artisan reverb:start --host=127.0.0.1 --port=6000
```

**Option 2: With PM2 (recommended for development)**

First, create your PM2 config:

```bash
cp ecosystem.config.example.js ecosystem.config.js
# Edit ecosystem.config.js with your local settings (port, hostname)
```

Then start with PM2:

```bash
pm2 start ecosystem.config.js
pm2 save  # Save the process list
```

**Note:** The Reverb server must be running for real-time features like voting to work.

## Jira Integration

This application supports importing issues from Jira into your planning poker sessions.

### Configuration

Each user can configure their own Jira credentials in their profile settings:

1. Navigate to your **Profile** page
2. Scroll to the **Jira Credentials** section
3. Enter your Jira instance URL (e.g., `https://yourcompany.atlassian.net`)
4. Enter your Jira email address
5. Enter your Jira API token

**How to get your API Token:**

1. Go to [Atlassian Account Settings](https://id.atlassian.com/manage-profile/security/api-tokens)
2. Click on "API tokens"
3. Click "Create API token"
4. Give it a label (e.g., "Planning Poker")
5. Copy the generated token
6. Paste it in the profile field

**Note:** Each user needs their own API token. The token is encrypted and stored securely in the database.

**Important:** You must configure your Jira credentials before you can import issues. The Jira import section will only be visible after configuring your credentials in your profile.

### Usage

1. Configure your Jira credentials in your profile (see above)
2. As a Product Owner, navigate to your session management page
3. Enter your Jira Project Key (e.g., "SAN")
4. Select the status of tickets you want to import (e.g., "In Estimation")
5. Click "Load Tickets" to fetch all matching tickets from Jira
6. A modal will open showing all available tickets
7. Select the tickets you want to import using the checkboxes
8. Use "Select All" to quickly select/deselect all tickets
9. Click "Import Selected" to import the chosen tickets
10. Tickets that were already imported will be marked and disabled

### Testing the Jira Integration

To test the Jira integration:

1. Configure your Jira credentials in your profile
2. Use the "Test Connection" button in the profile to verify your credentials
3. Create a session or navigate to an existing one
4. Look for the "Import from Jira" card
5. Enter a valid project key and status
6. Click "Load Tickets" - you should see a modal with tickets
7. Select some tickets and import them
8. Verify that the tickets appear in your session's issue list

**Note:** Make sure your Jira instance is accessible and you have permission to view the tickets in the specified project and status.

**If you don't see the Jira import section:** Navigate to your profile settings and configure your Jira credentials first. You'll see a link in the import section that takes you directly to the profile settings.

### Production Deployment

**For production deployment**, see [WEBSOCKET.md](WEBSOCKET.md) for detailed instructions using:

- **Supervisor** (recommended for Linux servers)
- **PM2** (Node.js process manager)
- **systemd** (system service manager)

### Testing the Connection

After starting the Reverb server, open your browser console and check for WebSocket connection errors. The connection should be established to `ws://127.0.0.1:6000`.

## Troubleshooting

### Livewire Component Errors

If you encounter "Could not find Livewire component in DOM tree" errors in production, see [LIVEWIRE-FIX.md](LIVEWIRE-FIX.md) for details about the implemented fixes and solutions.

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
