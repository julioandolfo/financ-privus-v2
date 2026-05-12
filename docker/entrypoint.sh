#!/bin/sh
set -e

cd /var/www/html

# Generate key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Cache config in production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# Run migrations
php artisan migrate --force --no-interaction

# Clear old caches in development
if [ "$APP_ENV" != "production" ]; then
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# Storage link
php artisan storage:link --force 2>/dev/null || true

exec "$@"
