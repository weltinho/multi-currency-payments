"use client"

import { TableHead } from "@/components/ui/table"
import { cn } from "@/lib/utils"
import type { PaymentSortKey, SortDirection } from "@/lib/sort-payments"
import { ChevronDown, ChevronUp } from "lucide-react"

export function SortableTableHead({
  label,
  sortKey,
  activeSort,
  direction,
  onSort,
  className,
  align = "left",
}: {
  label: string
  sortKey: PaymentSortKey
  activeSort: PaymentSortKey
  direction: SortDirection
  onSort: (key: PaymentSortKey) => void
  className?: string
  align?: "left" | "right"
}) {
  const active = activeSort === sortKey

  return (
    <TableHead
      className={className}
      aria-sort={active ? (direction === "asc" ? "ascending" : "descending") : "none"}
    >
      <div className={cn("flex", align === "right" && "justify-end")}>
        <button
          type="button"
          onClick={() => onSort(sortKey)}
          className={cn(
            "inline-flex items-center gap-1 rounded px-1 py-0.5 font-medium",
            "hover:text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring",
            active ? "text-foreground" : "text-muted-foreground",
          )}
        >
          <span>{label}</span>
          <span className="inline-flex flex-col" aria-hidden="true">
            <ChevronUp
              className={cn(
                "size-3",
                active && direction === "asc" ? "text-foreground" : "text-muted-foreground/35",
              )}
            />
            <ChevronDown
              className={cn(
                "-mt-1 size-3",
                active && direction === "desc" ? "text-foreground" : "text-muted-foreground/35",
              )}
            />
          </span>
        </button>
      </div>
    </TableHead>
  )
}
