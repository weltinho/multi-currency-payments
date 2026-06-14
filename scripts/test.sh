#!/usr/bin/env bash
docker compose exec backend php artisan test "$@"
