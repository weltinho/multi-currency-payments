"use client"

import { useState, type FormEvent } from "react"
import useSWR, { mutate } from "swr"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { CurrencySelect } from "@/components/currency-select"
import { StatusBadge } from "@/components/status-badge"
import { PaymentDetailDialog } from "@/components/payment-detail-dialog"
import { PaginationControls } from "@/components/pagination-controls"
import { ResponsiveTableLayout } from "@/components/responsive-table-layout"
import { useLanguage } from "@/components/language-provider"
import {
  formatCurrency,
  formatDateTime,
  formatExchangeRate,
} from "@/lib/data"
import { createPayment, EXCHANGE_RATE_POLL_MS, fetchExchangeRate, fetchPayments } from "@/lib/api"
import { ApiError } from "@/lib/http"
import type { CurrencyCode, PaymentRequest, User } from "@/lib/types"
import { ArrowRight, Inbox, Loader2, Plus } from "lucide-react"
import { EmptyState } from "@/components/empty-state"
import { InlineAlert } from "@/components/inline-alert"
import { SortableTableHead } from "@/components/sortable-table-head"
import { usePaymentSort } from "@/lib/sort-payments"
import type { PaymentSortKey } from "@/lib/sort-payments"

const PER_PAGE = 6

const TABLE_ROW_CLASS =
  "cursor-pointer even:bg-muted/40 hover:bg-primary/10 dark:even:bg-white/[0.02] dark:hover:bg-white/[0.04]"

export function EmployeeDashboard({ user }: { user: User }) {
  const { t, locale } = useLanguage()
  const [amount, setAmount] = useState("")
  const [description, setDescription] = useState("")
  const [paymentCurrency, setPaymentCurrency] = useState<CurrencyCode>(user.currency)
  const [selected, setSelected] = useState<PaymentRequest | null>(null)
  const [open, setOpen] = useState(false)
  const [page, setPage] = useState(1)
  const { sort, toggleSort } = usePaymentSort()
  const [submitting, setSubmitting] = useState(false)
  const [submitError, setSubmitError] = useState<string | null>(null)
  const [submitSuccess, setSubmitSuccess] = useState<string | null>(null)

  const listKey = ["my-payments", user.id, page, sort.sort, sort.dir] as const

  const {
    data: pageData,
    isLoading,
    isValidating,
  } = useSWR(
    listKey,
    ([, userId, p, sortKey, dir]: readonly [typeof listKey[0], number, number, PaymentSortKey, typeof sort.dir]) =>
      fetchPayments({
        user_id: userId,
        page: p,
        per_page: PER_PAGE,
        sort: sortKey,
        dir,
      }),
    { keepPreviousData: true },
  )

  const history: PaymentRequest[] = pageData?.data ?? []
  const busy = isLoading || isValidating
  const isEmpty = !isLoading && history.length === 0

  const {
    data: ratePreview,
    isLoading: rateLoading,
    error: rateError,
    isValidating: rateRefreshing,
  } = useSWR(
    ["exchange-rate", paymentCurrency],
    ([, currency]) => fetchExchangeRate(currency),
    {
      refreshInterval: EXCHANGE_RATE_POLL_MS,
      keepPreviousData: true,
      revalidateOnFocus: true,
    },
  )

  const numericAmount = Number.parseFloat(amount) || 0
  const rate = ratePreview?.exchange_rate ?? null
  const eurEstimate = rate && rate > 0 ? numericAmount / rate : 0
  const ratePending = rateLoading && rate === null

  function openDetail(payment: PaymentRequest) {
    setSelected(payment)
    setOpen(true)
  }

  function handleSort(key: PaymentSortKey) {
    toggleSort(key)
    setPage(1)
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    if (numericAmount <= 0 || !description.trim()) {
      return
    }

    setSubmitting(true)
    setSubmitError(null)
    setSubmitSuccess(null)

    try {
      await createPayment({
        description: description.trim(),
        local_amount: numericAmount,
        currency: paymentCurrency,
      })
      setAmount("")
      setDescription("")
      setPaymentCurrency(user.currency)
      setPage(1)
      setSubmitSuccess(t("employee.submitSuccess"))
      await mutate((key: unknown) => Array.isArray(key) && key[0] === "my-payments")
    } catch (err) {
      setSubmitError(
        err instanceof ApiError && err.message
          ? err.message
          : t("employee.submitError"),
      )
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <div className="mb-6">
        <h1 className="text-xl font-semibold tracking-tight text-foreground text-balance">
          {t("employee.greeting", { name: user.name.split(" ")[0] })}
        </h1>
        <p className="text-sm text-muted-foreground">
          {t("employee.baseCurrency", { country: user.country, currency: user.currency })}
        </p>
      </div>

      <div className="grid gap-6 lg:grid-cols-[380px_1fr]">
        <Card className="h-fit border-border">
          <CardHeader>
            <CardTitle className="text-base">{t("employee.newRequest")}</CardTitle>
            <CardDescription>{t("employee.newRequestDesc")}</CardDescription>
          </CardHeader>
          <CardContent>
            <form className="space-y-4" onSubmit={handleSubmit}>
              <div className="space-y-2">
                <Label htmlFor="description">{t("employee.description")}</Label>
                <Input
                  id="description"
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder={t("employee.description")}
                  maxLength={1000}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="currency">{t("employee.paymentCurrency")}</Label>
                <CurrencySelect
                  id="currency"
                  value={paymentCurrency}
                  onValueChange={setPaymentCurrency}
                  defaultCurrency={user.currency}
                  defaultLabel={t("employee.profileCurrencyDefault")}
                  disabled={submitting}
                />
                <p className="text-xs text-muted-foreground">
                  {t("employee.currencyHint", { currency: user.currency })}
                </p>
              </div>

              <div className="space-y-2">
                <Label htmlFor="amount">{t("employee.amount")}</Label>
                <Input
                  id="amount"
                  type="number"
                  inputMode="decimal"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                  value={amount}
                  onChange={(e) => setAmount(e.target.value)}
                  required
                />
              </div>

              <div className="rounded-lg border border-dashed border-border bg-muted/40 p-3">
                <div className="flex items-center justify-between text-sm">
                  <span className="flex items-center gap-1.5 text-muted-foreground">
                    {t("employee.estimate")}
                    <ArrowRight className="size-3.5" />
                    EUR
                    {rateRefreshing && rate !== null && (
                      <Loader2 className="size-3 animate-spin" aria-hidden="true" />
                    )}
                  </span>
                  <span className="font-semibold text-foreground">
                    {ratePending ? (
                      <Loader2 className="size-4 animate-spin text-muted-foreground" aria-label={t("common.loading")} />
                    ) : rate !== null ? (
                      formatCurrency(eurEstimate, "EUR")
                    ) : (
                      "—"
                    )}
                  </span>
                </div>
                {rate !== null && (
                  <>
                    <p className="mt-1 text-xs text-muted-foreground">
                      {t("employee.referenceRate", {
                        rate: formatExchangeRate(rate),
                        currency: paymentCurrency,
                      })}
                    </p>
                    {ratePreview?.rate_fetched_at && (
                      <p className="mt-0.5 text-xs text-muted-foreground">
                        {t("employee.rateAsOf", {
                          time: formatDateTime(ratePreview.rate_fetched_at, locale),
                        })}
                      </p>
                    )}
                  </>
                )}
                {rateError && rate === null && (
                  <p className="mt-1 text-xs text-destructive" role="alert">
                    {t("employee.estimateUnavailable")}
                  </p>
                )}
                <p className="mt-2 text-xs text-muted-foreground">
                  {t("employee.estimateHint")}
                </p>
              </div>

              {submitSuccess && (
                <InlineAlert variant="success" message={submitSuccess} />
              )}

              {submitError && (
                <InlineAlert variant="error" message={submitError} />
              )}

              <Button
                type="submit"
                className="w-full gap-2"
                disabled={submitting || numericAmount <= 0 || !description.trim()}
              >
                {submitting ? (
                  <Loader2 className="size-4 animate-spin" />
                ) : (
                  <Plus className="size-4" />
                )}
                {t("employee.submit")}
              </Button>
            </form>
          </CardContent>
        </Card>

        <Card className="border-border">
          <CardHeader>
            <div className="flex items-start justify-between gap-3">
              <div>
                <CardTitle className="text-base">{t("employee.history")}</CardTitle>
                <CardDescription>{t("employee.historyDesc")}</CardDescription>
              </div>
              {busy && (
                <span className="flex items-center gap-1.5 text-xs text-muted-foreground">
                  <Loader2 className="size-3.5 animate-spin" />
                  {t("common.loading")}
                </span>
              )}
            </div>
          </CardHeader>
          <CardContent className="px-0 sm:px-6">
            {isEmpty ? (
              <EmptyState
                icon={Inbox}
                title={t("employee.noRequests")}
                description={t("employee.noRequestsDesc")}
              />
            ) : (
              <>
                <div className={busy ? "opacity-60 transition-opacity" : undefined}>
                <ResponsiveTableLayout
                  measureKey={`${history.length}-${locale}-${page}-${sort.sort}-${sort.dir}`}
                  cards={
                    <ul className="space-y-3 px-4">
                      {history.map((p) => (
                        <li key={p.id}>
                          <button
                            type="button"
                            onClick={() => openDetail(p)}
                            className="w-full rounded-lg border border-border bg-card p-4 text-left outline-none transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background active:bg-muted/60"
                          >
                            <div className="flex items-start justify-between gap-3">
                              <div>
                                <p className="text-xs text-muted-foreground">
                                  {formatDateTime(p.created_at, locale)}
                                </p>
                                <p className="font-medium text-foreground">{p.currency}</p>
                              </div>
                              <StatusBadge status={p.status} />
                            </div>
                            <div className="mt-3 flex items-end justify-between border-t border-border pt-3">
                              <div>
                                <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                                  {t("table.localAmount")}
                                </p>
                                <p className="font-mono font-medium tabular-nums text-foreground">
                                  {formatCurrency(p.local_amount, p.currency)}
                                </p>
                              </div>
                              <div className="text-right">
                                <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                                  {t("table.eur")}
                                </p>
                                <p className="font-mono font-medium tabular-nums text-foreground">
                                  {formatCurrency(p.eur_amount, "EUR")}
                                </p>
                              </div>
                            </div>
                          </button>
                        </li>
                      ))}
                    </ul>
                  }
                  table={
                    <Table scrollable={false}>
                      <TableHeader>
                        <TableRow>
                          <SortableTableHead
                            label={t("table.date")}
                            sortKey="created_at"
                            activeSort={sort.sort}
                            direction={sort.dir}
                            onSort={handleSort}
                          />
                          <SortableTableHead
                            label={t("table.currency")}
                            sortKey="currency"
                            activeSort={sort.sort}
                            direction={sort.dir}
                            onSort={handleSort}
                          />
                          <SortableTableHead
                            label={t("table.localAmount")}
                            sortKey="local_amount"
                            activeSort={sort.sort}
                            direction={sort.dir}
                            onSort={handleSort}
                            className="text-right"
                            align="right"
                          />
                          <SortableTableHead
                            label={t("table.eur")}
                            sortKey="eur_amount"
                            activeSort={sort.sort}
                            direction={sort.dir}
                            onSort={handleSort}
                            className="text-right"
                            align="right"
                          />
                          <SortableTableHead
                            label={t("table.status")}
                            sortKey="status"
                            activeSort={sort.sort}
                            direction={sort.dir}
                            onSort={handleSort}
                            className="text-right"
                            align="right"
                          />
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {history.map((p) => (
                          <TableRow
                            key={p.id}
                            className={TABLE_ROW_CLASS}
                            onClick={() => openDetail(p)}
                          >
                            <TableCell className="whitespace-nowrap text-muted-foreground">
                              {formatDateTime(p.created_at, locale)}
                            </TableCell>
                            <TableCell className="font-medium">{p.currency}</TableCell>
                            <TableCell className="text-right font-mono tabular-nums">
                              {formatCurrency(p.local_amount, p.currency)}
                            </TableCell>
                            <TableCell className="text-right font-mono tabular-nums">
                              {formatCurrency(p.eur_amount, "EUR")}
                            </TableCell>
                            <TableCell className="text-right">
                              <div className="flex justify-end">
                                <StatusBadge status={p.status} />
                              </div>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  }
                />
                </div>

                {pageData && (
                  <PaginationControls
                    meta={pageData}
                    onPageChange={setPage}
                    isLoading={busy}
                  />
                )}
              </>
            )}
          </CardContent>
        </Card>
      </div>

      <PaymentDetailDialog payment={selected} open={open} onOpenChange={setOpen} />
    </div>
  )
}
