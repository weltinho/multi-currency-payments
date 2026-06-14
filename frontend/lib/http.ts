/**
 * HTTP client for the Laravel API (Sanctum, cookie-based / same-origin).
 * ---------------------------------------------------------------------
 * The SPA and the API share the same top-level origin:
 *   site.com        -> this Next.js front-end
 *   site.com/api    -> the Laravel API
 *
 * Because they are same-origin, Sanctum's stateful (cookie) guard is used:
 *   1. Before any mutating request we hit `/sanctum/csrf-cookie` once so Laravel
 *      sets the `XSRF-TOKEN` cookie.
 *   2. Every request is sent with `credentials: "include"` so the session cookie
 *      travels automatically.
 *   3. For non-GET requests we echo the CSRF token back in the `X-XSRF-TOKEN` header.
 *
 * No token is ever stored in JavaScript — the session lives in an HttpOnly cookie,
 * which is why this flow is preferred for browser SPAs (XSS cannot read it).
 */

import { SUPPORTED_LANGUAGE_CODES } from "./i18n"

/** Backend prefix. Same origin, so a relative path is enough (no CORS). */
export const API_BASE = "/api"

const LANGUAGE_STORAGE_KEY = "buzzvell.language"

/** Mirrors the UI language so API responses match the screen locale. */
export function getAppLanguage(): string {
  if (typeof window === "undefined") return "en"
  const stored = localStorage.getItem(LANGUAGE_STORAGE_KEY)
  if (stored && SUPPORTED_LANGUAGE_CODES.includes(stored as (typeof SUPPORTED_LANGUAGE_CODES)[number])) {
    return stored
  }
  return document.documentElement.lang || "en"
}

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public body?: unknown,
  ) {
    super(message)
    this.name = "ApiError"
  }
}

function readCookie(name: string): string | null {
  if (typeof document === "undefined") return null
  const match = document.cookie.match(new RegExp("(^|; )" + name + "=([^;]*)"))
  return match ? decodeURIComponent(match[2]) : null
}

let csrfRequested = false

/** Fetch the CSRF cookie exactly once per session before the first mutation. */
export async function ensureCsrfCookie(): Promise<void> {
  if (csrfRequested) return
  await fetch("/sanctum/csrf-cookie", { credentials: "include" })
  csrfRequested = true
}

type ApiFetchOptions = Omit<RequestInit, "body"> & { body?: unknown }

/** Thin wrapper around fetch that applies the Sanctum cookie conventions. */
export async function apiFetch<T>(path: string, options: ApiFetchOptions = {}): Promise<T> {
  const method = (options.method ?? "GET").toUpperCase()
  const mutating = !["GET", "HEAD", "OPTIONS"].includes(method)

  if (mutating) await ensureCsrfCookie()

  const headers: Record<string, string> = {
    Accept: "application/json",
    "X-App-Language": getAppLanguage(),
    "Accept-Language": getAppLanguage(),
    ...(options.body !== undefined ? { "Content-Type": "application/json" } : {}),
    ...(mutating ? { "X-XSRF-TOKEN": readCookie("XSRF-TOKEN") ?? "" } : {}),
    ...(options.headers as Record<string, string> | undefined),
  }

  const res = await fetch(`${API_BASE}${path}`, {
    credentials: "include",
    ...options,
    method,
    headers,
    body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
  })

  if (res.status === 204) return undefined as T

  const isJson = res.headers.get("content-type")?.includes("application/json")
  const payload = isJson ? await res.json() : await res.text()

  if (!res.ok) {
    const message =
      (isJson && (payload as { message?: string })?.message) ||
      `Request to ${path} failed with ${res.status}`
    throw new ApiError(res.status, message, payload)
  }

  return payload as T
}
