# Architecture

## Overview

Multi-currency payment request system for a company with employees across different countries. The base currency for reporting and approval totals is **EUR**.

## Components

| Service | Technology | Responsibility |
|---|---|---|
| `webserver` | Nginx | Single entry point (`http://webserver` on the internal network) |
| `frontend` | Next.js 16 + React 19 | Employee and finance dashboards |
| `backend` | Laravel 12 + PHP 8.4 | REST API, auth, business rules |
| `database` | MySQL 8 | Users, payment requests |
| `redis` | Redis 7 | Cache, job queue |
| `scheduler` | Laravel Schedule | Expire pending payments after 48h |
| API Docs | Scramble | Auto-generated OpenAPI at `/docs/api` |

## Authentication flow (Sanctum SPA)

1. Browser requests `GET /sanctum/csrf-cookie`
2. Laravel sets the `XSRF-TOKEN` cookie
3. Browser posts credentials to `POST /api/login`
4. Laravel starts an HttpOnly session
5. Subsequent requests include cookies automatically (`credentials: include`)
6. `GET /api/user` returns the authenticated user with role

## Payment request lifecycle

```
pending ──(finance approves)──► approved
        ──(finance rejects)──► rejected
        ──(48h without action)──► expired
```

## Exchange rate rules

- Rate is fetched from a public API at **creation time only**
- Stored fields: `exchange_rate`, `rate_source`, `rate_fetched_at`, `eur_amount`
- These fields are **immutable** after creation
- Formula: `eur_amount = local_amount / exchange_rate`

## Authorization

| Action | Employee | Finance |
|---|---|---|
| Create payment request | Yes (own) | No |
| List own requests | Yes | Yes (all) |
| List all requests | No | Yes |
| Approve / reject | No | Yes (pending only) |
| View request details | Own only | All |

## Docker networking

All services communicate on the internal `app` bridge network by **service name**. Only `webserver` exposes port `8080` to the host.

```
webserver:80 ──► frontend:3000
webserver:80 ──► backend:9000 (PHP-FPM)
backend ──► database:3306
backend ──► redis:6379
scheduler ──► database:3306
```

Containers can call the public gateway internally at `http://webserver`.

## Scramble documentation

Scramble auto-generates OpenAPI docs from Laravel routes, controllers, and form requests. It is served at `/docs/api` through `webserver`, keeping the documentation on the same origin as the API.
