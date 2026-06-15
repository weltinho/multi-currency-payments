# Multi-Currency Payment

Buzzvel 2026 Dev Team Test — corporate multi-currency payment request system.

Employees submit payment requests in their local currency. Exchange rates are fetched at creation time and stored immutably. The finance team reviews and approves or rejects requests.

## Architecture

```
Browser (:8080)
    └── webserver (Nginx gateway)
            ├── /           → frontend
            ├── /api        → backend (Laravel PHP-FPM)
            ├── /sanctum    → backend (Sanctum CSRF)
            └── /docs       → backend (Scramble docs)
    └── database (MySQL)
    └── redis
    └── scheduler (Laravel schedule:work — 48h expiration planned)
```

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Docker Engine + Compose v2)

No need to install PHP, Node, Composer, or pnpm on the host — everything runs in containers.

## Environment files (committed for reviewers)

This is a **test submission**, so `.env` and `backend/.env` are **committed to the repository** on purpose. That is not recommended for production, but it lets reviewers run the stack without copying example files or hunting for API keys.

| File | Purpose |
|---|---|
| `.env` | Docker Compose (port, MySQL credentials) |
| `backend/.env` | Laravel app config, including `EXCHANGE_RATE_API_KEY` |

After clone, you should not need to edit anything unless you want to change the port or use your own exchange-rate API key.

## Quick start

```bash
git clone <your-repo-url>
cd multi-currency-payments
docker compose up -d --build
```

**First boot can take a few minutes** (Composer install, Laravel migrate/seed, Next.js build). Wait until all containers are healthy:

```bash
docker compose ps
```

Then open:

| URL | Purpose |
|---|---|
| http://localhost:8080 | UI |
| http://localhost:8080/docs/api | Interactive API docs (Scramble) |
| http://localhost:8080/api/health | Health check |

### What happens automatically on first start

The `backend` container bootstraps the API:

1. `composer install` (if `vendor/` is missing)
2. `php artisan key:generate` (if `APP_KEY` is empty)
3. `php artisan migrate --force`
4. `php artisan db:ensure-seeded` (demo users when the database is empty)

The `scheduler` container waits for `vendor/` and then runs `php artisan schedule:work`.

**You do not need** to run `key:generate` or `migrate --seed` manually for a normal Docker setup.

### Optional setup script

```bash
./scripts/setup.sh
```

This only starts Docker — bootstrap still runs inside the `backend` entrypoint.

## Seed credentials

Password for **all** seeded users: `123456`

On the login screen, open **Test instructions** to see the full list of finance and employee accounts.

| Email | Role | Country | Currency |
|---|---|---|---|
| `finance@buzzvel.com` | finance | Portugal | EUR |
| `rafael@buzzvel.com` | employee | Brazil | BRL |
| `emily@buzzvel.com` | employee | United States | USD |
| `oliver@buzzvel.com` | employee | United Kingdom | GBP |
| `yuki@buzzvel.com` | employee | Japan | JPY |

More users are seeded — see `backend/database/seeders/UserSeeder.php` or `GET /api/test-users`.

## Exchange rate API

Payment creation fetches a live EUR → local rate from [ExchangeRate-API v6](https://www.exchangerate-api.com/). A key is already set in `backend/.env` (`EXCHANGE_RATE_API_KEY`).

The amount shown in the form is a **reference estimate** only; the locked rate is fetched when you submit.

To use your own key, edit `backend/.env` and restart:

```bash
docker compose restart backend scheduler
```

## Running tests

```bash
docker compose exec backend php artisan test
```

## Project structure

```
├── backend/          Laravel 12 API (Sanctum, Scramble, tests)
├── frontend/         Next.js UI (employee + finance dashboards)
├── docker/           Container configs (nginx, php, node, mysql)
├── docs/             API documentation and architecture notes
├── scripts/          Setup helpers
└── docker-compose.yml
```

Reviewer-focused backend notes: [backend/REVIEWER_NOTES.md](backend/REVIEWER_NOTES.md)

## API documentation

- **Interactive (Scramble):** http://localhost:8080/docs/api
- **Static reference:** [docs/api.md](docs/api.md)
- **Architecture:** [docs/architecture.md](docs/architecture.md)

## Useful commands

```bash
# Logs (all services or one)
docker compose logs -f
docker compose logs -f backend

# Stop
docker compose down

# Rebuild after Dockerfile / entrypoint changes
docker compose up -d --build

# Artisan
docker compose exec backend php artisan <command>
```

## Troubleshooting

**`pull access denied` for `multi-currency/*` images**

Harmless on first run — Docker tries to pull local image names, then builds them from this repo.

**Frontend stuck on “Waiting”**

Normal on first boot while Next.js builds. Check progress with `docker compose logs -f frontend` (allow 2–3 minutes).

**Composer / `vendor/` errors on first start**

If `backend` and `scheduler` previously raced on `composer install`, reset vendor and rebuild:

```bash
docker compose down
rm -rf backend/vendor
docker compose up -d --build
```

**Payment submit fails — exchange rate unavailable**

Check `EXCHANGE_RATE_API_KEY` in `backend/.env` and restart `backend`. With `APP_DEBUG=true`, the API returns a more specific error message.

**Completely fresh database**

```bash
docker compose down --remove-orphans -v
docker compose up -d --build
```

## Development notes

- The frontend talks to the API via same-origin paths (`/api`, `/sanctum`) through Nginx — use **http://localhost:8080**, not port 3000 directly.
- `backend/vendor/` is gitignored and installed inside Docker on first boot.
- For standalone frontend dev without Nginx, see [frontend/README.md](frontend/README.md).

## License

Private — Buzzvel 2026 Dev Team Test submission.
