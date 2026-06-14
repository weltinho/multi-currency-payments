"use client"

import { useEffect, useState } from "react"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { useLanguage } from "@/components/language-provider"
import { fetchTestUsers, type TestUserAccount } from "@/lib/test-users"
import { Check, Copy, Loader2 } from "lucide-react"

const TEST_PASSWORD = "123456"

function UserAccountList({
  accounts,
  copiedEmail,
  onCopyEmail,
}: {
  accounts: TestUserAccount[]
  copiedEmail: string | null
  onCopyEmail: (email: string) => void
}) {
  const { t } = useLanguage()

  return (
    <ul className="space-y-2 text-sm">
      {accounts.map((account) => {
        const copied = copiedEmail === account.email

        return (
          <li
            key={account.email}
            className="rounded-lg border border-border px-3 py-2"
          >
            <p className="font-medium text-foreground">{account.name}</p>
            <div className="mt-0.5 flex items-center gap-1">
              <p className="min-w-0 flex-1 truncate font-mono text-xs text-muted-foreground">
                {account.email}
              </p>
              <Button
                type="button"
                variant="ghost"
                size="icon-xs"
                className="shrink-0 text-muted-foreground"
                aria-label={t("login.testInstructionsCopyEmail", { email: account.email })}
                title={copied ? t("login.testInstructionsCopied") : t("login.testInstructionsCopyEmail", { email: account.email })}
                onClick={() => onCopyEmail(account.email)}
              >
                {copied ? <Check /> : <Copy />}
              </Button>
            </div>
            <p className="mt-0.5 text-xs text-muted-foreground">
              {account.country} · {account.currency}
            </p>
          </li>
        )
      })}
    </ul>
  )
}

export function LoginTestInstructionsDialog() {
  const { t } = useLanguage()
  const [open, setOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(false)
  const [finance, setFinance] = useState<TestUserAccount[]>([])
  const [employees, setEmployees] = useState<TestUserAccount[]>([])
  const [copiedEmail, setCopiedEmail] = useState<string | null>(null)

  useEffect(() => {
    if (!open) return

    let cancelled = false
    setLoading(true)
    setError(false)

    fetchTestUsers()
      .then((data) => {
        if (cancelled) return
        setFinance(data.finance)
        setEmployees(data.employees)
      })
      .catch(() => {
        if (cancelled) return
        setError(true)
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [open])

  async function handleCopyEmail(email: string) {
    try {
      await navigator.clipboard.writeText(email)
      setCopiedEmail(email)
      window.setTimeout(() => {
        setCopiedEmail((current) => (current === email ? null : current))
      }, 2000)
    } catch {
      // Clipboard may be unavailable outside secure contexts.
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <div className="text-center">
        <Button
          type="button"
          variant="link"
          className="h-auto p-0 text-xs text-muted-foreground"
          onClick={() => setOpen(true)}
        >
          {t("login.testInstructionsLink")}
        </Button>
      </div>

      <DialogContent className="max-h-[90vh] gap-0 overflow-hidden p-0 sm:max-w-md">
        <DialogHeader className="border-b border-border p-6 pb-4 text-left">
          <DialogTitle>{t("login.testInstructionsTitle")}</DialogTitle>
          <DialogDescription>{t("login.testInstructionsDesc")}</DialogDescription>
        </DialogHeader>

        <div className="max-h-[min(60vh,28rem)] space-y-4 overflow-y-auto p-6 pt-4">
          <p className="rounded-lg border border-border bg-muted/40 px-3 py-2 text-sm font-medium">
            {t("login.testInstructionsPassword", { password: TEST_PASSWORD })}
          </p>

          {loading && (
            <div className="flex items-center justify-center gap-2 py-8 text-sm text-muted-foreground">
              <Loader2 className="size-4 animate-spin" />
              {t("common.loading")}
            </div>
          )}

          {error && (
            <p className="text-sm text-destructive" role="alert">
              {t("login.testInstructionsError")}
            </p>
          )}

          {!loading && !error && (
            <>
              <div className="space-y-2">
                <p className="text-sm font-medium text-foreground">
                  {t("login.testInstructionsFinance")} ({finance.length})
                </p>
                <UserAccountList
                  accounts={finance}
                  copiedEmail={copiedEmail}
                  onCopyEmail={handleCopyEmail}
                />
              </div>

              <div className="space-y-2">
                <p className="text-sm font-medium text-foreground">
                  {t("login.testInstructionsEmployees")} ({employees.length})
                </p>
                <UserAccountList
                  accounts={employees}
                  copiedEmail={copiedEmail}
                  onCopyEmail={handleCopyEmail}
                />
              </div>
            </>
          )}
        </div>

        <DialogFooter showCloseButton={false} className="border-t border-border">
          <Button type="button" className="w-full sm:w-auto" onClick={() => setOpen(false)}>
            {t("login.testInstructionsClose")}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
