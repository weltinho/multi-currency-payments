"use client"

import { Button } from "@/components/ui/button"
import { LanguageToggle, ThemeToggle } from "@/components/app-controls"
import { useLanguage } from "@/components/language-provider"
import type { User } from "@/lib/types"
import { Building2, LogOut } from "lucide-react"

interface AppHeaderProps {
  user: User
  onLogout: () => void
}

export function AppHeader({ user, onLogout }: AppHeaderProps) {
  const { t } = useLanguage()
  const roleLabel = user.role === "finance" ? t("role.finance") : t("role.employee")

  // Abrevia o nome para poupar espaço no cartão: "Helena Marques" -> "H. Marques".
  // Nomes com uma só palavra ficam inalterados.
  const parts = user.name.trim().split(/\s+/)
  const displayName = parts.length > 1 ? `${parts[0][0]}. ${parts[parts.length - 1]}` : parts[0]

  return (
    <header className="sticky top-0 z-30 border-b border-border bg-background/80 backdrop-blur-sm">
      <div className="mx-auto flex h-14 max-w-6xl items-center justify-between gap-2 px-4 sm:px-6">
        <div className="flex min-w-0 items-center gap-2.5">
          <div className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground">
            <Building2 className="size-4" />
          </div>
          <span className="truncate text-sm font-semibold tracking-tight text-foreground">
            Buzzvel Welton Test
          </span>
          <span className="hidden rounded-full border border-border bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground sm:inline">
            {roleLabel}
          </span>
        </div>

        <div className="flex items-center gap-1 sm:gap-1.5">
          <LanguageToggle />
          <ThemeToggle />
          {/* Cartão compacto do utilizador + logout, na extrema direita.
              Avatar azul (cor de marca) nos dois temas; logout vermelho para destaque. */}
          <div className="flex items-center gap-1.5 rounded-full border border-border bg-card py-1 pl-1 pr-1">
            <div className="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-semibold uppercase text-primary-foreground">
              {parts[0][0]}
            </div>
            {/* Nome abreviado só aparece a partir de telas médias para manter o cartão pequeno no mobile. */}
            <span className="hidden max-w-[8rem] flex-col leading-tight sm:flex">
              <span className="truncate text-xs font-medium text-foreground">{displayName}</span>
              <span className="text-[10px] text-muted-foreground">
                {user.role === "finance" ? t("role.finance") : user.currency}
              </span>
            </span>
            <span className="hidden h-5 w-px bg-border sm:block" aria-hidden="true" />
            <Button
              variant="ghost"
              size="icon"
              onClick={onLogout}
              aria-label={t("header.signOut")}
              className="size-6 rounded-full text-destructive hover:bg-destructive/10 hover:text-destructive"
            >
              <LogOut className="size-3.5" />
            </Button>
          </div>
        </div>
      </div>
    </header>
  )
}
