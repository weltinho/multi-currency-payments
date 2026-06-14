#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Setting up Multi-Currency Payments"

if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

if [ ! -f backend/.env ]; then
  cp backend/.env.example backend/.env
  echo "Created backend/.env from backend/.env.example"
fi

echo "==> Starting Docker services..."
docker compose up -d --build

echo "==> Waiting for MySQL..."
sleep 10

echo "==> Bootstrapping Laravel..."
docker compose exec backend php artisan key:generate --force
docker compose exec backend php artisan migrate --seed

echo ""
echo "Setup complete!"
echo "  App:      http://localhost:8080"
echo "  API docs: http://localhost:8080/docs/api"
echo ""
echo "Login: finance@buzzvel.com / password"
