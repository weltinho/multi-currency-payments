# Laravel API

Laravel 12 backend for the multi-currency payment request system.

**Reviewers:** see [REVIEWER_NOTES.md](REVIEWER_NOTES.md) for intentional design decisions before reading the code.

## Planned structure

```
app/
├── Console/Commands/     # ExpirePendingPayments command
├── Enums/                # Role, PaymentStatus, CurrencyCode
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Middleware/       # Role-based access
│   ├── Requests/         # Form request validation
│   └── Resources/        # API response transformers
├── Models/               # User, Payment
├── Policies/             # Payment authorization
└── Services/             # ExchangeRateService
```

## Packages

- **Laravel Sanctum** — SPA cookie authentication
- **Scramble** — interactive OpenAPI docs at `/docs/api`

## Docker commands

```bash
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed
docker compose exec backend php artisan test
```

## API documentation

Interactive docs (Scramble): `http://localhost:8080/docs/api`

Static reference: `../docs/api.md`
