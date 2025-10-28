<p align="center"><img src="https://raw.githubusercontent.com/Andreas-Halemba/livewire-planning-poker/main/resources/images/logo-cards.png" height="200" alt="Planning Poker Logo"></p>

## Configuration

These environment variables should be set accordingly

```env
DB_DATABASE=planning_poker
DB_USERNAME=root
DB_PASSWORD=

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http

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

## Services

If you want to use the mail verification feature make sure to start a mailpit

```bash
docker run -d --restart unless-stopped --name=mailpit -p 8025:8025 -p 1025:1025 axllent/mailpit
```

For the websocket server, this project uses Laravel Reverb - Laravel's official WebSocket server.

To start the Reverb server locally:

```bash
php artisan reverb:start
```

**For production deployment**, see [WEBSOCKET.md](WEBSOCKET.md) for detailed instructions using:

-   **Supervisor** (recommended for Linux servers)
-   **PM2** (Node.js process manager)
-   **systemd** (system service manager)

**Note:** Make sure your `.env` file has these settings:

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=app-id-poker
REVERB_APP_KEY=app-key-poker
REVERB_APP_SECRET=HrX9xcwUAA-poker
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

The Reverb server runs on port 8080 by default and handles all WebSocket connections for real-time features like voting.

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
