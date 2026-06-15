"use client"

import { useLayoutEffect, useRef, useState } from "react"

interface ResponsiveTableLayoutProps {
  cards: React.ReactNode
  table: React.ReactNode
  /** Bust the layout measurement when row data or locale changes. */
  measureKey?: string | number
}

/**
 * Shows the table when it fits the container; otherwise falls back to cards.
 * Never allows horizontal table overflow.
 */
export function ResponsiveTableLayout({
  cards,
  table,
  measureKey,
}: ResponsiveTableLayoutProps) {
  const containerRef = useRef<HTMLDivElement>(null)
  const measureRef = useRef<HTMLDivElement>(null)
  const [useCards, setUseCards] = useState(true)

  useLayoutEffect(() => {
    const container = containerRef.current
    const measure = measureRef.current
    if (!container || !measure) {
      return
    }

    const check = () => {
      const tableEl = measure.querySelector<HTMLTableElement>('[data-slot="table"]')
      if (!tableEl) {
        setUseCards(true)
        return
      }

      const needed = tableEl.scrollWidth
      const available = container.clientWidth
      setUseCards(needed > available - 1)
    }

    check()

    const observer = new ResizeObserver(check)
    observer.observe(container)
    observer.observe(measure)

    return () => observer.disconnect()
  }, [measureKey])

  return (
    <div ref={containerRef} className="w-full min-w-0">
      <div
        ref={measureRef}
        className="pointer-events-none fixed top-0 -left-[10000px] opacity-0 [&_[data-slot=table-container]]:w-max [&_[data-slot=table]]:w-max"
        aria-hidden
      >
        {table}
      </div>
      {useCards ? cards : table}
    </div>
  )
}
