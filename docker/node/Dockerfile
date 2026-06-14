# Next.js frontend — Node 22 with pnpm
#
# Dependencies are installed at IMAGE BUILD time (pnpm install).
# At runtime, ./frontend is bind-mounted for hot reload; node_modules
# is preserved via a volume seeded from /opt/node_modules in entrypoint.sh.
#
# Build context is the repo root (see docker-compose.yml).

FROM node:22-alpine

# Pin pnpm 9 — v10+ blocks lifecycle scripts unless explicitly approved,
# which breaks `pnpm dev` inside Docker on every container start.
RUN corepack enable && corepack prepare pnpm@9.15.9 --activate

WORKDIR /app

# Install dependencies first (cached layer — only re-runs when lockfile changes)
COPY frontend/package.json frontend/pnpm-lock.yaml frontend/.npmrc ./
RUN pnpm install --frozen-lockfile \
    && cp -a node_modules /opt/node_modules

# Copy application source
COPY frontend/ .

COPY docker/node/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/node/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/start.sh

EXPOSE 3000

ENTRYPOINT ["entrypoint.sh"]
# Build synchronously, then start dev — see start.sh
CMD ["/usr/local/bin/start.sh"]
