import { apiFetch, ensureCsrfCookie, ApiError } from "./http"
import type { ChangePasswordPayload, User } from "./types"

export type { ChangePasswordPayload } from "./types"

/**
 * AUTH (Laravel Sanctum, cookie-based)
 * ------------------------------------
 * Same-origin SPA flow:
 *   1. GET  /sanctum/csrf-cookie   -> Laravel sets the XSRF-TOKEN cookie
 *   2. POST /api/login             -> validates credentials, starts the session
 *   3. GET  /api/user              -> returns the authenticated user
 *   4. POST /api/logout            -> destroys the session
 */

export class InvalidCredentialsError extends Error {
  constructor() {
    super("Invalid credentials")
    this.name = "InvalidCredentialsError"
  }
}

export async function login(email: string, password: string): Promise<User> {
  await ensureCsrfCookie()
  try {
    await apiFetch("/login", { method: "POST", body: { email, password } })
  } catch (err) {
    if (err instanceof ApiError && (err.status === 422 || err.status === 401)) {
      throw new InvalidCredentialsError()
    }
    throw err
  }
  return apiFetch<User>("/user")
}

export function logout(): Promise<void> {
  return apiFetch("/logout", { method: "POST" })
}

export async function changePassword(payload: ChangePasswordPayload): Promise<User> {
  return apiFetch<User>("/password", {
    method: "PUT",
    body: payload,
  })
}

export async function currentUser(): Promise<User | null> {
  try {
    return await apiFetch<User>("/user")
  } catch (err) {
    if (err instanceof ApiError && err.status === 401) return null
    throw err
  }
}
