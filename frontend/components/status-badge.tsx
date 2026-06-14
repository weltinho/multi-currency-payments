"use client"

import { Badge } from "@/components/ui/badge"
import { useLanguage } from "@/components/language-provider"
import { STATUS_META } from "@/lib/data"
import type { PaymentStatus } from "@/lib/types"
import type { TranslationKey } from "@/lib/i18n"
import { cn } from "@/lib/utils"

export function StatusBadge({ status }: { status: PaymentStatus }) {
  const { t } = useLanguage()
  const meta = STATUS_META[status]
  return (
    <Badge
      variant="outline"
      className={cn("gap-1.5 rounded-full px-2.5 py-0.5 font-medium", meta.badge)}
    >
      <span className={cn("size-1.5 rounded-full", meta.dot)} aria-hidden="true" />
      {t(`status.${status}` as TranslationKey)}
    </Badge>
  )
}
