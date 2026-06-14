#!/bin/sh
# Start Next.js only after a production build is available.
# Docker marks the container as "started" once `next dev` is running;
# the healthcheck below waits until HTTP responds on :3000.
set -e

if [ ! -f .next/BUILD_ID ]; then
  echo "Building Next.js (first run or missing .next cache)..."
  ./node_modules/.bin/next build --webpack
else
  echo "Next.js build cache found — skipping build."
fi

echo "Starting Next.js dev server..."
exec ./node_modules/.bin/next dev --webpack
