"use client"

import { useAuthStore, usePermissoesStore } from "@workspace/mock-data/stores"
import type { ModuloSistema, PermissoesModulo } from "@workspace/mock-data/types"

const SEM_ACESSO: PermissoesModulo = {
  ver: false,
  criar: false,
  editar: false,
  eliminar: false,
  aprovar: false,
}

export function usePermissoesModulo(modulo: ModuloSistema): PermissoesModulo {
  const roleAtivaId = useAuthStore((state) => state.roleAtivaId)
  const permissoes = usePermissoesStore((state) => state.permissoes)

  if (!roleAtivaId) {
    return SEM_ACESSO
  }

  return permissoes[roleAtivaId][modulo]
}
