import { create } from "zustand"

import type { Curso } from "../types/curso"
import { cursosSeed } from "../data/cursos"

interface CursosState {
  cursos: Curso[]
  adicionarCurso: (curso: Curso) => void
  atualizarCurso: (id: string, dados: Partial<Curso>) => void
  removerCurso: (id: string) => void
  obterCursoPorId: (id: string) => Curso | undefined
}

export const useCursosStore = create<CursosState>((set, get) => ({
  cursos: cursosSeed,
  adicionarCurso: (curso) => set((state) => ({ cursos: [...state.cursos, curso] })),
  atualizarCurso: (id, dados) =>
    set((state) => ({
      cursos: state.cursos.map((curso) => (curso.id === id ? { ...curso, ...dados } : curso)),
    })),
  removerCurso: (id) =>
    set((state) => ({ cursos: state.cursos.filter((curso) => curso.id !== id) })),
  obterCursoPorId: (id) => get().cursos.find((curso) => curso.id === id),
}))
