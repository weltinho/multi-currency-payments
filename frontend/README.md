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

The frontend runs inside the `frontend` service. Access the app through Nginx at `http://localhost:8080`.
