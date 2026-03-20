#!/bin/sh
set -e
php artisan package:discover --ansi 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
exec "$@"
