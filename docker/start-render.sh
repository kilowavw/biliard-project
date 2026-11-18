#!/usr/bin/env sh
set -eu

PORT="${PORT:-10000}"

sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/*.conf

php artisan config:cache
php artisan migrate --force
php artisan db:seed --force
php artisan view:cache

exec apache2-foreground
