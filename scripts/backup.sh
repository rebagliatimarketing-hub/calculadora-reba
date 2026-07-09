#!/usr/bin/env sh
set -eu

mkdir -p storage/app/backups
STAMP=$(date +"%Y%m%d_%H%M%S")
FILE="storage/app/backups/rd_lanzamientos_${STAMP}.sql"

mysqldump -h "${DB_HOST:-mysql}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-rd_user}" -p"${DB_PASSWORD:-secret}" "${DB_DATABASE:-rd_lanzamientos}" > "$FILE"
gzip "$FILE"
find storage/app/backups -type f -name "*.sql.gz" -mtime +14 -delete

echo "Backup creado: ${FILE}.gz"
