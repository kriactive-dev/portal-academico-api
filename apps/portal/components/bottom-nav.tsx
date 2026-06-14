"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import {
  FileTextIcon,
  HomeIcon,
  PlusCircleIcon,
  UserIcon,
  type LucideIcon,
} from "lucide-react"

import { cn } from "@workspace/ui/lib/utils"

interface NavItem {
  titulo: string
  url: string
  icon: LucideIcon
  match: (pathname: string) => boolean
}

const NAV_ITEMS: NavItem[] = [
  {
    titulo: "Início",
    url: "/",
    icon: HomeIcon,
    match: (pathname) => pathname === "/",
  },
  {
    titulo: "Pedidos",
    url: "/pedidos",
    icon: FileTextIcon,
    match: (pathname) =>
      pathname === "/pedidos" ||
      (pathname.startsWith("/pedidos/") && !pathname.startsWith("/pedidos/novo")),
  },
  {
    titulo: "Novo Pedido",
    url: "/pedidos/novo",
    icon: PlusCircleIcon,
    match: (pathname) => pathname.startsWith("/pedidos/novo"),
  },
  {
    titulo: "Perfil",
    url: "/perfil",
    icon: UserIcon,
    match: (pathname) => pathname.startsWith("/perfil"),
  },
]

export function BottomNav() {
  const pathname = usePathname()

  return (
    <nav className="sticky bottom-0 z-10 flex border-t bg-background">
      {NAV_ITEMS.map((item) => {
        const active = item.match(pathname)
        return (
          <Link
            key={item.url}
            href={item.url}
            className={cn(
              "flex flex-1 flex-col items-center gap-1 py-2 text-xs",
              active ? "text-primary" : "text-muted-foreground"
            )}
          >
            <item.icon className="size-5" />
            <span>{item.titulo}</span>
          </Link>
        )
      })}
    </nav>
  )
}
