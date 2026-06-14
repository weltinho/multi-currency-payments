"use client"

import { createContext, useCallback, useContext, useEffect, useState } from "react"
import {
  type Language,
  type TranslationKey,
  LOCALE_MAP,
  translations,
} from "@/lib/i18n"

interface LanguageContextValue {
  language: Language
  setLanguage: (lang: Language) => void
  locale: string
  t: (key: TranslationKey, vars?: Record<string, string | number>) => string
}

const LanguageContext = createContext<LanguageContextValue | null>(null)

const STORAGE_KEY = "buzzvell.language"

export function LanguageProvider({ children }: { children: React.ReactNode }) {
  // Começa sempre em "en" para o HTML do servidor e do cliente baterem certo
  // (evita warning de hidratação); a preferência guardada é aplicada a seguir.
  const [language, setLanguageState] = useState<Language>("en")

  // Recupera o idioma escolhido em visitas anteriores, já no cliente.
  useEffect(() => {
    const stored = localStorage.getItem(STORAGE_KEY) as Language | null
    if (stored && stored in translations) {
      setLanguageState(stored)
    }
  }, [])

  // Muda o idioma, persiste a escolha e atualiza o atributo lang do <html>
  // (importante para acessibilidade e SEO).
  const setLanguage = useCallback((lang: Language) => {
    setLanguageState(lang)
    localStorage.setItem(STORAGE_KEY, lang)
    document.documentElement.lang = lang
  }, [])

  // Tradutor: procura a chave no idioma atual, cai para inglês e, em último
  // caso, devolve a própria chave. O vars permite interpolar {placeholders}.
  const t = useCallback(
    (key: TranslationKey, vars?: Record<string, string | number>) => {
      let text: string = translations[language][key] ?? translations.en[key] ?? key
      if (vars) {
        for (const [k, v] of Object.entries(vars)) {
          text = text.replace(new RegExp(`\\{${k}\\}`, "g"), String(v))
        }
      }
      return text
    },
    [language],
  )

  return (
    <LanguageContext.Provider
      value={{ language, setLanguage, locale: LOCALE_MAP[language], t }}
    >
      {children}
    </LanguageContext.Provider>
  )
}

export function useLanguage() {
  const ctx = useContext(LanguageContext)
  if (!ctx) {
    throw new Error("useLanguage must be used within a LanguageProvider")
  }
  return ctx
}
