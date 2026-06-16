# API Reference

> **Interactive docs:** http://localhost:8080/docs/api (Scramble — testable from the browser)  
> **Live:** https://welton-buzzvel.duckdns.org/docs/api

This document provides a static reference. The Scramble UI is the primary testable documentation for reviewers.

## Base URL

```
http://localhost:8080/api
```

All authenticated endpoints require a valid Sanctum session cookie.

## Authentication

### Get CSRF cookie

```
GET /sanctum/csrf-cookie
```

Full URL: `http://localhost:8080/sanctum/csrf-cookie` (site root — **not** under `/api`).

No request body. Returns **204 No Content** (empty response body is normal) and sets the `XSRF-TOKEN` cookie required for `POST`/`PUT`/`PATCH`.

**After 204, proceed** to login or the next mutating request on the same docs tab — e.g. **Public → auth.login** in Scramble Try It.

**Try It (Scramble):** open **Public → Get CSRF cookie**, click **Send API Request** (nothing to fill), wait for **204**, then go to **auth.login** or any mutating endpoint.

---

### Login

```
POST /api/login
```

**Request body:**

```json
{
  "email": "finance@buzzvel.com",
  "password": "123456"
}
```

**Response `200`:**

```json
{
  "message": "Authenticated"
}
```

**Response `422`:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

### Get current user

```
GET /api/user
```

**Response `200`:**

```json
{
  "id": "01932a1b-2c3d-7000-8000-000000000001",
  "name": "Helena Marques",
  "email": "finance@buzzvel.com",
  "role": "finance",
  "country": "Portugal",
  "country_code": "PT",
  "currency": "EUR",
  "must_change_password": false
}
```

---

### Change password

```
PUT /api/password
```

**Request body:**

```json
{
  "current_password": "123456",
  "password": "654321",
  "password_confirmation": "654321"
}
```

**Response `200`:** Updated user object (same shape as `GET /api/user`).

---

### Logout

```
POST /api/logout
```

**Response `204`:** No content.

---

### Registration (Buzzvel brief — corporate onboarding)

The brief lists **Registration, Login, Logout**. There is no public `POST /register`. In this project, **registration means finance provisioning a new employee account** — a realistic corporate flow for a reimbursement portal.

```
POST /api/employees
```

**Authorization:** Finance only (`auth:sanctum` + password changed).

**Request body:**

```json
{
  "name": "Jordan Lee",
  "email": "jordan.lee@buzzvel.com",
  "country_code": "US"
}
```

**Response `201`:** New employee user (same shape as `GET /api/user`) with `must_change_password: true`. Initial password is the employee's **first name**; they must call `PUT /api/password` before accessing payments.

**Response `403`:**

```json
{
  "message": "Only the finance team can manage employee accounts."
}
```

**Response `422`:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["An account with this email already exists."]
  }
}
```

**Demo flow:** Finance → **Register employee** in the UI → employee logs in → forced password change → submit payment.

See also [Employees (finance only)](#employees-finance-only) below for list/country endpoints.

---

## Demo test users (demo only)

```
GET /api/test-users
```

**Authorization:** None (public, for evaluators only).

**Seeded login password:** `123456` for every account returned below (use with `POST /api/login`).

**Response `200`:**

```json
{
  "finance": [
    {
      "name": "Helena Marques",
      "email": "finance@buzzvel.com",
      "country": "Portugal",
      "currency": "EUR"
    }
  ],
  "employees": [
    {
      "name": "Rafael Silva",
      "email": "rafael@buzzvel.com",
      "country": "Brazil",
      "currency": "BRL"
    }
  ]
}
```

---

## Employees (finance only)

### List employees

```
GET /api/employees
```

**Response `200`:**

```json
{
  "data": [
    {
      "id": "01932a1b-2c3d-7000-8000-000000000010",
      "name": "Rafael Silva",
      "email": "rafael@buzzvel.com",
      "country": "Brazil",
      "country_code": "BR",
      "currency": "BRL"
    }
  ]
}
```

**Response `403`:**

```json
{
  "message": "Only the finance team can manage employee accounts."
}
```

---

### Create employee

```
POST /api/employees
```

**Request body:**

```json
{
  "name": "Jordan Lee",
  "email": "jordan.lee@buzzvel.com",
  "country_code": "US"
}
```

**Response `201`:**

```json
{
  "id": "01932a1b-2c3d-7000-8000-000000000099",
  "name": "Jordan Lee",
  "email": "jordan.lee@buzzvel.com",
  "role": "employee",
  "country": "United States",
  "country_code": "US",
  "currency": "USD",
  "must_change_password": true
}
```

**Response `422`:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["An account with this email already exists."]
  }
}
```

---

### List employee countries

```
GET /api/employee-countries
```

**Response `200`:**

```json
{
  "data": [
    {
      "code": "BR",
      "name": "Brazil",
      "currency": "BRL"
    }
  ]
}
```

---

## Payment requests

### List payments

```
GET /api/payments
```

**Query parameters:**

| Parameter | Type | Description |
|---|---|---|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 8) |
| `status` | string | Filter: `pending`, `approved`, `rejected`, `expired` |
| `collaborator` | string | Filter by employee name (finance only) |
| `user_id` | string | Filter by user ID (scoped to own ID for employees) |

**Response `200`:** Laravel length-aware paginator:

```json
{
  "data": [
    {
      "id": "01932a1b-2c3d-7000-8000-000000000501",
      "reference": "PAY-2026-1007",
      "user_id": "01932a1b-2c3d-7000-8000-000000000010",
      "user_name": "Rafael Silva",
      "country": "Brazil",
      "currency": "BRL",
      "local_amount": 4200,
      "exchange_rate": 6.21,
      "eur_amount": 676.33,
      "status": "pending",
      "created_at": "2026-06-15T08:00:00+00:00",
      "updated_at": "2026-06-15T08:00:00+00:00",
      "reviewed_at": null,
      "rate_source": "exchangerate-api.com",
      "rate_fetched_at": "2026-06-15T08:00:00+00:00",
      "description": "Equipment reimbursement — monitor and peripherals"
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 8,
  "total": 1,
  "from": 1,
  "to": 1
}
```

---

### Payment summary

```
GET /api/payments/summary
```

**Query parameters:**

| Parameter | Type | Description |
|---|---|---|
| `collaborator` | string | Filter by employee name |

**Response `200`:**

```json
{
  "total": 12,
  "pending": 4,
  "approved_eur": 1842.59,
  "status_counts": {
    "all": 12,
    "pending": 4,
    "approved": 5,
    "rejected": 1,
    "expired": 2
  }
}
```

---

### Live rate preview (employee form)

```
GET /api/exchange-rates/{currency}
```

**Authorization:** Authenticated employee or finance (`auth:sanctum` + password changed).

Returns the same EUR → local rate snapshot used at payment creation (Redis cache, default TTL 30s). The frontend polls this every 30 seconds while the employee dashboard is open.

**Response `200`:**

```json
{
  "currency": "BRL",
  "exchange_rate": 6.21,
  "rate_source": "exchangerate-api.com",
  "rate_fetched_at": "2026-06-15T08:00:00+00:00"
}
```

**Response `422`:** Invalid currency code.

**Response `503`:** Exchange rate provider unavailable.

---

### Create payment request

```
POST /api/payments
```

**Authorization:** Employee only.

**Request body:**

```json
{
  "description": "Equipment reimbursement — monitor and peripherals",
  "local_amount": 4200,
  "currency": "BRL"
}
```

**Response `201`:**

```json
{
  "id": "01932a1b-2c3d-7000-8000-000000000501",
  "reference": "PAY-2026-1007",
  "user_id": "01932a1b-2c3d-7000-8000-000000000010",
  "user_name": "Rafael Silva",
  "country": "Brazil",
  "currency": "BRL",
  "local_amount": 4200,
  "exchange_rate": 6.21,
  "eur_amount": 676.33,
  "status": "pending",
  "created_at": "2026-06-15T08:00:00+00:00",
  "updated_at": "2026-06-15T08:00:00+00:00",
  "reviewed_at": null,
  "rate_source": "exchangerate-api.com",
  "rate_fetched_at": "2026-06-15T08:00:00+00:00",
  "description": "Equipment reimbursement — monitor and peripherals"
}
```

**Response `422`:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "local_amount": ["The local amount must be greater than 0."]
  }
}
```

**Response `403`:**

```json
{
  "message": "You are not allowed to perform this action."
}
```

**Response `503`:** Exchange rate provider unavailable.

```json
{
  "message": "Exchange rate is temporarily unavailable. Please try again later."
}
```

---

### Get payment details

```
GET /api/payments/{id}
```

**Response `200`:**

```json
{
  "id": "01932a1b-2c3d-7000-8000-000000000501",
  "reference": "PAY-2026-1007",
  "user_id": "01932a1b-2c3d-7000-8000-000000000010",
  "user_name": "Rafael Silva",
  "country": "Brazil",
  "currency": "BRL",
  "local_amount": 4200,
  "exchange_rate": 6.21,
  "eur_amount": 676.33,
  "status": "pending",
  "created_at": "2026-06-15T08:00:00+00:00",
  "updated_at": "2026-06-15T08:00:00+00:00",
  "reviewed_at": null,
  "rate_source": "exchangerate-api.com",
  "rate_fetched_at": "2026-06-15T08:00:00+00:00",
  "description": "Equipment reimbursement — monitor and peripherals"
}
```

**Response `403`:**

```json
{
  "message": "You are not allowed to perform this action."
}
```

**Response `404`:**

```json
{
  "message": "Payment not found."
}
```

---

### Approve or reject payment

```
PATCH /api/payments/{id}
```

**Authorization:** Finance only.

**Request body:**

```json
{
  "status": "approved"
}
```

Allowed values: `approved`, `rejected`.

**Response `200`:**

```json
{
  "id": "01932a1b-2c3d-7000-8000-000000000501",
  "reference": "PAY-2026-1007",
  "user_id": "01932a1b-2c3d-7000-8000-000000000010",
  "user_name": "Rafael Silva",
  "country": "Brazil",
  "currency": "BRL",
  "local_amount": 4200,
  "exchange_rate": 6.21,
  "eur_amount": 676.33,
  "status": "approved",
  "created_at": "2026-06-15T08:00:00+00:00",
  "updated_at": "2026-06-15T12:00:00+00:00",
  "reviewed_at": "2026-06-15T12:00:00+00:00",
  "rate_source": "exchangerate-api.com",
  "rate_fetched_at": "2026-06-15T08:00:00+00:00",
  "description": "Equipment reimbursement — monitor and peripherals"
}
```

**Response `409`:**

```json
{
  "message": "Only pending payments can be approved or rejected."
}
```

**Response `403`:**

```json
{
  "message": "You are not allowed to perform this action."
}
```

**Response `404`:**

```json
{
  "message": "Payment not found."
}
```

**Response `422`:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "status": ["The status field is required."]
  }
}
```

---

## Health check

```
GET /api/health
```

**Response `200`:**

```json
{
  "status": "ok"
}
```

---

## Error format

All errors return JSON with a `message` field. Validation errors include an `errors` object:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "local_amount": ["The local amount must be greater than 0."]
  }
}
```

## Status codes

| Code | Meaning |
|---|---|
| `200` | Success |
| `201` | Created |
| `204` | No content (logout) |
| `401` | Unauthenticated |
| `403` | Forbidden (wrong role or scope) |
| `404` | Not found |
| `409` | Conflict (invalid state transition) |
| `422` | Validation error |
| `503` | Exchange rate service unavailable |
