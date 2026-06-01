#!/usr/bin/env 
set -o errexit

php artisan config:clear || true

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf