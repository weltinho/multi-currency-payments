import type { CurrencyCode, PaymentStatus } from "./types"

export const CURRENCY_META: Record<
  CurrencyCode,
  { symbol: string; label: string; locale: string; flag: string }
> = {
  EUR: { symbol: "€", label: "Euro", locale: "de-DE", flag: "EU" },
  BRL: { symbol: "R$", label: "Brazilian Real", locale: "pt-BR", flag: "BR" },
  USD: { symbol: "$", label: "US Dollar", locale: "en-US", flag: "US" },
  GBP: { symbol: "£", label: "British Pound", locale: "en-GB", flag: "GB" },
  JPY: { symbol: "¥", label: "Japanese Yen", locale: "ja-JP", flag: "JP" },
  INR: { symbol: "₹", label: "Indian Rupee", locale: "en-IN", flag: "IN" },
  KRW: { symbol: "₩", label: "South Korean Won", locale: "ko-KR", flag: "KR" },
  PLN: { symbol: "zł", label: "Polish Zloty", locale: "pl-PL", flag: "PL" },
  CAD: { symbol: "CA$", label: "Canadian Dollar", locale: "en-CA", flag: "CA" },
  AUD: { symbol: "A$", label: "Australian Dollar", locale: "en-AU", flag: "AU" },
  MXN: { symbol: "MX$", label: "Mexican Peso", locale: "es-MX", flag: "MX" },
  AED: { symbol: "د.إ", label: "UAE Dirham", locale: "ar-AE", flag: "AE" },
  SEK: { symbol: "kr", label: "Swedish Krona", locale: "sv-SE", flag: "SE" },
  ZAR: { symbol: "R", label: "South African Rand", locale: "en-ZA", flag: "ZA" },
  SGD: { symbol: "S$", label: "Singapore Dollar", locale: "en-SG", flag: "SG" },
}

// EUR -> local currency reference rates (used for the conversion estimate)
export const REFERENCE_RATES: Record<CurrencyCode, number> = {
  EUR: 1,
  BRL: 6.21,
  USD: 1.08,
  GBP: 0.84,
  JPY: 162.45,
  INR: 90.12,
  KRW: 1450.5,
  PLN: 4.32,
  CAD: 1.47,
  AUD: 1.65,
  MXN: 20.15,
  AED: 4.0,
  SEK: 11.5,
  ZAR: 20.5,
  SGD: 1.45,
}

export const STATUS_META: Record<
  PaymentStatus,
  { label: string; badge: string; dot: string }
> = {
  pending: {
    label: "Pending",
    badge:
      "border-status-pending-border bg-status-pending-bg text-status-pending-fg dark:border-yellow-500/20 dark:bg-yellow-500/10 dark:text-yellow-400",
    dot: "bg-status-pending-dot dark:bg-yellow-400",
  },
  approved: {
    label: "Approved",
    badge:
      "border-status-approved-border bg-status-approved-bg text-status-approved-fg dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400",
    dot: "bg-status-approved-dot dark:bg-emerald-400",
  },
  rejected: {
    label: "Rejected",
    badge:
      "border-status-rejected-border bg-status-rejected-bg text-status-rejected-fg dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400",
    dot: "bg-status-rejected-dot dark:bg-red-400",
  },
  expired: {
    label: "Expired",
    badge:
      "border-status-expired-border bg-status-expired-bg text-status-expired-fg dark:border-zinc-500/20 dark:bg-zinc-500/10 dark:text-zinc-400",
    dot: "bg-status-expired-dot dark:bg-zinc-400",
  },
}

export function formatCurrency(amount: number, currency: CurrencyCode): string {
  const meta = CURRENCY_META[currency]
  return new Intl.NumberFormat(meta.locale, {
    style: "currency",
    currency,
    maximumFractionDigits: currency === "JPY" ? 0 : 2,
  }).format(amount)
}

export function formatDateTime(iso: string, locale = "en-GB"): string {
  return new Date(iso).toLocaleString(locale, {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

export function formatDate(iso: string, locale = "en-GB"): string {
  return new Date(iso).toLocaleDateString(locale, {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  })
}
