import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

/** First word of a full name — matches backend PersonName::firstName(). */
export function getFirstName(fullName: string): string {
  const trimmed = fullName.trim()
  if (!trimmed) {
    return ""
  }
  return trimmed.split(/\s+/)[0] ?? trimmed
}

