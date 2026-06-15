"use client"

import { useMemo, useRef, useState } from "react"
import useSWR from "swr"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
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
import {
  PaymentDecisionDialog,
  type PaymentDecision,
} from "@/components/payment-decision-dialog"
import { PaginationControls } from "@/components/pagination-controls"
import { ResponsiveTableLayout } from "@/components/responsive-table-layout"
import { useLanguage } from "@/components/language-provider"
import { cn } from "@/lib/utils"
import { STATUS_META, formatCurrency, formatDateTime } from "@/lib/data"
import { decidePayment, fetchEmployees, fetchPayments, fetchPaymentSummary } from "@/lib/api"
import { ApiError } from "@/lib/http"
import { RegisterEmployeeDialog } from "@/components/register-employee-dialog"
import { EmptyState } from "@/components/empty-state"
import { InlineAlert } from "@/components/inline-alert"
import type { PaymentQuery, PaymentRequest, PaymentStatus, User } from "@/lib/types"
import type { TranslationKey } from "@/lib/i18n"
import { Check, Inbox, Loader2, Search, X } from "lucide-react"

type FilterValue = "all" | PaymentStatus

const FILTERS: FilterValue[] = ["all", "pending", "approved", "rejected", "expired"]
const PER_PAGE = 8

const TABLE_ROW_CLASS =
  "cursor-pointer even:bg-muted/40 hover:bg-primary/10 dark:even:bg-white/[0.02] dark:hover:bg-white/[0.04]"

const APPROVE_BUTTON_CLASS =
  "min-h-9 min-w-9 border-border text-emerald-500 hover:bg-emerald-500/15 hover:text-emerald-400 dark:text-emerald-400 dark:hover:bg-emerald-500/15"

const REJECT_BUTTON_CLASS =
  "min-h-9 min-w-9 border-border text-red-500 hover:bg-red-500/15 hover:text-red-400 dark:text-red-400 dark:hover:bg-red-500/15"

// Collaborator names for the typeahead helper, loaded from the API.
const EMPTY_EMPLOYEES: User[] = []

function StatCard({
  label,
  value,
  highlight,
}: {
  label: string
  value: string
  highlight?: boolean
}) {
  return (
    <div
      className={cn(
        "relative rounded-lg border border-border bg-card p-4",
        highlight &&
          "dark:border-emerald-500/20 dark:ring-1 dark:ring-emerald-500/10",
      )}
    >
      {highlight && (
        <span
          className="absolute top-3 right-3 size-1.5 rounded-full bg-emerald-400"
          aria-hidden="true"
        />
      )}
      <p className="text-xs text-muted-foreground">{label}</p>
      <p className="mt-1 font-mono text-2xl font-semibold tracking-tight text-foreground tabular-nums">
        {value}
      </p>
    </div>
  )
}

export function FinanceDashboard() {
  const { t, locale } = useLanguage()
  const [filter, setFilter] = useState<FilterValue>("all")
  const [collaborator, setCollaborator] = useState("")
  const [page, setPage] = useState(1)
  const [showSuggestions, setShowSuggestions] = useState(false)
  const [selected, setSelected] = useState<PaymentRequest | null>(null)
  const [open, setOpen] = useState(false)
  const [decisionContext, setDecisionContext] = useState<{
    payment: PaymentRequest
    decision: PaymentDecision
  } | null>(null)
  const blurTimeout = useRef<ReturnType<typeof setTimeout> | null>(null)
  const [decisionNotice, setDecisionNotice] = useState<{
    variant: "success" | "error"
    message: string
  } | null>(null)

  // GET /api/payments?page=&per_page=&status=&collaborator=
  const listQuery: PaymentQuery = {
    page,
    per_page: PER_PAGE,
    status: filter,
    collaborator: collaborator.trim(),
  }
  const {
    data: pageData,
    isLoading: listLoading,
    isValidating: listValidating,
    mutate: mutateList,
  } = useSWR(["payments", listQuery], ([, q]) => fetchPayments(q), {
    keepPreviousData: true,
  })

  // GET /api/payments/summary?collaborator= — aggregates independent of pagination.
  const { data: summary, mutate: mutateSummary } = useSWR(
    ["payments-summary", collaborator.trim()],
    ([, c]) => fetchPaymentSummary({ collaborator: c }),
  )

  const { data: employeesData, mutate: mutateEmployees } = useSWR(
    "employees",
    fetchEmployees,
  )

  const collaboratorNames = useMemo(
    () =>
      (employeesData?.data ?? EMPTY_EMPLOYEES)
        .map((employee) => employee.name)
        .sort((a, b) => a.localeCompare(b)),
    [employeesData],
  )

  const rows = pageData?.data ?? []
  const counts = summary?.status_counts ?? {
    all: 0,
    pending: 0,
    approved: 0,
    rejected: 0,
    expired: 0,
  }

  // Typeahead suggestions filtered by the current query
  const suggestions = useMemo(() => {
    const q = collaborator.trim().toLowerCase()
    return collaboratorNames.filter((name) => name.toLowerCase().includes(q))
  }, [collaborator, collaboratorNames])

  // Dot color per status to make the status filter obvious at a glance
  const STATUS_DOT: Record<FilterValue, string> = {
    all: "bg-muted-foreground",
    pending: STATUS_META.pending.dot,
    approved: STATUS_META.approved.dot,
    rejected: STATUS_META.rejected.dot,
    expired: STATUS_META.expired.dot,
  }

  // Trocar o filtro de estado. Volto sempre à página 1, senão o utilizador
  // podia ficar "preso" numa página que já não existe no resultado filtrado.
  function changeFilter(f: FilterValue) {
    setFilter(f)
    setPage(1)
  }

  // Mesmo raciocínio do filtro acima: ao mudar a pesquisa por colaborador
  // reinicio a paginação para o topo da nova lista.
  function changeCollaborator(value: string) {
    setCollaborator(value)
    setPage(1)
  }

  function promptDecision(
    payment: PaymentRequest,
    status: PaymentDecision,
    e: React.MouseEvent,
  ) {
    e.stopPropagation()
    setDecisionContext({ payment, decision: status })
  }

  async function confirmDecision(id: number, status: PaymentDecision) {
    try {
      await decidePayment(id, status)
      await Promise.all([mutateList(), mutateSummary()])
      setDecisionContext(null)
      setDecisionNotice({
        variant: "success",
        message:
          status === "approved"
            ? t("finance.decisionSuccessApprove")
            : t("finance.decisionSuccessReject"),
      })
    } catch (err) {
      setDecisionContext(null)
      setDecisionNotice({
        variant: "error",
        message:
          err instanceof ApiError && err.message
            ? err.message
            : t("finance.decisionError"),
      })
    }
  }

  // Abre o modal com os detalhes completos do pedido clicado.
  function openDetail(payment: PaymentRequest) {
    setSelected(payment)
    setOpen(true)
  }

  const busy = listLoading || listValidating
  const isEmpty = !listLoading && rows.length === 0

  return (
    <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 className="text-xl font-semibold tracking-tight text-foreground text-balance">
            {t("finance.title")}
          </h1>
          <p className="text-sm text-muted-foreground">{t("finance.subtitle")}</p>
        </div>
        <RegisterEmployeeDialog onCreated={() => mutateEmployees()} />
      </div>

      <div className="mb-6 grid gap-3 sm:grid-cols-3">
        <StatCard
          label={t("finance.totalRequests")}
          value={summary ? String(summary.total) : listLoading ? "…" : "—"}
        />
        <StatCard
          label={t("finance.pending")}
          value={summary ? String(summary.pending) : listLoading ? "…" : "—"}
        />
        <StatCard
          label={t("finance.approvedEur")}
          value={summary ? formatCurrency(summary.approved_eur, "EUR") : listLoading ? "…" : "—"}
          highlight
        />
      </div>

      {decisionNotice && (
        <InlineAlert
          variant={decisionNotice.variant}
          message={decisionNotice.message}
          className="mb-4"
        />
      )}

      <Card className="border-border">
        <CardHeader className="gap-4">
          <div className="flex items-start justify-between gap-3">
            <div>
              <CardTitle className="text-base">{t("finance.overview")}</CardTitle>
              <CardDescription>{t("finance.overviewDesc")}</CardDescription>
            </div>
            {busy && (
              <span className="flex items-center gap-1.5 text-xs text-muted-foreground">
                <Loader2 className="size-3.5 animate-spin" />
                {t("common.loading")}
              </span>
            )}
          </div>
          {/* Status filter chips */}
          <div>
            <p className="mb-1.5 text-xs font-medium text-muted-foreground">
              {t("finance.filterStatus")}
            </p>
            <div className="flex flex-wrap gap-2">
              {FILTERS.map((f) => {
                const active = filter === f
                return (
                  <button
                    key={f}
                    type="button"
                    aria-pressed={active}
                    onClick={() => changeFilter(f)}
                    className={cn(
                      "inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background",
                      active
                        ? "border-foreground bg-foreground text-background dark:border-white/20 dark:bg-white/10 dark:text-foreground"
                        : "border-border bg-card text-foreground hover:bg-muted",
                    )}
                  >
                    <span
                      className={cn("size-1.5 rounded-full", STATUS_DOT[f])}
                      aria-hidden="true"
                    />
                    {t(`filter.${f}` as TranslationKey)}
                    <span
                      className={cn(
                        "rounded-full px-1.5 text-xs tabular-nums",
                        active ? "bg-background/20 text-background dark:bg-white/10 dark:text-foreground" : "bg-muted text-muted-foreground",
                      )}
                    >
                      {counts[f]}
                    </span>
                  </button>
                )
              })}
            </div>
          </div>

          {/* Collaborator typeahead filter */}
          <div className="relative max-w-sm">
            <label className="mb-1.5 block text-xs font-medium text-muted-foreground">
              {t("finance.filterCollaborator")}
            </label>
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                type="text"
                role="combobox"
                aria-expanded={showSuggestions}
                aria-autocomplete="list"
                value={collaborator}
                placeholder={t("finance.collaboratorPlaceholder")}
                onChange={(e) => {
                  changeCollaborator(e.target.value)
                  setShowSuggestions(true)
                }}
                onFocus={() => setShowSuggestions(true)}
                onBlur={() => {
                  // Pequeno atraso ao fechar: dá tempo ao clique numa sugestão
                  // de disparar antes de a lista desaparecer (o onClick cancela
                  // este timeout). Sem isto, o blur fecharia a lista primeiro.
                  blurTimeout.current = setTimeout(() => setShowSuggestions(false), 120)
                }}
                className="pl-9 pr-9"
              />
              {collaborator && (
                <button
                  type="button"
                  aria-label={t("finance.clear")}
                  onClick={() => {
                    changeCollaborator("")
                    setShowSuggestions(false)
                  }}
                  className="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-muted-foreground transition-colors outline-none hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                >
                  <X className="size-3.5" />
                </button>
              )}
            </div>

            {showSuggestions && suggestions.length > 0 && (
              <ul className="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-border bg-popover p-1 shadow-xl ring-1 ring-foreground/10">
                {suggestions.map((name) => (
                  <li key={name}>
                    <button
                      type="button"
                      onMouseDown={(e) => e.preventDefault()}
                      onClick={() => {
                        changeCollaborator(name)
                        setShowSuggestions(false)
                        if (blurTimeout.current) clearTimeout(blurTimeout.current)
                      }}
                      className="flex w-full items-center justify-between rounded-md px-2.5 py-2 text-left text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                      <span className="truncate">{name}</span>
                      {name === collaborator && <Check className="size-4 shrink-0" />}
                    </button>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </CardHeader>
        <CardContent className="px-0 sm:px-6">
          {isEmpty ? (
            <EmptyState
              icon={Inbox}
              title={t("finance.noRequests")}
              description={t("finance.noRequestsDesc")}
            />
          ) : (
            <>
              <div className={cn(busy && "opacity-60 transition-opacity")}>
                <ResponsiveTableLayout
                  measureKey={`${rows.length}-${locale}-${page}-${filter}`}
                  cards={
                    <ul className="space-y-3 px-4">
                      {rows.map((p) => (
                        <li key={p.id}>
                          <div
                            role="button"
                            tabIndex={0}
                            onClick={() => openDetail(p)}
                            onKeyDown={(e) => {
                              if (e.key === "Enter" || e.key === " ") {
                                e.preventDefault()
                                openDetail(p)
                              }
                            }}
                            className="w-full cursor-pointer rounded-lg border border-border bg-card p-4 text-left outline-none transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background active:bg-muted/60"
                          >
                            <div className="flex items-start justify-between gap-3">
                              <div className="min-w-0">
                                <p className="text-xs text-muted-foreground">
                                  {formatDateTime(p.created_at, locale)}
                                </p>
                                <p className="truncate font-medium text-foreground">{p.user_name}</p>
                                <p className="font-mono text-xs text-muted-foreground">
                                  {p.reference}
                                </p>
                              </div>
                              <StatusBadge status={p.status} />
                            </div>

                            <div className="mt-3 grid grid-cols-2 gap-3 border-t border-border pt-3">
                              <div>
                                <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                                  {t("table.countryCurrency")}
                                </p>
                                <p className="text-sm text-foreground">
                                  {p.country} · {p.currency}
                                </p>
                              </div>
                              <div className="text-right">
                                <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                                  {t("table.localAmount")}
                                </p>
                                <p className="font-mono text-sm tabular-nums text-foreground">
                                  {formatCurrency(p.local_amount, p.currency)}
                                </p>
                              </div>
                              <div>
                                <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                                  {t("table.rate")}
                                </p>
                                <p className="font-mono text-sm tabular-nums text-muted-foreground">
                                  {p.exchange_rate}
                                </p>
                              </div>
                              <div className="text-right">
                                <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                                  {t("table.eurTotal")}
                                </p>
                                <p className="font-mono text-sm font-medium tabular-nums text-foreground">
                                  {formatCurrency(p.eur_amount, "EUR")}
                                </p>
                              </div>
                            </div>

                            {p.status === "pending" && (
                              <div className="mt-3 flex gap-2 border-t border-border pt-3">
                                <Button
                                  size="sm"
                                  variant="outline"
                                  className={cn("flex-1 gap-1.5", APPROVE_BUTTON_CLASS)}
                                  onClick={(e) => promptDecision(p, "approved", e)}
                                >
                                  <Check className="size-4" />
                                  {t("action.approve")}
                                </Button>
                                <Button
                                  size="sm"
                                  variant="outline"
                                  className={cn("flex-1 gap-1.5", REJECT_BUTTON_CLASS)}
                                  onClick={(e) => promptDecision(p, "rejected", e)}
                                >
                                  <X className="size-4" />
                                  {t("action.reject")}
                                </Button>
                              </div>
                            )}
                          </div>
                        </li>
                      ))}
                    </ul>
                  }
                  table={
                    <Table scrollable={false}>
                      <TableHeader>
                        <TableRow>
                          <TableHead>{t("table.date")}</TableHead>
                          <TableHead>{t("table.user")}</TableHead>
                          <TableHead>{t("table.countryCurrency")}</TableHead>
                          <TableHead className="text-right">{t("table.localAmount")}</TableHead>
                          <TableHead className="text-right">{t("table.rate")}</TableHead>
                          <TableHead className="text-right">{t("table.eurTotal")}</TableHead>
                          <TableHead>{t("table.status")}</TableHead>
                          <TableHead className="text-right">{t("table.actions")}</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {rows.map((p) => (
                          <TableRow
                            key={p.id}
                            className={TABLE_ROW_CLASS}
                            onClick={() => openDetail(p)}
                          >
                            <TableCell className="whitespace-nowrap text-muted-foreground">
                              {formatDateTime(p.created_at, locale)}
                            </TableCell>
                            <TableCell>
                              <div className="font-medium text-foreground">{p.user_name}</div>
                              <div className="font-mono text-xs text-muted-foreground">
                                {p.reference}
                              </div>
                            </TableCell>
                            <TableCell className="whitespace-nowrap text-muted-foreground">
                              {p.country} · {p.currency}
                            </TableCell>
                            <TableCell className="text-right font-mono tabular-nums">
                              {formatCurrency(p.local_amount, p.currency)}
                            </TableCell>
                            <TableCell className="text-right font-mono text-xs text-muted-foreground tabular-nums">
                              {p.exchange_rate}
                            </TableCell>
                            <TableCell className="text-right font-mono font-medium tabular-nums">
                              {formatCurrency(p.eur_amount, "EUR")}
                            </TableCell>
                            <TableCell>
                              <StatusBadge status={p.status} />
                            </TableCell>
                            <TableCell className="text-right">
                              {p.status === "pending" ? (
                                <div className="flex justify-end gap-1">
                                  <Button
                                    size="icon-lg"
                                    variant="outline"
                                    aria-label={t("action.approve")}
                                    className={APPROVE_BUTTON_CLASS}
                                    onClick={(e) => promptDecision(p, "approved", e)}
                                  >
                                    <Check className="size-4" />
                                  </Button>
                                  <Button
                                    size="icon-lg"
                                    variant="outline"
                                    aria-label={t("action.reject")}
                                    className={REJECT_BUTTON_CLASS}
                                    onClick={(e) => promptDecision(p, "rejected", e)}
                                  >
                                    <X className="size-4" />
                                  </Button>
                                </div>
                              ) : (
                                <span className="text-xs text-muted-foreground">—</span>
                              )}
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

      <PaymentDetailDialog payment={selected} open={open} onOpenChange={setOpen} />

      <PaymentDecisionDialog
        payment={decisionContext?.payment ?? null}
        decision={decisionContext?.decision ?? null}
        open={decisionContext !== null}
        onOpenChange={(next) => {
          if (!next) setDecisionContext(null)
        }}
        onConfirm={confirmDecision}
      />
    </div>
  )
}
