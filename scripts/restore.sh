#!/usr/bin/env sh
set -eu

if [ $# -lt 1 ]; then
  echo "Uso: sh scripts/restore.sh ruta/al/backup.sql.gz"
  exit 1
fi

gzip -dc "$1" | mysql -h "${DB_HOST:-mysql}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-rd_user}" -p"${DB_PASSWORD:-secret}" "${DB_DATABASE:-rd_lanzamientos}"
php artisan optimize:clear
php artisan migrate --force

echo "Backup restaurado."
