"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { useLanguage } from "@/components/language-provider"
import { formatCurrency } from "@/lib/data"
import type { PaymentRequest, PaymentStatus } from "@/lib/types"
import { Check, Loader2, X } from "lucide-react"

export type PaymentDecision = Extract<PaymentStatus, "approved" | "rejected">

interface PaymentDecisionDialogProps {
  payment: PaymentRequest | null
  decision: PaymentDecision | null
  open: boolean
  onOpenChange: (open: boolean) => void
  onConfirm: (paymentId: number, decision: PaymentDecision) => Promise<void>
}

export function PaymentDecisionDialog({
  payment,
  decision,
  open,
  onOpenChange,
  onConfirm,
}: PaymentDecisionDialogProps) {
  const { t } = useLanguage()
  const [submitting, setSubmitting] = useState(false)

  if (!payment || !decision) {
    return null
  }

  const paymentRecord = payment
  const decisionValue = decision
  const isApprove = decisionValue === "approved"
  const title = isApprove ? t("finance.confirmApproveTitle") : t("finance.confirmRejectTitle")
  const description = t("finance.confirmDecisionDesc", {
    name: paymentRecord.user_name,
    reference: paymentRecord.reference,
    eurAmount: formatCurrency(paymentRecord.eur_amount, "EUR"),
    localAmount: formatCurrency(paymentRecord.local_amount, paymentRecord.currency),
  })

  async function handleConfirm() {
    setSubmitting(true)
    try {
      await onConfirm(paymentRecord.id, decisionValue)
      onOpenChange(false)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Dialog open={open} onOpenChange={(next) => !submitting && onOpenChange(next)}>
      <DialogContent className="sm:max-w-md" showCloseButton={!submitting}>
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription>{description}</DialogDescription>
          {!isApprove && (
            <p className="text-sm text-muted-foreground">{t("finance.confirmRejectHint")}</p>
          )}
        </DialogHeader>
        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            disabled={submitting}
            onClick={() => onOpenChange(false)}
          >
            {t("finance.registerCancel")}
          </Button>
          <Button
            type="button"
            disabled={submitting}
            className={
              isApprove
                ? "gap-1.5 bg-status-approved-bg text-status-approved-fg hover:bg-status-approved-bg/80"
                : "gap-1.5 bg-status-rejected-bg text-status-rejected-fg hover:bg-status-rejected-bg/80"
            }
            onClick={handleConfirm}
          >
            {submitting ? (
              <Loader2 className="size-4 animate-spin" />
            ) : isApprove ? (
              <Check className="size-4" />
            ) : (
              <X className="size-4" />
            )}
            {isApprove ? t("action.approve") : t("action.reject")}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
