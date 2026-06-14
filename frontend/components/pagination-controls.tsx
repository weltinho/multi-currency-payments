"use client"

import { Button } from "@/components/ui/button"
import { useLanguage } from "@/components/language-provider"
import type { Paginated } from "@/lib/types"
import { ChevronLeft, ChevronRight } from "lucide-react"

interface PaginationControlsProps {
  /** The Laravel-style paginator meta returned by the API. */
  meta: Pick<
    Paginated<unknown>,
    "current_page" | "last_page" | "from" | "to" | "total"
  >
  onPageChange: (page: number) => void
  /** Disables the buttons while a new page is being fetched. */
  isLoading?: boolean
}

/**
 * Footer controls for a paginated list. Reads its meta straight from the
 * Laravel `LengthAwarePaginator` shape so it works unchanged once the mock
 * API is swapped for the real backend.
 */
export function PaginationControls({
  meta,
  onPageChange,
  isLoading = false,
}: PaginationControlsProps) {
  const { t } = useLanguage()
  const { current_page, last_page, from, to, total } = meta

  const canPrev = current_page > 1
  const canNext = current_page < last_page

  return (
    <div className="flex flex-col items-center justify-between gap-3 border-t border-border px-4 py-3 sm:flex-row sm:px-0">
      <p className="text-xs text-muted-foreground tabular-nums" aria-live="polite">
        {t("pagination.range", { from, to, total })}
        <span className="mx-2 text-border" aria-hidden="true">
          ·
        </span>
        {t("pagination.page", { current: current_page, last: last_page })}
      </p>

      <div className="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          className="gap-1"
          disabled={!canPrev || isLoading}
          onClick={() => onPageChange(current_page - 1)}
        >
          <ChevronLeft className="size-4" />
          {t("pagination.prev")}
        </Button>
        <Button
          variant="outline"
          size="sm"
          className="gap-1"
          disabled={!canNext || isLoading}
          onClick={() => onPageChange(current_page + 1)}
        >
          {t("pagination.next")}
          <ChevronRight className="size-4" />
        </Button>
      </div>
    </div>
  )
}
