"use client"

import { rolesSeed } from "@workspace/mock-data/data"
import { useAuthStore } from "@workspace/mock-data/stores"
import type { RoleId } from "@workspace/mock-data/types"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@workspace/ui/components/select"

export function RoleSwitcher() {
  const roleAtivaId = useAuthStore((state) => state.roleAtivaId)
  const definirRoleAtiva = useAuthStore((state) => state.definirRoleAtiva)

  if (!roleAtivaId) {
    return null
  }

  return (
    <div className="flex flex-col gap-1 px-2 py-1.5 group-data-[collapsible=icon]:hidden">
      <span className="text-xs text-sidebar-foreground/70">Perfil de acesso (demo)</span>
      <Select
        value={roleAtivaId}
        onValueChange={(value) => definirRoleAtiva(value as RoleId)}
      >
        <SelectTrigger className="w-full">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          {rolesSeed.map((role) => (
            <SelectItem key={role.id} value={role.id}>
              {role.nome}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  )
}
