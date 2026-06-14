import { create } from "zustand"

import type { Usuario } from "../types/usuario"
import { usuariosSeed } from "../data/usuarios"

interface UsuariosState {
  usuarios: Usuario[]
  adicionarUsuario: (usuario: Usuario) => void
  atualizarUsuario: (id: string, dados: Partial<Usuario>) => void
  removerUsuario: (id: string) => void
  obterUsuarioPorId: (id: string) => Usuario | undefined
}

export const useUsuariosStore = create<UsuariosState>((set, get) => ({
  usuarios: usuariosSeed,
  adicionarUsuario: (usuario) =>
    set((state) => ({ usuarios: [...state.usuarios, usuario] })),
  atualizarUsuario: (id, dados) =>
    set((state) => ({
      usuarios: state.usuarios.map((usuario) =>
        usuario.id === id ? { ...usuario, ...dados } : usuario
      ),
    })),
  removerUsuario: (id) =>
    set((state) => ({ usuarios: state.usuarios.filter((usuario) => usuario.id !== id) })),
  obterUsuarioPorId: (id) => get().usuarios.find((usuario) => usuario.id === id),
}))
