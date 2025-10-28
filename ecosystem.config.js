module.exports = {
    apps: [
        {
            name: 'reverb',
            script: 'artisan',
            args: 'reverb:start --host=0.0.0.0 --port=8080',
            interpreter: 'php',
            cwd: __dirname,
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
