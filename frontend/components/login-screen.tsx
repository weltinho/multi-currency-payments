"use client"

import { useEffect, useState } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { LanguageToggle, ThemeToggle } from "@/components/app-controls"
import { useLanguage } from "@/components/language-provider"
import { login } from "@/lib/auth"
import { ensureCsrfCookie } from "@/lib/http"
import type { User } from "@/lib/types"
import { LoginTestInstructionsDialog } from "@/components/login-test-instructions-dialog"
import { Building2, Loader2 } from "lucide-react"

export function LoginScreen({ onLogin }: { onLogin: (user: User, password: string) => void }) {
  const { t } = useLanguage()
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [error, setError] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    void ensureCsrfCookie() // Ensure the CSRF cookie is set before the login form is submitted.
  }, []) 

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setError(false)
    setSubmitting(true)
    try {
      // Sanctum cookie login (mock-resolved in the preview); finance and
      // employees are routed to different dashboards by their role.
      const user = await login(email, password)
      onLogin(user, password)
    } catch {
      setError(true)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <main className="relative flex min-h-screen items-center justify-center bg-muted/30 px-6 py-12">
      <div className="absolute right-4 top-4 flex items-center gap-1 sm:right-6 sm:top-6">
        <LanguageToggle />
        <ThemeToggle />
      </div>

      <div className="w-full max-w-sm">
        <div className="mb-8 flex flex-col items-center text-center">
          <div className="flex size-11 items-center justify-center rounded-xl bg-primary text-primary-foreground">
            <Building2 className="size-5" />
          </div>
          <h1 className="mt-4 text-xl font-semibold tracking-tight text-foreground">
            Buzzvel Welton Test
          </h1>
          <p className="mt-1 text-sm text-muted-foreground text-balance">
            {t("brand.tagline")}
          </p>
        </div>

        <Card className="border-border shadow-sm">
          <CardContent className="p-6">
            <form className="space-y-4" autoComplete="off" onSubmit={handleSubmit}>
              <div className="space-y-2">
                <Label htmlFor="email">{t("login.email")}</Label>
                <Input
                  id="email"
                  name="login-email"
                  type="email"
                  value={email}
                  onChange={(e) => {
                    setEmail(e.target.value)
                    if (error) setError(false)
                  }}
                  autoComplete="off"
                  aria-invalid={error}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">{t("login.password")}</Label>
                <Input
                  id="password"
                  name="login-password"
                  type="password"
                  value={password}
                  onChange={(e) => {
                    setPassword(e.target.value)
                    if (error) setError(false)
                  }}
                  autoComplete="new-password"
                  aria-invalid={error}
                />
              </div>
              {error && (
                <p className="text-sm text-destructive" role="alert">
                  {t("login.invalid")}
                </p>
              )}
              <Button type="submit" className="w-full" disabled={submitting}>
                {submitting && <Loader2 className="size-4 animate-spin" />}
                {t("login.signIn")}
              </Button>
              <LoginTestInstructionsDialog />
            </form>
          </CardContent>
        </Card>
      </div>
    </main>
  )
}
