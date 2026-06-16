#!/bin/sh
# Start Next.js only after the backend is healthy (migrate + seed + PHP-FPM).
# Docker marks the container healthy once Next.js responds AND backend :9000 is reachable.
set -e

if [ ! -f .next/BUILD_ID ]; then
  echo "Building Next.js for production (first run or missing .next cache)..."
  ./node_modules/.bin/next build
else
  echo "Next.js production build found — skipping build."
fi
echo "Starting Next.js production server..."
exec ./node_modules/.bin/next start
