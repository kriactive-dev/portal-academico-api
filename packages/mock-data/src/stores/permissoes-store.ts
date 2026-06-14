import { create } from "zustand"

import type { AcaoPermissao, ModuloSistema, PermissoesPorRole, RoleId } from "../types/usuario"
import { permissoesPorRoleSeed } from "../data/permissoes"

interface PermissoesState {
  permissoes: PermissoesPorRole
  atualizarPermissao: (
    roleId: RoleId,
    modulo: ModuloSistema,
    acao: AcaoPermissao,
    valor: boolean
  ) => void
}

export const usePermissoesStore = create<PermissoesState>((set) => ({
  permissoes: permissoesPorRoleSeed,
  atualizarPermissao: (roleId, modulo, acao, valor) =>
    set((state) => ({
      permissoes: {
        ...state.permissoes,
        [roleId]: {
          ...state.permissoes[roleId],
          [modulo]: {
            ...state.permissoes[roleId][modulo],
            [acao]: valor,
          },
        },
      },
    })),
}))
