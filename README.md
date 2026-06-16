# Multi-Currency Payment

Buzzvel 2026 Dev Team Test — corporate multi-currency payment request system.

Employees submit payment requests in their local currency. Exchange rates are fetched at creation time and stored immutably. The finance team reviews and approves or rejects requests.

## Run locally

```bash
docker compose up -d --build
```

Then open:

- http://localhost:8080
- http://localhost:8080/docs/api

**For reviewers:** [backend/REVIEWER_NOTES.md](backend/REVIEWER_NOTES.md) — brief checklist, architecture decisions, and code pointers. API reference: [docs/api.md](docs/api.md).

**Live deployment:** https://welton-buzzvel.duckdns.org (same stack as below, configured on the host).

> **For Buzzvel reviewers — intentional shortcuts**  
> I **know** that committing `.env` files and exposing `GET /api/test-users` is **not** acceptable in a real production system. **This submission keeps both on purpose** so you can run `docker compose up` and log in without copying secrets, hunting API keys, or digging through seeders. I am **not** changing that for this test — reviewer experience comes first here. See [Intentional reviewer conveniences](#intentional-reviewer-conveniences-not-production-practice) and [Beyond the test submission](#beyond-the-test-submission) for what I would do in production instead.

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
    └── scheduler (runs payments:expire-pending every 15 seconds via schedule:work)
```

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Docker Engine + Compose v2)

No need to install PHP, Node, Composer, or pnpm on the host — everything runs in containers.

## Intentional reviewer conveniences (not production practice)

**Deliberate choices for this Buzzvel test only.** I would never ship a real product this way — but I want the person grading this to spend time on the app, not on setup friction.

| What | Why it is in the repo | What I know is “correct” in production |
|------|------------------------|----------------------------------------|
| **Committed `.env` and `backend/.env`** | Clone → `docker compose up` — no `cp .env.example`, no missing `EXCHANGE_RATE_API_KEY`, no `APP_KEY` hunt | Secrets in a vault or CI; `.env` gitignored; rotate keys per environment |
| **`GET /api/test-users` + login “Test instructions” modal** | One click / one request to see all seeded finance and employee emails; password `123456` documented in Scramble | Remove the route or protect it; no public account enumeration |
| **Demo password `123456` for seeded users** | Fast login while reviewing employee vs finance flows | Strong passwords; no shared demo secret in a live system |

**I am keeping these as-is for this submission.** The [Beyond the test submission](#beyond-the-test-submission) section describes production alternatives — that is a roadmap, not a to-do list for this repo.

### Environment files (committed)

| File | Purpose |
|---|---|
| `.env` | Docker Compose (port, MySQL credentials) |
| `backend/.env` | Laravel app config (`APP_URL`, Sanctum, exchange-rate API key) |

`APP_URL` and Sanctum domains are set for **http://localhost:8080** (Docker gateway). Override on the host for production (e.g. `https://welton-buzzvel.duckdns.org`) — Scramble docs and Try It URLs follow `APP_URL` automatically after `docker compose restart backend`.

After clone, you should not need to edit anything unless you want your own exchange-rate API key.

## URLs (local Docker)

| URL | Purpose |
|---|---|
| http://localhost:8080 | UI |
| http://localhost:8080/docs/api | Interactive API docs (Scramble) |
| http://localhost:8080/api/health | Health check |

## Quick start

```bash
git clone https://github.com/weltinho/multi-currency-payments
cd multi-currency-payments
docker compose up -d --build
```

**First boot can take a few minutes** (Composer install, Laravel migrate/seed, Next.js build). Wait until **all** containers are running and healthy:

```bash
docker compose ps
```

You should see `backend`, `frontend`, and `database` as **healthy** before opening the UI. The stack is wired so the frontend does not start until the API has finished bootstrapping and demo data is seeded (`php artisan app:ready`).

### What happens automatically on first start

The `backend` container bootstraps the API:

1. `composer install` (if `vendor/` is missing)
2. `php artisan key:generate` (if `APP_KEY` is empty)
3. `php artisan migrate --force`
4. `php artisan db:ensure-seeded` (demo users and payments when data is missing)
5. `php artisan scramble:analyze` + `scramble:export` (OpenAPI docs validated at bootstrap)
6. Writes `storage/framework/.bootstrap-complete` and exposes a healthcheck (`php artisan app:ready`)

The `frontend` container waits for `backend` to be **healthy** before starting Next.js. The `webserver` waits for both `frontend` and `backend` to be healthy, then exposes port 8080 only when `/api/health` **and** `/docs/api` (Scramble) respond — so the UI and interactive API docs are guaranteed on `docker compose up`.

The `scheduler` container also waits for `backend` to be healthy, then runs `php artisan schedule:work` (expires pending payments strictly older than 48 hours every 15 seconds).

**You do not need** to run `key:generate` or `migrate --seed` manually for a normal Docker setup.

## Payment expiration (48h)

Pending requests that finance does not approve or reject within **48 hours** are marked `expired` automatically.

### How it works

1. The `scheduler` container runs `php artisan schedule:work`.
2. Every 15 seconds it runs `php artisan payments:expire-pending`.
3. That command updates all `pending` rows **strictly older than 48 hours** (`created_at + 48h < now`) to `expired`, and sets `updated_at` to `created_at + 48h` for accurate display.

Demo seed data includes one pending payment per employee **just under the configured window** (default 47h54m) so reviewers can see expirations within **~6 minutes** of `docker compose up`, without waiting two days.

**Local testing:** set `PAYMENT_PENDING_EXPIRATION_HOURS=1` in `backend/.env`, then `docker compose restart backend scheduler`. Re-seed if needed (`docker compose exec backend php artisan db:seed --class=PaymentSeeder`). Pending demo rows will expire ~6 minutes after creation.

### Why a scheduled command instead of a queued Job?
I considered dispatching a delayed `ExpirePaymentJob` when each payment is created (`->delay(48 hours)`). We chose a **scheduled Artisan command** that scans the database instead:

| | Scheduled command (what we built) | Delayed Job per payment |
|---|---|---|
| **Fits the rule** | “Expire everything pending older than 48h” is naturally a batch query | Better when each payment needs its own exact expiry moment |
| **Docker setup** | Only needs the existing `scheduler` container | Would also need a `queue:work` process always running |
| **Resilience** | If the scheduler is down for a while, the next run catches all overdue rows | Depends on Redis, worker uptime, and job retries |
| **Seeded / backfilled data** | Works immediately for old `created_at` values | Only expires payments that had a job dispatched at create time |
| **Changing the window** | Update `PAYMENT_PENDING_EXPIRATION_HOURS` — next run uses the new cutoff | Already-queued jobs keep their old delay |

For this test project the rule is simple, the volume is small, and we wanted reviewers to run `docker compose up` without extra moving parts. A command plus scheduler is the usual Laravel pattern for this kind of housekeeping.

See [Beyond the test submission](#beyond-the-test-submission) for how I would harden expiry and other demo shortcuts in a real deployment.

Configuration: `config/payments.php` / `PAYMENT_PENDING_EXPIRATION_HOURS` in `backend/.env`.

### Optional setup script

```bash
./scripts/setup.sh
```

This only starts Docker — bootstrap still runs inside the `backend` entrypoint.

## Authentication & registration (Buzzvel brief)

The brief requires **Registration, Login, and Logout**. This project uses **Laravel Sanctum** (session cookies), not Passport — allowed by the brief (“or another preferred mechanism”).

| Brief requirement | Implementation |
|-------------------|----------------|
| **Login** | `POST /api/login` |
| **Logout** | `POST /api/logout` |
| **Registration** | `POST /api/employees` (**finance only**) — corporate HR provisions employee accounts; there is no public self-signup |

New employees get `must_change_password: true` and must call `PUT /api/password` on first login (initial password = first name). Seeded demo users use `123456` for easy review.

**Recommended demo flow for reviewers:** finance registers an employee → employee logs in → password change → submit payment → finance approves.



## Demo video

**https://www.loom.com/share/af8e0ab31dd7478184360fb58dfc4d8b**


## Seed credentials (demo only — for reviewers)

Password for **all** seeded users: `123456`

On the login screen, open **Test instructions** to see the full list of finance and employee accounts (backed by `GET /api/test-users`). **This endpoint exists only to help you test** — it is documented as demo-only in Scramble and would be removed or locked down in production.

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

The amount shown in the form is a **live preview** polled every 30 seconds from the same Redis cache used at submission; the locked rate is fetched when you submit.

To use your own key, edit `backend/.env` and restart:

```bash
docker compose restart backend scheduler
```

## Running tests

PHPUnit uses a **separate MySQL database** (`payments_test`), not the demo `payments` database. `RefreshDatabase` can safely run `migrate:fresh` during tests without wiping seeded users or payments.

```bash
./scripts/test.sh
# or
docker compose exec backend php artisan db:ensure-test-database
docker compose exec backend php artisan test
```

**Critical subset** (payments + expiration):

```bash
docker compose exec backend php artisan test --filter='PaymentExpiration|PaymentService|PaymentImmutability|ExpirePendingPayments|PaymentCreate'
```

**SQLite (in-memory, no MySQL setup):**

```bash
./scripts/test-sqlite.sh
# or
docker compose exec backend php artisan test --configuration=phpunit.sqlite.xml
```

On first boot, the entrypoint creates `payments_test` automatically. If you already had a Docker volume before this was added, run `db:ensure-test-database` once (or recreate the volume with `docker compose down -v && docker compose up -d`).

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

- **Interactive (Scramble):** http://localhost:8080/docs/api (live: https://welton-buzzvel.duckdns.org/docs/api)
- **Static reference:** [docs/api.md](docs/api.md)
- **Architecture:** [docs/architecture.md](docs/architecture.md)
- **Demo video:** https://www.loom.com/share/af8e0ab31dd7478184360fb58dfc4d8b ([docs/demo.md](docs/demo.md))

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

Check `EXCHANGE_RATE_API_KEY` in `backend/.env` and restart `backend`. With `APP_DEBUG=true` (local only), the API returns a more specific error message.

**Completely fresh database**

```bash
docker compose down --remove-orphans -v
docker compose up -d --build
```

## Development notes

- The frontend talks to the API via same-origin paths (`/api`, `/sanctum`) through Nginx — not port 3000 directly.
- `backend/vendor/` is gitignored and installed inside Docker on first boot.
- For standalone frontend dev without Nginx, see [frontend/README.md](frontend/README.md).

## Beyond the test submission

**Nothing below changes this repo.** It maps what stays **on purpose** for reviewers versus what I would build in a real rollout.

Several rows in the table are **not oversights** — committed secrets and `GET /api/test-users` are [intentional reviewer conveniences](#intentional-reviewer-conveniences-not-production-practice) that I chose to keep for this test.

| Area | This repo (test) — **kept on purpose** | Production |
|------|----------------------------------------|------------|
| **Secrets & config** | `.env` committed so reviewers need zero secret setup | Secrets manager or CI injection; `.env` gitignored; `APP_DEBUG=false`; HTTPS session cookies |
| **Demo API surface** | `GET /api/test-users` + login “Test instructions” modal — **helps graders log in fast** | Remove or protect the route; hide the modal |
| **API docs** | Scramble public at `/docs/api` | `RestrictedDocsAccess` in production — gate with `viewApiDocs`, VPN, or basic auth |
| **Passwords** | Six-digit demo policy (`PasswordPolicy`, `frontend/lib/password-policy.ts`) | Stronger rules (length, complexity, breach checks) in the same two files |
| **Employee onboarding** | Initial password = first name | Random secret delivered out-of-band (email, IdP) |
| **Payment expiry** | Scheduled sweeper every 15s | Lookahead command + delayed `ExpirePaymentJob` per payment + hourly fallback sweeper + `queue:work` (see below) |
| **Exchange rates** | Single provider (ExchangeRate-API v6) + Redis cache | Multi-provider price oracle + immutable per-payment snapshot (see below) |
| **Authorization** | Rules in `PaymentService` (unit-tested) | Laravel Policies if the rule set grows; keep contracts/repos for external HR integrations |

### Payment expiry (production detail)

For second-precise expiry:

1. **Lookahead command** (e.g. every minute) — pending payments expiring in the next ~2 minutes get an `ExpirePaymentJob` delayed to `created_at + 48h`; the job re-checks status before expiring.
2. **Fallback sweeper** — `payments:expire-pending` on a slower schedule (e.g. hourly) for missed jobs, worker downtime, or backfilled rows.
3. **`queue:work`** — dedicated worker container (Redis is already in Compose).

### Exchange rates (production detail)

Keep the current **immutable snapshot** on each payment (`exchange_rate`, `rate_source`, `rate_fetched_at`, `eur_amount`). Do not overwrite `rate_fetched_at` with `created_at` when cache reuses an earlier fetch.

On top of that:

- **Price oracle** — fetch several APIs in parallel (e.g. five), discard outliers, aggregate (median or trimmed mean), cache, and record which providers contributed on the payment row.
- **Optional `exchange_rate_snapshots` table** — full fetch log if compliance needs more than the row-level audit fields.

## License

Private — Buzzvel 2026 Dev Team Test submission.
