import { create } from "zustand"

import type { RoleId, Usuario } from "../types/usuario"
import type { Estudante } from "../types/estudante"

interface AuthState {
  usuarioAtualId: string | null
  estudanteAtualId: string | null
  roleAtivaId: RoleId | null
  loginComoUsuario: (usuario: Usuario) => void
  loginComoEstudante: (estudante: Estudante) => void
  definirRoleAtiva: (roleId: RoleId) => void
  logout: () => void
}

export const useAuthStore = create<AuthState>((set) => ({
  usuarioAtualId: null,
  estudanteAtualId: null,
  roleAtivaId: null,
  loginComoUsuario: (usuario) =>
    set({ usuarioAtualId: usuario.id, estudanteAtualId: null, roleAtivaId: usuario.roleId }),
  loginComoEstudante: (estudante) =>
    set({ estudanteAtualId: estudante.id, usuarioAtualId: null, roleAtivaId: null }),
  definirRoleAtiva: (roleId) => set({ roleAtivaId: roleId }),
  logout: () => set({ usuarioAtualId: null, estudanteAtualId: null, roleAtivaId: null }),
}))
