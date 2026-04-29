#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.example .env
fi

until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent; do
  echo "Aguardando banco de dados..."
  sleep 3
done

php artisan config:clear --no-interaction || true
php artisan cache:clear --no-interaction || true
php artisan package:discover --ansi --no-interaction
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

exec "$@"
