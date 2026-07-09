#!/usr/bin/env sh
set -eu

composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deploy preparado."
