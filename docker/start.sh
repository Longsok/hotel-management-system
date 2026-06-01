#!/usr/bin/env bash
set -o errexit

# Clear any stale cache first
php artisan config:clear
php artisan cache:clear

# Run migrations
php artisan migrate --force

# Now cache with real env vars present
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start services
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf