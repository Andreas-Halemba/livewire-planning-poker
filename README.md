<p align="center"><img src="https://raw.githubusercontent.com/Andreas-Halemba/livewire-planning-poker/main/resources/images/logo-cards.png" height="200" alt="Planning Poker Logo"></p>

## Configuration

These environment variables should be set acodingly

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

For the websocket server you can either choose a pusher js or a replacement SAAS solution.

If you want to use the open source laravel websocket which is installed run

```bash
php artisan websocket:serve
```

## Code style and quality

formats the code

```bash
npm run format
```

static code check

```bash
php vendor/bin/phpstan
```

Run tests

```bash
php artisan test
```

## License

The Planning Poker App is under the [MIT license](https://opensource.org/licenses/MIT).
