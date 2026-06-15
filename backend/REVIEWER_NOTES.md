# Backend — notes for reviewers (Buzzvel 2026 test)

Short guide mapping the Buzzvel brief to this repo. Start here, then follow file-level comments in the codebase.

## Brief checklist (backend)

| Requirement | How we satisfy it |
|-------------|-------------------|
| Laravel 12, PHP 8.2+ | Laravel **12** (`composer.lock`); Docker runs **PHP 8.4** |
| Auth (Passport or alternative) + Registration, Login, Logout | **Sanctum** SPA cookies; `POST /login`, `POST /logout`; **Registration** = finance provisions employees via `POST /api/employees` (no public self-signup — corporate onboarding) |
| Payment CRUD (create, read list/detail + status filter, approve/reject) | `POST/GET /payments`, `GET /payments/{id}`, `PATCH /payments/{id}`; finance vs employee scoping in `PaymentService` |
| Exchange rate at creation, immutable | Fetched once in `PaymentService::create()`; model guard blocks changes to economic fields |
| EUR → local rate, store rate + source + timestamp, return EUR amount | Columns `exchange_rate`, `rate_source`, `rate_fetched_at`; API returns `eur_amount`, `rate_source`, `rate_fetched_at` |
| Expire pending > 48h (scheduled) | `payments:expire-pending` every minute via `scheduler` container |
| Validation | FormRequests on all mutating endpoints |
| API docs + example responses | Scramble at `/docs/api` + [docs/api.md](../docs/api.md) |
| Unit tests (critical paths) | `tests/Unit/*` + feature tests; run `php artisan test` |
| Seeders (≥5 employees, multiple countries/currencies) | `UserSeeder`: 20 employees + 3 finance across many ISO profiles |

The **Next.js frontend** at `:8080` consumes this API (same-origin via Nginx). The brief asks for a Laravel application; the API is Laravel 12 — the UI is a separate container for a polished SPA experience.

## Architecture

**Pattern:** thin Controllers → Services (business logic) → Repositories / Eloquent models.

**Contracts** (`app/Contracts/*`) exist so unit tests can mock dependencies without hitting HTTP or the database. Bindings live in `AppServiceProvider`.

**Domain exceptions** (`ForbiddenException`, `NotFoundException`, `ConflictException`, `ExchangeRateException`) carry translation keys; controllers map them to HTTP status codes and localized messages via `TranslatorContract`.

## Authentication

- **Laravel Sanctum** cookie SPA (same-origin via Nginx), not Passport — acceptable per brief (“or another preferred mechanism”); fits the Next.js frontend without storing tokens in JavaScript.
- **Login / logout:** `POST /api/login`, `POST /api/logout` (session).
- **Registration:** not a public `POST /register`. Finance creates employee accounts with `POST /api/employees`. New employees get `must_change_password = true` and must call `PUT /api/password` on first login (initial password = first name from `EmployeeService`; seeded demo users use `123456` for easy review).
- Demo credentials: `finance@buzzvel.com` / `123456`. `GET /api/test-users` lists seeded accounts for the login modal (demo only).

## Payments & exchange rates

- Single table `payment_requests` — rate, source, timestamp, and EUR amount are **frozen at creation** (Buzzvel requirement). No separate `exchange_rates` history table.
- **Provider:** [ExchangeRate-API](https://www.exchangerate-api.com/) v6 (`EXCHANGE_RATE_API_KEY` in `.env`). Same provider family as the brief’s example; v6 uses an API key. Live rates are cached in Redis for `EXCHANGE_RATE_CACHE_TTL_SECONDS` (default 30s) to respect rate limits.
- **Formula:** `eur_amount = local_amount / exchange_rate` where `exchange_rate` is EUR → local (e.g. 1 EUR = 6.21 BRL).
- **Currency on create:** defaults to the employee’s profile currency; optional `currency` in the request body overrides with a supported ISO code (`StorePaymentRequest`).
- **Timestamps — two different meanings:**
  - `rate_fetched_at` — when the EUR→local **rate snapshot** was taken (HTTP call to ExchangeRate-API, or when that snapshot entered Redis cache). Usually **≤ `created_at`**. Multiple payments within the cache window can share the same `rate_fetched_at` even if submitted seconds apart.
  - `created_at` — when the payment row was persisted. Shown in the UI timeline as “created”.
  - We do **not** overwrite `rate_fetched_at` with `created_at` — that would misstate audit data when the cache reuses an earlier fetch.
- **API response** includes `exchange_rate`, `rate_source`, `rate_fetched_at`, and `eur_amount` alongside `local_amount` and `currency`.
- **Authorization:** employees see/create only their own requests; finance sees all, filters by status/collaborator, and approves/rejects pending items (`409` if not pending).
- `user_name` / `country` are **not** denormalized on the payment row — joined from `users` at serialize time (`Payment::toApiArray()`).

## Localization

- Client sends `X-App-Language` (mirrors UI locale). `SetLocaleFromRequest` middleware sets the app locale before validation and translated API messages (`lang/*/messages.php`).

## Demo data

- `UserSeeder`: 3 finance + 20 employees across countries/currencies.
- `PaymentSeeder`: at least one pending request per employee, timestamped just under the configured expiration window so the scheduler expires them shortly after `docker compose up`. Extra showcase rows cover other statuses.
- Docker entrypoint runs `migrate` + `db:ensure-seeded` when the database has no users.

## Payment expiration (48h)

- Pending requests older than **48 hours** (configurable) are marked `expired` by `php artisan payments:expire-pending`.
- Registered in `routes/console.php` to run **every minute**; the `scheduler` container runs `php artisan schedule:work` (no host cron).
- Window: `PAYMENT_PENDING_EXPIRATION_HOURS` / `config/payments.php`.
- Expiration does **not** set `reviewed_at` — finance never acted on these.
- **Why command, not Job?** See root [README.md](../README.md#payment-expiration-48h) — batch sweeper fits the global 48h rule and keeps Docker simple (no dedicated queue worker).

## Tests

```bash
docker compose exec backend php artisan test
./scripts/test.sh
./scripts/test-sqlite.sh   # in-memory SQLite alternative
```

Feature tests use `RefreshDatabase` against **`payments_test`** only (see `phpunit.xml`). The demo database is `payments`.

Feature tests cover auth, employee registration, payment create/approve, expiration, and locale. Unit tests mock contracts for `PaymentService`, `AuthService`, `EmployeeService`, and `ExchangeRateService`.

## Documentation

- **Interactive:** http://localhost:8080/docs/api (Scramble — Try It, example responses)
- **Static:** [docs/api.md](../docs/api.md)
- **Demo video:** [docs/demo.md](../docs/demo.md) (add link before submission)
