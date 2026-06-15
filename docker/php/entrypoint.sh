#!/bin/sh
# Laravel API entrypoint — runs before php-fpm (or any overridden command).
#
# Bootstrap (APP_BOOTSTRAP=true, backend only):
#   1. Installs Composer dependencies if vendor/ is missing (with a file lock).
#   2. Generates APP_KEY if .env exists but the key is empty.
#   3. Runs migrations and seeds demo users when the database is empty.
#
# Non-bootstrap (scheduler): waits for vendor/autoload.php, then hands off to CMD.
# Both services share ./backend on the host — only one process may run composer install.

set -e

cd /var/www/html

LOCK_DIR="/var/www/html/storage/framework/.composer-install.lock"
VENDOR_READY="/var/www/html/vendor/autoload.php"

wait_for_vendor() {
  if [ -f "$VENDOR_READY" ]; then
    return 0
  fi

  echo "Waiting for Composer dependencies (bootstrap runs in backend)..."
  attempts=0
  while [ ! -f "$VENDOR_READY" ]; do
    attempts=$((attempts + 1))
    if [ "$attempts" -gt 120 ]; then
      echo "Timed out waiting for vendor/autoload.php after 4 minutes."
      exit 1
    fi
    sleep 2
  done
  echo "Composer dependencies ready."
}

wait_for_database() {
  echo "Waiting for database connection..."
  attempts=0
  while [ $attempts -lt 60 ]; do
    # PDO ping only — db:show can exit 1 when intl is missing even if MySQL is up.
    if php -r '
      $host = getenv("DB_HOST") ?: "127.0.0.1";
      $port = getenv("DB_PORT") ?: "3306";
      $db = getenv("DB_DATABASE") ?: "payments";
      $user = getenv("DB_USERNAME") ?: "payments";
      $pass = getenv("DB_PASSWORD") ?: "secret";
      try {
        new PDO(
          "mysql:host={$host};port={$port};dbname={$db}",
          $user,
          $pass,
          [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        exit(0);
      } catch (Throwable $e) {
        exit(1);
      }
    ' >/dev/null 2>&1; then
      echo "Database connection ready."
      return 0
    fi
    attempts=$((attempts + 1))
    echo "Database not ready (attempt $attempts/60)..."
    sleep 2
  done
  echo "Timed out waiting for database after 2 minutes."
  exit 1
}

composer_install() {
  if [ -f "$VENDOR_READY" ]; then
    echo "Composer dependencies already installed."
    return 0
  fi

  mkdir -p "$(dirname "$LOCK_DIR")"

  echo "Acquiring Composer install lock..."
  while ! mkdir "$LOCK_DIR" 2>/dev/null; do
    if [ -f "$VENDOR_READY" ]; then
      echo "Composer dependencies installed by another process."
      return 0
    fi
    sleep 2
  done

  trap 'rmdir "$LOCK_DIR" 2>/dev/null || true' EXIT INT TERM

  if [ -f "$VENDOR_READY" ]; then
    rmdir "$LOCK_DIR" 2>/dev/null || true
    trap - EXIT INT TERM
    echo "Composer dependencies already installed."
    return 0
  fi

  echo "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist --optimize-autoloader

  rmdir "$LOCK_DIR" 2>/dev/null || true
  trap - EXIT INT TERM
}

if [ "${APP_BOOTSTRAP:-false}" = "true" ]; then
  composer_install

  if [ -f .env ] && grep -q "APP_KEY=$" .env 2>/dev/null; then
    php artisan key:generate --force
  fi

  wait_for_database

  echo "Running migrations..."
  php artisan migrate --force

  echo "Ensuring demo data is seeded..."
  php artisan db:ensure-seeded --no-interaction

  echo "Ensuring test database exists..."
  php artisan db:ensure-test-database --no-interaction
else
  wait_for_vendor
  wait_for_database
fi

exec "$@"
