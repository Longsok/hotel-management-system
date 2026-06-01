#!/usr/bin/env bash
set -o errexit

# Run migrations against the live DB on boot
php artisan migrate --force

# Start both services via supervisor
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf