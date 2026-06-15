"use client"

import { useState } from "react"
import { AppHeader } from "@/components/app-header"
import { ChangePasswordScreen } from "@/components/change-password-screen"
import { EmployeeDashboard } from "@/components/employee-dashboard"
import { FinanceDashboard } from "@/components/finance-dashboard"
import { LoginScreen } from "@/components/login-screen"
import { logout } from "@/lib/auth"
import type { User } from "@/lib/types"

type AuthState = {
  user: User
  pendingPassword?: string
}

export default function Page() {
  const [auth, setAuth] = useState<AuthState | null>(null)

  async function handleLogout() {
    try {
      await logout()
    } finally {
      setAuth(null)
    }
  }

  if (!auth) {
    return (
      <LoginScreen
        onLogin={(user, password) => {
          setAuth({ user, pendingPassword: password })
        }}
      />
    )
  }

  const { user, pendingPassword } = auth

  if (user.must_change_password) {
    return (
      <ChangePasswordScreen
        user={user}
        initialPassword={pendingPassword ?? ""}
        onPasswordChanged={(updated) => {
          setAuth({ user: updated })
        }}
      />
    )
  }

  return (
    <div className="min-h-screen bg-background text-foreground">
      <AppHeader user={user} onLogout={handleLogout} />
      {user.role === "finance" ? (
        <FinanceDashboard />
      ) : (
        <EmployeeDashboard user={user} />
      )}
    </div>
  )
}
