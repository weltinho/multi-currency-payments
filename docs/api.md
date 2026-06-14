# API Reference

> **Interactive docs:** http://localhost:8080/docs/api (Scramble — testable from the browser)

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

No request body. Sets the `XSRF-TOKEN` cookie required for mutating requests.

---

### Login

```
POST /api/login
```

**Request body:**

```json
{
  "email": "finance@buzzvel.com",
  "password": "password"
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
  "id": "uuid",
  "name": "Helena Marques",
  "email": "finance@buzzvel.com",
  "role": "finance",
  "country": "Portugal",
  "country_code": "PT",
  "currency": "EUR"
}
```

---

### Logout

```
POST /api/logout
```

**Response `204`:** No content.

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
      "id": "uuid",
      "reference": "PAY-2026-1001",
      "user_id": "uuid",
      "user_name": "Rafael Souza",
      "country": "Brazil",
      "currency": "BRL",
      "local_amount": 4200,
      "exchange_rate": 6.21,
      "eur_amount": 676.33,
      "status": "pending",
      "created_at": "2026-06-12T09:24:00Z",
      "reviewed_at": null,
      "rate_source": "exchangerate-api.com",
      "description": "Equipment reimbursement"
    }
  ],
  "current_page": 1,
  "last_page": 3,
  "per_page": 8,
  "total": 22,
  "from": 1,
  "to": 8
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
  "total": 22,
  "pending": 8,
  "approved_eur": 12500.50,
  "status_counts": {
    "all": 22,
    "pending": 8,
    "approved": 10,
    "rejected": 2,
    "expired": 2
  }
}
```

---

### Create payment request

```
POST /api/payments
```

**Authorization:** Employee only.

**Request body:**

```json
{
  "local_amount": 4200,
  "currency": "BRL",
  "description": "Equipment reimbursement"
}
```

**Response `201`:** Payment object with exchange rate locked at creation time.

**Response `422`:** Validation errors.

---

### Get payment details

```
GET /api/payments/{id}
```

**Response `200`:** Single payment object.

**Response `403`:** Employee accessing another user's payment.

**Response `404`:** Payment not found.

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

**Response `200`:** Updated payment object.

**Response `409`:** Payment is not in `pending` status.

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
