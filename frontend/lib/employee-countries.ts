import type { CurrencyCode } from "./types"

export interface EmployeeCountryProfile {
  country: string
  country_code: string
  currency: CurrencyCode
}

/** Mirrors backend EmployeeCountryProfiles for finance employee registration. */
export const EMPLOYEE_COUNTRY_PROFILES: EmployeeCountryProfile[] = [
  { country: "Brazil", country_code: "BR", currency: "BRL" },
  { country: "United States", country_code: "US", currency: "USD" },
  { country: "United Kingdom", country_code: "GB", currency: "GBP" },
  { country: "Japan", country_code: "JP", currency: "JPY" },
  { country: "Portugal", country_code: "PT", currency: "EUR" },
  { country: "Germany", country_code: "DE", currency: "EUR" },
  { country: "France", country_code: "FR", currency: "EUR" },
  { country: "Ireland", country_code: "IE", currency: "EUR" },
  { country: "India", country_code: "IN", currency: "INR" },
  { country: "South Korea", country_code: "KR", currency: "KRW" },
  { country: "Spain", country_code: "ES", currency: "EUR" },
  { country: "Italy", country_code: "IT", currency: "EUR" },
  { country: "Poland", country_code: "PL", currency: "PLN" },
  { country: "Canada", country_code: "CA", currency: "CAD" },
  { country: "Australia", country_code: "AU", currency: "AUD" },
  { country: "Mexico", country_code: "MX", currency: "MXN" },
  { country: "United Arab Emirates", country_code: "AE", currency: "AED" },
  { country: "Sweden", country_code: "SE", currency: "SEK" },
  { country: "South Africa", country_code: "ZA", currency: "ZAR" },
  { country: "Singapore", country_code: "SG", currency: "SGD" },
]
