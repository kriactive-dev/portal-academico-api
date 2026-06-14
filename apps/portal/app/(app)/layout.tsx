"use client"

import { useEffect } from "react"
import { useRouter } from "next/navigation"

import { useAuthStore } from "@workspace/mock-data/stores"

import { BottomNav } from "@/components/bottom-nav"
import { SiteHeader } from "@/components/site-header"

export default function AppLayout({
  children,
}: {
  children: React.ReactNode
}) {
  const router = useRouter()
  const estudanteAtualId = useAuthStore((state) => state.estudanteAtualId)

  useEffect(() => {
    if (!estudanteAtualId) {
      router.replace("/login")
    }
  }, [estudanteAtualId, router])

  if (!estudanteAtualId) {
    return null
  }

  return (
    <div className="mx-auto flex min-h-svh max-w-md flex-col">
      <SiteHeader />
      <main className="flex flex-1 flex-col gap-4 p-4">{children}</main>
      <BottomNav />
    </div>
  )
}
