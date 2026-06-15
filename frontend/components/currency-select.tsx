"use client"

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { CURRENCY_META } from "@/lib/data"
import type { CurrencyCode } from "@/lib/types"

const ALL_CURRENCIES = Object.keys(CURRENCY_META) as CurrencyCode[]

function orderedCurrencies(defaultCurrency: CurrencyCode): CurrencyCode[] {
  const others = ALL_CURRENCIES.filter((code) => code !== defaultCurrency).sort((a, b) =>
    a.localeCompare(b),
  )

  return [defaultCurrency, ...others]
}

interface CurrencySelectProps {
  value: CurrencyCode
  onValueChange: (currency: CurrencyCode) => void
  defaultCurrency: CurrencyCode
  defaultLabel?: string
  id?: string
  disabled?: boolean
}

export function CurrencySelect({
  value,
  onValueChange,
  defaultCurrency,
  defaultLabel = "default",
  id,
  disabled,
}: CurrencySelectProps) {
  const options = orderedCurrencies(defaultCurrency)

  return (
    <Select
      value={value}
      onValueChange={(next) => {
        if (next) {
          onValueChange(next as CurrencyCode)
        }
      }}
      disabled={disabled}
    >
      <SelectTrigger id={id} className="w-full">
        <SelectValue />
      </SelectTrigger>
      <SelectContent>
        {options.map((code) => (
          <SelectItem key={code} value={code}>
            <span className="flex items-center gap-2">
              <span className="rounded border border-border bg-muted/60 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-muted-foreground">
                {CURRENCY_META[code].flag}
              </span>
              <span>
                {code} · {CURRENCY_META[code].label}
              </span>
              {code === defaultCurrency && (
                <span className="text-xs text-muted-foreground">({defaultLabel})</span>
              )}
            </span>
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  )
}
