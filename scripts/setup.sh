#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Multi-Currency Payments — Docker setup"
echo "    (.env files are committed; no copy step needed)"
echo ""
echo "==> Starting services (bootstrap runs in the backend entrypoint)..."
docker compose up -d --build

echo ""
echo "Setup started. First boot may take a few minutes."
echo "  Status:   docker compose ps"
echo "  Logs:     docker compose logs -f frontend"
echo "  App:      http://localhost:8080"
echo "  API docs: http://localhost:8080/docs/api"
echo ""
echo "Login: finance@buzzvel.com / 123456"
echo "       (open Test instructions on the login screen for more accounts)"
