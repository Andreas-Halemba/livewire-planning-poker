module.exports = {
    apps: [
        {
            name: 'reverb',
            script: 'artisan',
            // Anpassen: Port und Hostname für deine Umgebung
            args: 'reverb:start --host=0.0.0.0 --port=YOUR_PORT --hostname=YOUR_HOSTNAME',
            interpreter: 'php',
            cwd: __dirname,
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '1G',
            env: {
                // 'local' für lokale Entwicklung, 'production' für Server
                APP_ENV: 'production',
            },
            error_file: './storage/logs/reverb-error.log',
            out_file: './storage/logs/reverb-out.log',
            log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
            merge_logs: true,
        },
    ],
}
