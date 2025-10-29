# WebSocket Server Deployment Guide

This project uses **Laravel Reverb** for real-time WebSocket connections. Reverb is Laravel's official WebSocket server and provides a simple, production-ready solution for broadcasting events to connected clients.

## Overview

Reverb handles real-time features such as:

- Live voting updates
- Session participant changes
- Vote reveal notifications
- Real-time collaboration features

## Prerequisites

- PHP 8.2 or higher
- Laravel 11.x
- Laravel Reverb package installed
- A process manager (Supervisor, PM2, or systemd)

## Installation

Reverb is already included in this project's dependencies. If you need to install it manually:

```bash
composer require laravel/reverb
php artisan reverb:install
```

## Configuration

### Environment Variables

Configure your `.env` file with the following Reverb settings:

```env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=your-domain.com
REVERB_PORT=8080
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Generate Reverb Credentials

Run the Reverb installation command to generate unique credentials:

```bash
php artisan reverb:install
```

This will:

1. Publish the Reverb configuration file
2. Generate unique app credentials
3. Update your `.env` file automatically

## Production Deployment

### Using Supervisor (Recommended)

Supervisor is a popular process control system for Linux. Here's how to set it up:

#### 1. Install Supervisor

```bash
sudo apt-get install supervisor  # Ubuntu/Debian
sudo yum install supervisor       # CentOS/RHEL
```

#### 2. Create Supervisor Configuration

Create a new configuration file at `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/reverb.log
stopwaitsecs=3600
```

**Important:** Replace `/path/to/your/project` with your actual project path.

#### 3. Start Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb:*
```

#### 4. Monitor the Process

```bash
sudo supervisorctl status reverb:*
sudo tail -f /path/to/your/project/storage/logs/reverb.log
```

### Using systemd

For systemd-based systems (Ubuntu 16.04+, CentOS 7+):

#### 1. Create Service File

Create `/etc/systemd/system/reverb.service`:

```ini
[Unit]
Description=Laravel Reverb Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

#### 2. Enable and Start

```bash
sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl start reverb
sudo systemctl status reverb
```

### Using PM2 (Node.js Process Manager)

PM2 is a popular process manager for Node.js applications, but it also works great for PHP processes like Reverb.

#### 1. Install PM2

```bash
npm install -g pm2
```

#### 2. Create PM2 Ecosystem File

Create a file `ecosystem.config.js` in your project root:

```javascript
module.exports = {
    apps: [
        {
            name: 'reverb',
            script: 'artisan',
            args: 'reverb:start --host=0.0.0.0 --port=8080',
            interpreter: 'php',
            cwd: '/path/to/your/project',
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '1G',
            env: {
                APP_ENV: 'production',
            },
            error_file: './storage/logs/reverb-error.log',
            out_file: './storage/logs/reverb-out.log',
            log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
            merge_logs: true,
        },
    ],
}
```

**Important:** Replace `/path/to/your/project` with your actual project path.

#### 3. Start with PM2

```bash
# Start Reverb
pm2 start ecosystem.config.js

# Save PM2 configuration
pm2 save

# Setup PM2 to start on system boot
pm2 startup
```

#### 4. Monitor and Manage

```bash
# Check status
pm2 status

# View logs
pm2 logs reverb

# Restart Reverb
pm2 restart reverb

# Stop Reverb
pm2 stop reverb

# Monitor resources
pm2 monit
```

#### 5. Running Multiple Instances

To run multiple Reverb instances with PM2, update `ecosystem.config.js`:

```javascript
module.exports = {
    apps: [
        {
            name: 'reverb',
            script: 'artisan',
            args: 'reverb:start --host=0.0.0.0 --port=8080',
            interpreter: 'php',
            cwd: '/path/to/your/project',
            instances: 3, // Run 3 instances
            exec_mode: 'fork',
            autorestart: true,
            // ... rest of config
        },
    ],
}
```

**Note:** When running multiple instances, make sure to enable Redis scaling in `config/reverb.php` for proper message distribution.

## Load Balancing

If you need to run multiple Reverb servers behind a load balancer:

### Option 1: Multiple Supervisor Processes

Update your Supervisor config:

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan reverb:start --host=0.0.0.0 --port=808%(process_num)02d
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/reverb-%(process_num)02d.log
```

This will start three instances on ports 8080, 8081, and 8082.

### Option 2: Redis Scaling

Enable Redis scaling in `config/reverb.php`:

```php
'scaling' => [
    'enabled' => env('REVERB_SCALING_ENABLED', true),
    'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
    'server' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', '6379'),
        'password' => env('REDIS_PASSWORD'),
        'database' => env('REDIS_DB', '0'),
    ],
],
```

Then update your `.env`:

```env
REVERB_SCALING_ENABLED=true
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Reverse Proxy Configuration

### Nginx

Add this to your Nginx configuration:

```nginx
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
```

### Apache

Add this to your Apache VirtualHost:

```apache
<LocationMatch "^/app/">
    ProxyPass http://127.0.0.1:8080/app/
    ProxyPassReverse http://127.0.0.1:8080/app/

    RewriteEngine on
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/?(.*) "ws://127.0.0.1:8080/$1" [P,L]
</LocationMatch>
```

## Security Considerations

### SSL/TLS

For production, always use HTTPS/WSS:

```env
REVERB_SCHEME=https
```

### Allowed Origins

Update `config/reverb.php` to restrict origins:

```php
'allowed_origins' => [
    'https://yourdomain.com',
    'https://www.yourdomain.com',
],
```

### Rate Limiting

Consider implementing rate limiting for WebSocket connections to prevent abuse.

## Monitoring

### Health Check

Reverb provides a health check endpoint:

```bash
curl http://localhost:8080/reverb/health
```

### Logs

Monitor Reverb logs for issues:

```bash
tail -f storage/logs/reverb.log
```

### Telescope Integration

Reverb integrates with Laravel Telescope. Enable it in your environment:

```env
TELESCOPE_ENABLED=true
```

## Troubleshooting

### Connection Refused

- Ensure Reverb is running: `sudo supervisorctl status reverb:*`
- Check firewall settings for port 8080
- Verify the host binding (use `0.0.0.0` for production)

### WebSocket Connection Failed

- Check browser console for errors
- Verify `.env` variables match between Laravel and Vite build
- Ensure SSL certificates are valid (for HTTPS)

### High Memory Usage

- Restart Reverb periodically
- Consider running multiple instances
- Monitor for memory leaks

### Connection Drops

- Increase timeout values in proxy configuration
- Check network stability
- Verify Redis connection (if using scaling)

## Quick Start Commands

```bash
# Start Reverb manually (for testing)
php artisan reverb:start

# Check Reverb status (Supervisor)
sudo supervisorctl status reverb:*

# Restart Reverb (Supervisor)
sudo supervisorctl restart reverb:*

# View logs
tail -f storage/logs/reverb.log

# Stop Reverb
sudo supervisorctl stop reverb:*
```

## Performance Tips

1. **Process Management**: Always use a process manager in production
2. **Multiple Instances**: Run multiple Reverb processes for better performance
3. **Redis Scaling**: Enable Redis scaling for horizontal scaling
4. **Connection Limits**: Configure `max_connections` in `config/reverb.php`
5. **Monitoring**: Set up monitoring and alerting for production environments

## Additional Resources

- [Laravel Reverb Documentation](https://laravel.com/docs/reverb)
- [Supervisor Documentation](http://supervisord.org/)
- [Nginx WebSocket Proxy](https://nginx.org/en/docs/http/websocket.html)
