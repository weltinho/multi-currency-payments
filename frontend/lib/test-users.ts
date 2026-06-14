import { apiFetch } from "./http"

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

export function fetchTestUsers(): Promise<TestUsersResponse> {
  return apiFetch<TestUsersResponse>("/test-users")
}
