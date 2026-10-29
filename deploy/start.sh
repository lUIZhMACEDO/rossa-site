#!/bin/sh
set -e
cd /var/www/html

# Render passes the port to listen on in $PORT
PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# A stable Laravel key derived from a secret seed the host generates once
if [ -z "$APP_KEY" ] && [ -n "$APP_KEY_SEED" ]; then
    export APP_KEY="base64:$(php -r 'echo base64_encode(hash("sha256", getenv("APP_KEY_SEED"), true));')"
fi

php artisan storage:link >/dev/null 2>&1 || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
