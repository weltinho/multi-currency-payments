import { apiFetch, ensureCsrfCookie } from "./http"

export interface TestUserAccount {
  name: string
  email: string
  country: string
  currency: string
}

export interface TestUsersResponse {
  finance: TestUserAccount[]
  employees: TestUserAccount[]
}

export async function fetchTestUsers(): Promise<TestUsersResponse> {
  await ensureCsrfCookie()
  return apiFetch<TestUsersResponse>("/test-users")
}
