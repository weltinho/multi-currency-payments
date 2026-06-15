// Tipos partilhados por toda a app. Os campos seguem snake_case de propósito,
// para baterem 1:1 com o JSON devolvido pelo Laravel (sem precisar de mapear).

// Define que dashboard o utilizador vê depois do login.
export type Role = "finance" | "employee"

// Ciclo de vida de um pedido de pagamento.
export type PaymentStatus = "pending" | "approved" | "rejected" | "expired"

export interface User {
  id: number
  name: string
  email: string
  role: Role
  country: string
  country_code: string
  currency: CurrencyCode
  must_change_password: boolean
}

export type CurrencyCode =
  | "EUR"
  | "BRL"
  | "USD"
  | "GBP"
  | "JPY"
  | "INR"
  | "KRW"
  | "PLN"
  | "CAD"
  | "AUD"
  | "MXN"
  | "AED"
  | "SEK"
  | "ZAR"
  | "SGD"

export interface PaymentRequest {
  id: number
  reference: string
  user_id: number
  user_name: string
  country: string
  currency: CurrencyCode
  local_amount: number
  exchange_rate: number // EUR -> local currency rate at creation time
  eur_amount: number
  status: PaymentStatus
  created_at: string
  updated_at: string
  reviewed_at: string | null
  rate_source: string
  description: string
}

/**
 * Mirrors Laravel's LengthAwarePaginator JSON shape.
 * When you swap the mock API for the real Laravel endpoint, the response
 * body maps 1:1 to this interface (e.g. `return PaymentRequest::paginate($perPage)`).
 */
export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

/** Query params accepted by the payments endpoint (sent as the Laravel query string). */
export interface PaymentQuery {
  page?: number
  per_page?: number
  status?: PaymentStatus | "all"
  collaborator?: string
  user_id?: number
}

/** Aggregate figures returned by the summary endpoint (independent of pagination). */
export interface PaymentSummary {
  total: number
  pending: number
  approved_eur: number
  status_counts: Record<PaymentStatus | "all", number>
}

export interface CreatePaymentPayload {
  description: string
  local_amount: number
  currency?: CurrencyCode
}

export interface RegisterEmployeePayload {
  name: string
  email: string
  country_code: string
}

export interface ChangePasswordPayload {
  current_password: string
  password: string
  password_confirmation: string
}
