# Next.js frontend

Corporate multi-currency payment UI. Talks to the Laravel API via same-origin paths (`/api`, `/sanctum`).

## Standalone development (without Docker)

```bash
cp .env.example .env.local
pnpm install
pnpm dev
```

Set `API_URL=http://localhost:8000` in `.env.local` if the Laravel API runs on a different port.

## With Docker

Dependencies are installed **during `docker compose build`** — no manual `pnpm install` needed.

**Local (Docker):** `http://localhost:8080` when using Docker Compose with `APP_PORT=8080`.

**Live:** https://welton-buzzvel.duckdns.org (same-origin `/api` and `/sanctum` via host Nginx).
