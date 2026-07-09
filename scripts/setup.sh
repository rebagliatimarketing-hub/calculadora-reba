#!/usr/bin/env sh
set -eu

if [ ! -f .env ]; then
  cp .env.example .env
fi

composer install
php artisan key:generate --force
php artisan migrate --seed --force
npm install
npm run build

echo "Sistema listo en http://localhost:8000"
