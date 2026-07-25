#!/bin/bash
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan cache:clear
php artisan migrate --force
frankenphp run --config /Caddyfile
