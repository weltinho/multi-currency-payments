#!/usr/bin/env bash
set -euo pipefail

docker compose exec backend php artisan test --configuration=phpunit.sqlite.xml "$@"
