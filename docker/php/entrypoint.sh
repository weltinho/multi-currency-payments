#!/bin/sh
# Laravel API entrypoint — runs before php-fpm (or any overridden command).
#
# 1. Installs Composer dependencies if vendor/ is missing or outdated.
# 2. Generates APP_KEY if .env exists but the key is empty.
# 3. Runs migrations and seeds demo users when the database is empty.
# 4. Hands off to the CMD (default: php-fpm).

set -e

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
  echo "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
else
  echo "Composer dependencies already installed."
fi

if [ -f .env ] && grep -q "APP_KEY=$" .env 2>/dev/null; then
  php artisan key:generate --force
fi

echo "Running migrations..."
php artisan migrate --force

echo "Ensuring demo data is seeded..."
php artisan db:ensure-seeded --no-interaction

exec "$@"
