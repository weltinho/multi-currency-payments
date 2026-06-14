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
    └── scheduler (expire pending payments after 48h)
```

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Docker Engine + Compose)

## Quick start

```bash
# 1. Clone and configure
git clone <your-repo-url>
cd multi-currency-payments   # or your local clone path
cp .env.example .env
cp backend/.env.example backend/.env

# 2. Start all services
docker compose up -d --build

# 3. Bootstrap the API
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed

# 4. Open the app
#    UI:          http://localhost:8080
#    API docs:    http://localhost:8080/docs/api
#    Health:      http://localhost:8080/api/health
```

## Seed credentials

| Email | Role | Country | Currency |
|---|---|---|---|
| `finance@buzzvel.com` | finance | Portugal | EUR |
| `rafael@buzzvel.com` | employee | Brazil | BRL |
| `emily@buzzvel.com` | employee | United States | USD |
| `oliver@buzzvel.com` | employee | United Kingdom | GBP |
| `yuki@buzzvel.com` | employee | Japan | JPY |

Password for all seed users: `123456`

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

## API documentation

- **Interactive (Scramble):** http://localhost:8080/docs/api
- **Static reference:** [docs/api.md](docs/api.md)
- **Architecture:** [docs/architecture.md](docs/architecture.md)

## Useful commands

```bash
# View logs
docker compose logs -f

# Stop all services
docker compose down

# Rebuild after Dockerfile changes
docker compose up -d --build

# Run artisan commands
docker compose exec backend php artisan <command>
```

## Development notes

- The frontend always talks to the Laravel API via same-origin paths (`/api`, `/sanctum`). Nginx routes them to the API container.
- For standalone frontend dev without Nginx, see [frontend/README.md](frontend/README.md).

## License

Private — Buzzvel 2026 Dev Team Test submission.
