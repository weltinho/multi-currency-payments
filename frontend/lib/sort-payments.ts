import { useCallback, useState } from "react"

export type PaymentSortKey =
  | "created_at"
  | "currency"
  | "local_amount"
  | "eur_amount"
  | "status"
  | "user_name"
  | "exchange_rate"
  | "country"

export type SortDirection = "asc" | "desc"

export interface PaymentSort {
  sort: PaymentSortKey
  dir: SortDirection
}

const DESC_FIRST: PaymentSortKey[] = [
  "created_at",
  "local_amount",
  "eur_amount",
  "exchange_rate",
]

function defaultDirectionFor(key: PaymentSortKey): SortDirection {
  return DESC_FIRST.includes(key) ? "desc" : "asc"
}

export function nextPaymentSort(prev: PaymentSort, key: PaymentSortKey): PaymentSort {
  if (prev.sort === key) {
    return { sort: key, dir: prev.dir === "asc" ? "desc" : "asc" }
  }

  return { sort: key, dir: defaultDirectionFor(key) }
}

export function usePaymentSort(
  initial: PaymentSort = { sort: "created_at", dir: "desc" },
): {
  sort: PaymentSort
  toggleSort: (key: PaymentSortKey) => void
} {
  const [sort, setSort] = useState<PaymentSort>(initial)

  const toggleSort = useCallback((key: PaymentSortKey) => {
    setSort((prev) => nextPaymentSort(prev, key))
  }, [])

  return { sort, toggleSort }
}
