"use client"

import { useState } from "react"
import { AppHeader } from "@/components/app-header"
import { EmployeeDashboard } from "@/components/employee-dashboard"
import { FinanceDashboard } from "@/components/finance-dashboard"
import { LoginScreen } from "@/components/login-screen"
import { logout } from "@/lib/auth"
import type { User } from "@/lib/types"

export default function Page() {
  const [user, setUser] = useState<User | null>(null)

  async function handleLogout() {
    // End the Sanctum session (no-op in the mock), then clear local state.
    try {
      await logout()
    } finally {
      setUser(null)
    }
  }

  if (!user) {
    return <LoginScreen onLogin={setUser} />
  }

  return (
    <div className="min-h-screen bg-background text-foreground">
      <AppHeader
        user={user}
        onLogout={handleLogout}
      />
      {user.role === "finance" ? (
        <FinanceDashboard />
      ) : (
        <EmployeeDashboard user={user} />
      )}
    </div>
  )
}
