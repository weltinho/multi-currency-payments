# Backend — notes for reviewers (Buzzvel 2026 test)

Short guide to intentional design choices. Start here, then follow the file-level comments in the codebase.

## Architecture

**Pattern:** thin Controllers → Services (business logic) → Repositories / Eloquent models.

**Contracts** (`app/Contracts/*`) exist so unit tests can mock dependencies without hitting HTTP or the database. Bindings live in `AppServiceProvider`.

**Domain exceptions** (`ForbiddenException`, `NotFoundException`, `ConflictException`, `ExchangeRateException`) carry translation keys; controllers map them to HTTP status codes and localized messages via `TranslatorContract`.

## Authentication

- **Laravel Sanctum** cookie SPA (same-origin via Nginx), not Passport — fits the Next.js frontend without storing tokens in JavaScript.
- **Login / logout** only for end users. There is no public self-registration.
- **Registration** is implemented as **finance-provisioned employee accounts** (`POST /api/employees`). Finance creates employee users; initial password is the employee's **first name**; `must_change_password` forces a password update on first login (`PUT /api/password`).
- Demo credentials: `finance@buzzvel.com` / `123456` (all seeded users share `123456`). `GET /api/test-users` lists them for the login modal.

## Payments & exchange rates

- Single table `payment_requests` — rate and EUR amount are **frozen at creation** (Buzzvel requirement). No separate `exchange_rates` cache table.
- **Formula:** `eur_amount = local_amount / exchange_rate` where the rate is EUR → local from ExchangeRate-API v6 (`EXCHANGE_RATE_API_KEY` in `.env`).
- **Currency** on a payment comes from the employee's profile at create time, not from the request body (prevents tampering).
- **Authorization:** employees see/create only their own requests; finance sees all, filters by status/collaborator, and approves/rejects pending items (`409` if not pending).
- `user_name` / `country` are **not** denormalized on the payment row — joined from `users` at serialize time (`Payment::toApiArray()`).

## Localization

- Client sends `X-App-Language` (mirrors UI locale). `SetLocaleFromRequest` middleware sets the app locale before validation and translated API messages (`lang/*/messages.php`).

## Demo data

- `UserSeeder`: 3 finance + 20 employees across countries/currencies.
- `PaymentSeeder`: at least one pending request per employee, timestamped **47h55m** ago so the scheduler expires them shortly after `docker compose up`. Extra showcase rows cover other statuses.
- Docker entrypoint runs `migrate` + `db:ensure-seeded` when the database has no users.

## Payment expiration (48h)

- Pending requests older than **48 hours** are marked `expired` by `php artisan payments:expire-pending`.
- Registered in `routes/console.php` to run **every minute**; the `scheduler` container runs `php artisan schedule:work` (no host cron).
- Window is configurable via `PAYMENT_PENDING_EXPIRATION_HOURS` / `config/payments.php`.
- Expiration does **not** set `reviewed_at` — finance never acted on these.
- **Why command, not Job?** See root [README.md](../README.md#payment-expiration-48h) — batch sweeper fits the global 48h rule and keeps Docker simple (no dedicated queue worker).

## Tests

```bash
docker compose exec backend php artisan test
```

Feature tests use `RefreshDatabase` against **`payments_test`** only (see `phpunit.xml`). The demo database is `payments`.

Feature tests cover auth, employee registration, payment create/approve, expiration, and locale. Unit tests mock contracts for `PaymentService`, `AuthService`, `EmployeeService`, and `ExchangeRateService`.
