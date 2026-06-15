import { cn } from "@/lib/utils"
import { CheckCircle2, XCircle } from "lucide-react"

export function InlineAlert({
  variant,
  message,
  className,
}: {
  variant: "success" | "error"
  message: string
  className?: string
}) {
  const Icon = variant === "success" ? CheckCircle2 : XCircle

  return (
    <div
      role="status"
      className={cn(
        "flex items-start gap-2 rounded-lg border px-3 py-2.5 text-sm",
        variant === "success" &&
          "border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200",
        variant === "error" &&
          "border-destructive/30 bg-destructive/10 text-destructive",
        className,
      )}
    >
      <Icon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
      <span>{message}</span>
    </div>
  )
}
