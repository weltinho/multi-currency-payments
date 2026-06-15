"use client"

import { useId, useMemo, useState } from "react"
import { Popover as PopoverPrimitive } from "@base-ui/react/popover"
import { Input } from "@/components/ui/input"
import { useLanguage } from "@/components/language-provider"
import { CURRENCY_META } from "@/lib/data"
import {
  EMPLOYEE_COUNTRY_PROFILES,
  type EmployeeCountryProfile,
} from "@/lib/employee-countries"
import { cn } from "@/lib/utils"
import { Check, ChevronDown, Search } from "lucide-react"

function formatLabel(profile: EmployeeCountryProfile): string {
  return `${profile.country} · ${profile.currency}`
}

interface CountryComboboxProps {
  value: string
  onValueChange: (countryCode: string) => void
  id?: string
  disabled?: boolean
}

export function CountryCombobox({
  value,
  onValueChange,
  id,
  disabled,
}: CountryComboboxProps) {
  const { t } = useLanguage()
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState("")
  const listId = useId()

  const selected = EMPLOYEE_COUNTRY_PROFILES.find((p) => p.country_code === value)

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase()
    if (!q) {
      return EMPLOYEE_COUNTRY_PROFILES
    }

    return EMPLOYEE_COUNTRY_PROFILES.filter(
      (profile) =>
        profile.country.toLowerCase().includes(q) ||
        profile.country_code.toLowerCase().includes(q) ||
        profile.currency.toLowerCase().includes(q),
    )
  }, [query])

  function handleOpenChange(next: boolean) {
    setOpen(next)
    if (!next) {
      setQuery("")
    }
  }

  function select(profile: EmployeeCountryProfile) {
    onValueChange(profile.country_code)
    setOpen(false)
    setQuery("")
  }

  return (
    <PopoverPrimitive.Root open={open} onOpenChange={handleOpenChange}>
      <PopoverPrimitive.Trigger
        id={id}
        type="button"
        disabled={disabled}
        aria-expanded={open}
        aria-controls={listId}
        className={cn(
          "flex h-8 w-full items-center justify-between gap-2 rounded-lg border border-input bg-transparent px-2.5 text-sm transition-colors outline-none select-none",
          "focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50",
          "disabled:cursor-not-allowed disabled:opacity-50",
          "dark:bg-muted dark:hover:bg-secondary",
        )}
      >
        <span className="flex min-w-0 flex-1 items-center gap-2 truncate text-left">
          {selected ? (
            <>
              <span className="shrink-0 rounded border border-border bg-muted/60 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-muted-foreground">
                {CURRENCY_META[selected.currency].flag}
              </span>
              <span className="truncate">{formatLabel(selected)}</span>
            </>
          ) : (
            <span className="text-muted-foreground">
              {t("finance.employeeCountryPlaceholder")}
            </span>
          )}
        </span>
        <ChevronDown className="size-4 shrink-0 text-muted-foreground" />
      </PopoverPrimitive.Trigger>

      <PopoverPrimitive.Portal>
        <PopoverPrimitive.Positioner
          className="z-50 outline-none"
          side="bottom"
          align="start"
          sideOffset={4}
        >
          <PopoverPrimitive.Popup
            className={cn(
              "z-50 w-(--anchor-width) origin-(--transform-origin) overflow-hidden rounded-lg border border-border bg-popover text-popover-foreground shadow-md",
              "data-open:animate-in data-open:fade-in-0 data-open:zoom-in-95",
              "data-closed:animate-out data-closed:fade-out-0 data-closed:zoom-out-95",
            )}
          >
            <div className="border-b border-border p-2">
              <div className="relative">
                <Search
                  className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                  aria-hidden
                />
                <Input
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  className="pl-8"
                  autoComplete="off"
                  aria-label={t("finance.employeeCountrySearch")}
                  onKeyDown={(e) => {
                    if (e.key === "Escape") {
                      setOpen(false)
                    }
                  }}
                />
              </div>
            </div>

            <ul
              id={listId}
              role="listbox"
              className="max-h-56 overflow-y-auto p-1"
              aria-label={t("finance.employeeCountry")}
            >
              {filtered.length === 0 ? (
                <li className="px-2.5 py-6 text-center text-sm text-muted-foreground">
                  {t("finance.employeeCountryEmpty")}
                </li>
              ) : (
                filtered.map((profile) => {
                  const isSelected = profile.country_code === value

                  return (
                    <li key={profile.country_code}>
                      <button
                        type="button"
                        role="option"
                        aria-selected={isSelected}
                        onClick={() => select(profile)}
                        className={cn(
                          "flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm outline-none",
                          "hover:bg-muted focus-visible:bg-muted",
                          isSelected && "bg-muted/70",
                        )}
                      >
                        <span className="shrink-0 rounded border border-border bg-background px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-muted-foreground">
                          {CURRENCY_META[profile.currency].flag}
                        </span>
                        <span className="min-w-0 flex-1 truncate">{formatLabel(profile)}</span>
                        {isSelected && (
                          <Check className="size-4 shrink-0 text-primary" aria-hidden />
                        )}
                      </button>
                    </li>
                  )
                })
              )}
            </ul>
          </PopoverPrimitive.Popup>
        </PopoverPrimitive.Positioner>
      </PopoverPrimitive.Portal>
    </PopoverPrimitive.Root>
  )
}
