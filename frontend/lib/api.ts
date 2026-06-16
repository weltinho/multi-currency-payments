import { apiFetch } from "./http"
import type {
  CreatePaymentPayload,
  ExchangeRatePreview,
  Paginated,
  PaymentQuery,
  PaymentRequest,
  PaymentStatus,
  PaymentSummary,
  RegisterEmployeePayload,
  User,
} from "./types"

/** Build a Laravel-friendly query string from the typed query object. */
function toQueryString(query: PaymentQuery): string {
  const params = new URLSearchParams()
  if (query.page) params.set("page", String(query.page))
  if (query.per_page) params.set("per_page", String(query.per_page))
  if (query.status && query.status !== "all") params.set("status", query.status)
  if (query.collaborator) params.set("collaborator", query.collaborator)
  if (query.user_id) params.set("user_id", String(query.user_id))
  if (query.sort) params.set("sort", query.sort)
  if (query.dir) params.set("dir", query.dir)
  const qs = params.toString()
  return qs ? `?${qs}` : ""
}

/** GET /api/payments — one length-aware paginator page. */
export function fetchPayments(query: PaymentQuery): Promise<Paginated<PaymentRequest>> {
  return apiFetch<Paginated<PaymentRequest>>(`/payments${toQueryString(query)}`)
}

/** GET /api/payments/{id} — single payment detail. */
export function fetchPayment(id: number): Promise<PaymentRequest> {
  return apiFetch<PaymentRequest>(`/payments/${id}`)
}

/** POST /api/payments — employee creates a payment request. */
export function createPayment(payload: CreatePaymentPayload): Promise<PaymentRequest> {
  return apiFetch<PaymentRequest>("/payments", {
    method: "POST",
    body: payload,
  })
}

/** GET /api/payments/summary — status counts + totals, independent of pagination. */
export function fetchPaymentSummary(
  query: Pick<PaymentQuery, "collaborator" | "user_id">,
): Promise<PaymentSummary> {
  return apiFetch<PaymentSummary>(`/payments/summary${toQueryString(query)}`)
}

/** PATCH /api/payments/{id} — approve or reject a request. */
export function decidePayment(id: number, status: PaymentStatus): Promise<PaymentRequest> {
  return apiFetch<PaymentRequest>(`/payments/${id}`, {
    method: "PATCH",
    body: { status },
  })
}

/** GET /api/employees — finance lists provisioned employees. */
export function fetchEmployees(): Promise<{ data: User[] }> {
  return apiFetch<{ data: User[] }>("/employees")
}

/** POST /api/employees — finance registers a new employee account. */
export function registerEmployee(payload: RegisterEmployeePayload): Promise<User> {
  return apiFetch<User>("/employees", {
    method: "POST",
    body: payload,
  })
}

/** Poll interval for live rate previews — matches backend Redis cache TTL (30s). */
export const EXCHANGE_RATE_POLL_MS = 30_000

/** GET /api/exchange-rates/{currency} — live estimate for the submission form. */
export function fetchExchangeRate(currency: string): Promise<ExchangeRatePreview> {
  return apiFetch<ExchangeRatePreview>(`/exchange-rates/${encodeURIComponent(currency)}`)
}
