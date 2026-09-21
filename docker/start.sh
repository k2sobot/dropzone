#!/bin/bash
set -e

mkdir -p /var/www/storage/framework/views \
         /var/www/storage/framework/cache \
         /var/www/storage/framework/sessions \
         /var/www/storage/logs \
         /var/www/bootstrap/cache

if [ ! -f /var/www/storage/database.sqlite ]; then
    touch /var/www/storage/database.sqlite
    chmod 666 /var/www/storage/database.sqlite
fi

if [ ! -d /var/www/vendor ]; then
    composer install --no-dev --optimize-autoloader
fi

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force
php artisan storage:link --force 2>/dev/null || php artisan storage:link 2>/dev/null || true

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
