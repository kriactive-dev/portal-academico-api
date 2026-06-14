"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import {
  BookOpenIcon,
  ContactIcon,
  GraduationCapIcon,
  InboxIcon,
  LayoutDashboardIcon,
  ReceiptIcon,
  ShieldCheckIcon,
  UsersRoundIcon,
  WalletIcon,
  type LucideIcon,
} from "lucide-react"

import { useAuthStore, usePermissoesStore } from "@workspace/mock-data/stores"
import type { ModuloSistema } from "@workspace/mock-data/types"
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarSeparator,
} from "@workspace/ui/components/sidebar"

import { NavUser } from "./nav-user"
import { RoleSwitcher } from "./role-switcher"

interface NavItem {
  modulo: ModuloSistema
  titulo: string
  url: string
  icon: LucideIcon
}

const NAV_ITEMS: NavItem[] = [
  { modulo: "dashboard", titulo: "Dashboard", url: "/", icon: LayoutDashboardIcon },
  { modulo: "estudantes", titulo: "Estudantes", url: "/estudantes", icon: GraduationCapIcon },
  { modulo: "cursos", titulo: "Cursos", url: "/cursos", icon: BookOpenIcon },
  { modulo: "turmas", titulo: "Turmas", url: "/turmas", icon: UsersRoundIcon },
  { modulo: "formadores", titulo: "Formadores", url: "/formadores", icon: ContactIcon },
  { modulo: "taxas", titulo: "Taxas", url: "/taxas", icon: ReceiptIcon },
  { modulo: "pagamentos", titulo: "Pagamentos", url: "/pagamentos", icon: WalletIcon },
  { modulo: "usuarios", titulo: "Usuários", url: "/usuarios", icon: ShieldCheckIcon },
  { modulo: "pedidos", titulo: "Pedidos", url: "/pedidos", icon: InboxIcon },
]

export function AppSidebar() {
  const pathname = usePathname()
  const roleAtivaId = useAuthStore((state) => state.roleAtivaId)
  const permissoes = usePermissoesStore((state) => state.permissoes)

  const items = roleAtivaId
    ? NAV_ITEMS.filter((item) => permissoes[roleAtivaId][item.modulo].ver)
    : []

  return (
    <Sidebar collapsible="icon">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href="/">
                <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                  <GraduationCapIcon className="size-4" />
                </div>
                <div className="grid flex-1 text-left text-sm leading-tight">
                  <span className="truncate font-semibold">Ya Académico</span>
                  <span className="truncate text-xs text-sidebar-foreground/70">
                    Gestão Académica
                  </span>
                </div>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupContent>
            <SidebarMenu>
              {items.map((item) => (
                <SidebarMenuItem key={item.url}>
                  <SidebarMenuButton
                    asChild
                    isActive={pathname === item.url}
                    tooltip={item.titulo}
                  >
                    <Link href={item.url}>
                      <item.icon />
                      <span>{item.titulo}</span>
                    </Link>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
      <SidebarSeparator />
      <SidebarFooter>
        <RoleSwitcher />
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  )
}
