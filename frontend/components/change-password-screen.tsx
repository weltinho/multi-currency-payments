"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { useLanguage } from "@/components/language-provider"
import { changePassword } from "@/lib/auth"
import { ApiError } from "@/lib/http"
import { PASSWORD_POLICY } from "@/lib/password-policy"
import type { User } from "@/lib/types"
import { Loader2, ShieldAlert } from "lucide-react"

interface ChangePasswordScreenProps {
  user: User
  initialPassword: string
  onPasswordChanged: (user: User) => void
}

export function ChangePasswordScreen({
  user,
  initialPassword,
  onPasswordChanged,
}: ChangePasswordScreenProps) {
  const { t } = useLanguage()
  const [currentPassword, setCurrentPassword] = useState(initialPassword)
  const [password, setPassword] = useState("")
  const [passwordConfirmation, setPasswordConfirmation] = useState("")
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setSubmitting(true)
    setError(null)

    try {
      const updated = await changePassword({
        current_password: currentPassword,
        password,
        password_confirmation: passwordConfirmation,
      })
      onPasswordChanged(updated)
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        const body = err.body as { errors?: Record<string, string[]>; message?: string }
        const firstError = body.errors
          ? Object.values(body.errors).flat()[0]
          : body.message
        setError(firstError ?? t("passwordChange.error"))
      } else {
        setError(t("passwordChange.error"))
      }
    } finally {
      setSubmitting(false)
    }
  }

  const canSubmit =
    currentPassword.length > 0 &&
    PASSWORD_POLICY.isValid(password) &&
    password === passwordConfirmation

  return (
    <main className="flex min-h-screen items-center justify-center bg-background px-4 py-12">
      <Card className="w-full max-w-md border-border shadow-sm">
        <CardHeader className="space-y-3 text-center">
          <div className="mx-auto flex size-12 items-center justify-center rounded-full bg-status-pending-bg text-status-pending-fg">
            <ShieldAlert className="size-6" />
          </div>
          <CardTitle>{t("passwordChange.title")}</CardTitle>
          <CardDescription>
            {t("passwordChange.description", { name: user.name })}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form className="space-y-4" onSubmit={handleSubmit}>
            <div className="space-y-2">
              <Label htmlFor="current-password">{t("passwordChange.currentPassword")}</Label>
              <Input
                id="current-password"
                type="password"
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
                autoComplete="current-password"
                required
              />
              <p className="text-xs text-muted-foreground">
                {t("passwordChange.currentPasswordHint")}
              </p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="new-password">{t("passwordChange.newPassword")}</Label>
              <Input
                id="new-password"
                type="password"
                inputMode="numeric"
                pattern="\d{6}"
                maxLength={PASSWORD_POLICY.length}
                value={password}
                onChange={(e) => setPassword(e.target.value.replace(/\D/g, "").slice(0, PASSWORD_POLICY.length))}
                autoComplete="new-password"
                required
              />
              <p className="text-xs text-muted-foreground">
                {t("passwordChange.newPasswordHint")}
              </p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="confirm-password">{t("passwordChange.confirmPassword")}</Label>
              <Input
                id="confirm-password"
                type="password"
                inputMode="numeric"
                pattern="\d{6}"
                maxLength={PASSWORD_POLICY.length}
                value={passwordConfirmation}
                onChange={(e) =>
                  setPasswordConfirmation(
                    e.target.value.replace(/\D/g, "").slice(0, PASSWORD_POLICY.length),
                  )
                }
                autoComplete="new-password"
                required
              />
            </div>

            {error && (
              <p className="text-sm text-destructive" role="alert">
                {error}
              </p>
            )}

            <Button type="submit" className="w-full" disabled={submitting || !canSubmit}>
              {submitting && <Loader2 className="size-4 animate-spin" />}
              {t("passwordChange.submit")}
            </Button>
          </form>
        </CardContent>
      </Card>
    </main>
  )
}
