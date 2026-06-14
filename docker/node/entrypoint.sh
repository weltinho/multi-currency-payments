#!/bin/sh
# Seed node_modules from the image when the bind-mounted volume is empty.
set -e

if [ ! -f node_modules/.modules.yaml ]; then
  echo "Seeding node_modules from image..."
  rm -rf node_modules
  mkdir -p node_modules
  cp -a /opt/node_modules/. node_modules/
fi

exec "$@"
