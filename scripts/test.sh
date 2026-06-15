#!/usr/bin/env bash
set -euo pipefail

docker compose exec backend php artisan db:ensure-test-database --no-interaction
docker compose exec backend php artisan test "$@"
