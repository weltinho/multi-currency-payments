"use client"

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Separator } from "@/components/ui/separator"
import { StatusBadge } from "@/components/status-badge"
import { useLanguage } from "@/components/language-provider"
import {
  CURRENCY_META,
  formatCurrency,
  formatDateTime,
} from "@/lib/data"
import type { PaymentRequest } from "@/lib/types"
import type { TranslationKey } from "@/lib/i18n"
import { cn } from "@/lib/utils"
import { Check, Clock, FileText, Globe, X } from "lucide-react"

interface PaymentDetailDialogProps {
  payment: PaymentRequest | null
  open: boolean
  onOpenChange: (open: boolean) => void
}

type TimelineState = "done" | "current" | "rejected" | "pending"

interface TimelineStep {
  labelKey: TranslationKey
  descKey: TranslationKey
  state: TimelineState
}

function buildTimeline(payment: PaymentRequest): TimelineStep[] {
  const finalStep: TimelineStep =
    payment.status === "approved"
      ? { labelKey: "timeline.approved", descKey: "timeline.approvedDesc", state: "done" }
      : payment.status === "rejected"
        ? { labelKey: "timeline.rejected", descKey: "timeline.rejectedDesc", state: "rejected" }
        : payment.status === "expired"
          ? { labelKey: "timeline.expired", descKey: "timeline.expiredDesc", state: "rejected" }
          : { labelKey: "timeline.finalStatus", descKey: "timeline.finalStatusDesc", state: "pending" }

  return [
    { labelKey: "timeline.created", descKey: "timeline.createdDesc", state: "done" },
    {
      labelKey: "timeline.review",
      descKey: "timeline.reviewDesc",
      state: payment.status === "pending" ? "current" : "done",
    },
    finalStep,
  ]
}

function MetaBlock({
  icon,
  label,
  value,
}: {
  icon: React.ReactNode
  label: string
  value: string
}) {
  return (
    <div className="flex items-start gap-3 rounded-lg border border-border bg-muted/40 p-3">
      <div className="mt-0.5 text-muted-foreground">{icon}</div>
      <div className="min-w-0">
        <p className="text-xs text-muted-foreground">{label}</p>
        <p className="truncate font-mono text-sm font-medium text-foreground">{value}</p>
      </div>
    </div>
  )
}

export function PaymentDetailDialog({
  payment,
  open,
  onOpenChange,
}: PaymentDetailDialogProps) {
  const { t, locale } = useLanguage()
  if (!payment) return null

  const timeline = buildTimeline(payment)
  const currencyMeta = CURRENCY_META[payment.currency]
  const subtitleKey = `detail.subtitle.${payment.status}` as TranslationKey

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-h-[90vh] gap-0 overflow-y-auto p-0 sm:max-w-lg">
        <DialogHeader className="border-b border-border p-6 pb-4 text-left">
          <div className="flex items-center justify-between gap-3">
            <div>
              <DialogTitle className="font-mono text-base tracking-tight">
                {payment.reference}
              </DialogTitle>
              <DialogDescription className="mt-1">
                {t(subtitleKey)}
              </DialogDescription>
            </div>
            <StatusBadge status={payment.status} />
          </div>
        </DialogHeader>

        <div className="space-y-6 p-6">
          {/* Valores */}
          <div className="rounded-lg border border-border bg-card p-4">
            <div className="flex items-end justify-between">
              <div>
                <p className="text-xs text-muted-foreground">{t("detail.localAmount")}</p>
                <p className="text-2xl font-semibold tracking-tight text-foreground">
                  {formatCurrency(payment.local_amount, payment.currency)}
                </p>
              </div>
              <div className="text-right">
                <p className="text-xs text-muted-foreground">{t("detail.converted")}</p>
                <p className="text-2xl font-semibold tracking-tight text-foreground">
                  {formatCurrency(payment.eur_amount, "EUR")}
                </p>
              </div>
            </div>
            <Separator className="my-3" />
            <p className="text-sm text-muted-foreground">
              {payment.country} · {currencyMeta.label} ({payment.currency})
            </p>
            <p className="mt-3 text-xs text-muted-foreground">{t("detail.purpose")}</p>
            <p className="mt-0.5 text-sm text-foreground">{payment.description}</p>
          </div>

          {/* Metadados imutáveis */}
          <div>
            <h3 className="mb-3 text-sm font-medium text-foreground">
              {t("detail.metadata")}
            </h3>
            <div className="grid gap-3 sm:grid-cols-2">
              <MetaBlock
                icon={<FileText className="size-4" />}
                label={t("detail.lockedRate")}
                value={`1 € = ${payment.exchange_rate} ${payment.currency}`}
              />
              <MetaBlock
                icon={<Globe className="size-4" />}
                label={t("detail.dataSource")}
                value={payment.rate_source}
              />
              <MetaBlock
                icon={<Clock className="size-4" />}
                label={t("detail.captureTimestamp")}
                value={formatDateTime(payment.created_at, locale)}
              />
              <MetaBlock
                icon={<Clock className="size-4" />}
                label={
                  payment.status === "expired"
                    ? t("detail.expiredAt")
                    : t("detail.reviewedAt")
                }
                value={
                  payment.status === "expired"
                    ? formatDateTime(payment.updated_at, locale)
                    : payment.reviewed_at
                      ? formatDateTime(payment.reviewed_at, locale)
                      : "—"
                }
              />
            </div>
          </div>

          {/* Timeline */}
          <div>
            <h3 className="mb-3 text-sm font-medium text-foreground">{t("detail.timeline")}</h3>
            <ol className="relative space-y-5">
              {timeline.map((step, index) => {
                const isLast = index === timeline.length - 1
                return (
                  <li key={step.labelKey} className="relative flex gap-3">
                    {!isLast && (
                      <span
                        className="absolute left-[11px] top-6 h-[calc(100%+4px)] w-px bg-border"
                        aria-hidden="true"
                      />
                    )}
                    <span
                      className={cn(
                        "z-10 flex size-6 shrink-0 items-center justify-center rounded-full border",
                        step.state === "done" &&
                          "border-status-approved-border bg-status-approved-bg text-status-approved-fg",
                        step.state === "current" &&
                          "border-status-pending-border bg-status-pending-bg text-status-pending-fg",
                        step.state === "rejected" &&
                          "border-status-rejected-border bg-status-rejected-bg text-status-rejected-fg",
                        step.state === "pending" && "border-border bg-muted text-muted-foreground",
                      )}
                    >
                      {step.state === "rejected" ? (
                        <X className="size-3.5" />
                      ) : step.state === "done" ? (
                        <Check className="size-3.5" />
                      ) : (
                        <Clock className="size-3.5" />
                      )}
                    </span>
                    <div className="pb-1">
                      <p className="text-sm font-medium text-foreground">{t(step.labelKey)}</p>
                      <p className="text-xs text-muted-foreground">{t(step.descKey)}</p>
                    </div>
                  </li>
                )
              })}
            </ol>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  )
}
