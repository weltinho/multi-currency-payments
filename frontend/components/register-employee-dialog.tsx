"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { useLanguage } from "@/components/language-provider"
import { registerEmployee } from "@/lib/api"
import { EMPLOYEE_COUNTRY_PROFILES } from "@/lib/employee-countries"
import { ApiError } from "@/lib/http"
import { CheckCircle2, Loader2, UserPlus } from "lucide-react"

interface RegisterEmployeeDialogProps {
  onCreated?: () => void
}

export function RegisterEmployeeDialog({ onCreated }: RegisterEmployeeDialogProps) {
  const { t } = useLanguage()
  const [open, setOpen] = useState(false)
  const [name, setName] = useState("")
  const [email, setEmail] = useState("")
  const [countryCode, setCountryCode] = useState("BR")
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState<string | null>(null)

  function resetForm() {
    setName("")
    setEmail("")
    setCountryCode("BR")
    setError(null)
    setSuccess(null)
  }

  function handleOpenChange(next: boolean) {
    setOpen(next)
    if (!next) {
      resetForm()
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setSubmitting(true)
    setError(null)
    setSuccess(null)

    const trimmedName = name.trim()

    try {
      const created = await registerEmployee({
        name: trimmedName,
        email: email.trim(),
        country_code: countryCode,
      })
      setSuccess(
        t("finance.registerSuccess", {
          name: created.name,
          password: trimmedName,
        }),
      )
      onCreated?.()
      setTimeout(() => {
        setOpen(false)
        resetForm()
      }, 2200)
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        const body = err.body as { errors?: Record<string, string[]>; message?: string }
        const firstError = body.errors
          ? Object.values(body.errors).flat()[0]
          : body.message
        setError(firstError ?? t("finance.registerError"))
      } else {
        setError(t("finance.registerError"))
      }
    } finally {
      setSubmitting(false)
    }
  }

  const canSubmit =
    name.trim().length > 0 &&
    email.trim().length > 0 &&
    countryCode.length === 2

  return (
    <>
      <Button type="button" className="gap-2" onClick={() => setOpen(true)}>
        <UserPlus className="size-4" />
        {t("finance.addEmployee")}
      </Button>

      <Dialog open={open} onOpenChange={handleOpenChange}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{t("finance.registerEmployee")}</DialogTitle>
          <DialogDescription>{t("finance.addEmployeeDesc")}</DialogDescription>
        </DialogHeader>

        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="space-y-2">
            <Label htmlFor="employee-name">{t("finance.employeeName")}</Label>
            <Input
              id="employee-name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder={t("finance.employeeName")}
              maxLength={255}
              required
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="employee-email">{t("finance.employeeEmail")}</Label>
            <Input
              id="employee-email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="name@company.com"
              maxLength={255}
              required
            />
          </div>

          <p className="rounded-lg border border-border bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
            {t("finance.employeeInitialPasswordHint")}
          </p>

          <div className="space-y-2">
            <Label htmlFor="employee-country">{t("finance.employeeCountry")}</Label>
            <Select value={countryCode} onValueChange={(v) => setCountryCode(v ?? "BR")}>
              <SelectTrigger id="employee-country">
                <SelectValue placeholder={t("finance.employeeCountryPlaceholder")} />
              </SelectTrigger>
              <SelectContent>
                {EMPLOYEE_COUNTRY_PROFILES.map((profile) => (
                  <SelectItem key={profile.country_code} value={profile.country_code}>
                    {profile.country} · {profile.currency}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {error && <p className="text-sm text-destructive">{error}</p>}

          {success && (
            <p className="flex items-center gap-2 text-sm text-status-approved-fg">
              <CheckCircle2 className="size-4 shrink-0" />
              {success}
            </p>
          )}

          <div className="flex justify-end gap-2 pt-2">
            <Button type="button" variant="outline" onClick={() => setOpen(false)} disabled={submitting}>
              {t("finance.registerCancel")}
            </Button>
            <Button type="submit" disabled={submitting || !canSubmit} className="gap-2">
              {submitting ? <Loader2 className="size-4 animate-spin" /> : <UserPlus className="size-4" />}
              {t("finance.registerSubmit")}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
    </>
  )
}
