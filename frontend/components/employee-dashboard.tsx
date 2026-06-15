"use client"

import { useState } from "react"
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
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { StatusBadge } from "@/components/status-badge"
import { PaymentDetailDialog } from "@/components/payment-detail-dialog"
import { PaginationControls } from "@/components/pagination-controls"
import { useLanguage } from "@/components/language-provider"
import {
  REFERENCE_RATES,
  formatCurrency,
  formatDate,
} from "@/lib/data"
import { createPayment, fetchPayments } from "@/lib/api"
import { ApiError } from "@/lib/http"
import type { PaymentRequest, User } from "@/lib/types"
import { ArrowRight, Loader2, Plus } from "lucide-react"

const PER_PAGE = 6

export function EmployeeDashboard({ user }: { user: User }) {
  const { t, locale } = useLanguage()
  const [amount, setAmount] = useState("")
  const [description, setDescription] = useState("")
  const [selected, setSelected] = useState<PaymentRequest | null>(null)
  const [open, setOpen] = useState(false)
  const [page, setPage] = useState(1)
  const [submitting, setSubmitting] = useState(false)
  const [submitError, setSubmitError] = useState<string | null>(null)

  const listKey = ["my-payments", user.id, page] as const

  const {
    data: pageData,
    isLoading,
    isValidating,
  } = useSWR(
    listKey,
    ([, userId, p]) => fetchPayments({ user_id: userId, page: p, per_page: PER_PAGE }),
    { keepPreviousData: true },
  )

  const history = pageData?.data ?? []
  const busy = isLoading || isValidating
  const isEmpty = !isLoading && history.length === 0

  const numericAmount = Number.parseFloat(amount) || 0
  const rate = REFERENCE_RATES[user.currency] ?? 1
  const eurEstimate = rate > 0 ? numericAmount / rate : 0

  function openDetail(payment: PaymentRequest) {
    setSelected(payment)
    setOpen(true)
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (numericAmount <= 0 || !description.trim()) {
      return
    }

    setSubmitting(true)
    setSubmitError(null)

    try {
      await createPayment({
        description: description.trim(),
        local_amount: numericAmount,
      })
      setAmount("")
      setDescription("")
      setPage(1)
      await mutate((key) => Array.isArray(key) && key[0] === "my-payments")
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
                  </span>
                  <span className="font-semibold text-foreground">
                    {formatCurrency(eurEstimate, "EUR")}
                  </span>
                </div>
                <p className="mt-1 text-xs text-muted-foreground">
                  {t("employee.referenceRate", { rate, currency: user.currency })}
                </p>
              </div>

              {submitError && (
                <p className="text-sm text-destructive">{submitError}</p>
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
              <p className="py-10 text-center text-sm text-muted-foreground">
                {t("employee.noRequests")}
              </p>
            ) : (
              <>
                <div className={busy ? "opacity-60 transition-opacity" : undefined}>
                <ul className="space-y-3 px-4 sm:hidden">
                  {history.map((p) => (
                    <li key={p.id}>
                      <button
                        type="button"
                        onClick={() => openDetail(p)}
                        className="w-full rounded-xl border border-border bg-card p-4 text-left transition-colors active:bg-muted/60"
                      >
                        <div className="flex items-start justify-between gap-3">
                          <div>
                            <p className="font-medium text-foreground">{p.currency}</p>
                            <p className="text-xs text-muted-foreground">
                              {formatDate(p.created_at, locale)}
                            </p>
                          </div>
                          <StatusBadge status={p.status} />
                        </div>
                        <div className="mt-3 flex items-end justify-between border-t border-border pt-3">
                          <div>
                            <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                              {t("table.localAmount")}
                            </p>
                            <p className="font-medium tabular-nums text-foreground">
                              {formatCurrency(p.local_amount, p.currency)}
                            </p>
                          </div>
                          <div className="text-right">
                            <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                              {t("table.eur")}
                            </p>
                            <p className="font-medium tabular-nums text-foreground">
                              {formatCurrency(p.eur_amount, "EUR")}
                            </p>
                          </div>
                        </div>
                      </button>
                    </li>
                  ))}
                </ul>

                <div className="hidden overflow-x-auto sm:block">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>{t("table.date")}</TableHead>
                        <TableHead>{t("table.currency")}</TableHead>
                        <TableHead className="text-right">{t("table.localAmount")}</TableHead>
                        <TableHead className="text-right">{t("table.eur")}</TableHead>
                        <TableHead className="text-right">{t("table.status")}</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {history.map((p) => (
                        <TableRow
                          key={p.id}
                          className="cursor-pointer even:bg-muted/40 hover:bg-primary/10 dark:hover:bg-primary/15"
                          onClick={() => openDetail(p)}
                        >
                          <TableCell className="whitespace-nowrap text-muted-foreground">
                            {formatDate(p.created_at, locale)}
                          </TableCell>
                          <TableCell className="font-medium">{p.currency}</TableCell>
                          <TableCell className="text-right tabular-nums">
                            {formatCurrency(p.local_amount, p.currency)}
                          </TableCell>
                          <TableCell className="text-right tabular-nums">
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
                </div>
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
