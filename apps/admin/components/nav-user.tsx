"use client"

import { useRouter } from "next/navigation"
import { ChevronsUpDownIcon, LogOutIcon } from "lucide-react"

import { rolesSeed } from "@workspace/mock-data/data"
import { useAuthStore, useUsuariosStore } from "@workspace/mock-data/stores"
import { Avatar, AvatarFallback } from "@workspace/ui/components/avatar"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@workspace/ui/components/dropdown-menu"
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@workspace/ui/components/sidebar"
import { getIniciais } from "@workspace/ui/lib/utils"

export function NavUser() {
  const router = useRouter()
  const usuarioAtualId = useAuthStore((state) => state.usuarioAtualId)
  const logout = useAuthStore((state) => state.logout)
  const usuario = useUsuariosStore((state) =>
    state.usuarios.find((usuario) => usuario.id === usuarioAtualId)
  )

  if (!usuario) {
    return null
  }

  const role = rolesSeed.find((role) => role.id === usuario.roleId)

  function handleLogout() {
    logout()
    router.replace("/login")
  }

  return (
    <SidebarMenu>
      <SidebarMenuItem>
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <SidebarMenuButton size="lg">
              <Avatar size="sm">
                <AvatarFallback>{getIniciais(usuario.nome)}</AvatarFallback>
              </Avatar>
              <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{usuario.nome}</span>
                <span className="truncate text-xs text-sidebar-foreground/70">
                  {role?.nome}
                </span>
              </div>
              <ChevronsUpDownIcon className="ml-auto" />
            </SidebarMenuButton>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="w-56">
            <DropdownMenuLabel className="text-xs font-normal text-muted-foreground">
              {usuario.email}
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem onClick={handleLogout}>
              <LogOutIcon />
              Sair
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarMenuItem>
    </SidebarMenu>
  )
}
