# Laravel API

Laravel 12 backend for the multi-currency payment request system.

**Reviewers:** see [REVIEWER_NOTES.md](REVIEWER_NOTES.md) for intentional design decisions before reading the code.

## Structure

```
app/
├── Console/Commands/     # ExpirePendingPayments, AppReady
├── Contracts/            # Service/repository interfaces
├── Enums/                # UserRole, PaymentStatus
├── Http/
│   ├── Controllers/      # API endpoints
│   ├── Middleware/       # Locale, forced password change
│   └── Requests/         # Form request validation
├── Models/               # User, Payment
├── OpenApi/              # Scramble schema markers
├── Repositories/         # Eloquent data access
├── Services/             # Auth, payments, employees, exchange rates
└── Support/              # Scramble config, locale, OpenAPI examples
```

Authorization for payments lives in `PaymentService` (explicit, unit-tested). There is no separate policy layer.

## Packages

- **Laravel Sanctum** — SPA cookie authentication
- **Scramble** — interactive OpenAPI docs at `/docs/api`

## Docker commands

```bash
docker compose exec backend composer install
docker compose exec backend php artisan migrate --seed
docker compose exec backend php artisan test
./scripts/test.sh              # MySQL (payments_test)
./scripts/test-sqlite.sh       # in-memory SQLite
```

## API documentation

Interactive docs (Scramble): `http://localhost:8080/docs/api`

Static reference: `../docs/api.md`
