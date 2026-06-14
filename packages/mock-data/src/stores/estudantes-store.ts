import { create } from "zustand"

import type { Estudante } from "../types/estudante"
import { estudantesSeed } from "../data/estudantes"

interface EstudantesState {
  estudantes: Estudante[]
  adicionarEstudante: (estudante: Estudante) => void
  atualizarEstudante: (id: string, dados: Partial<Estudante>) => void
  removerEstudante: (id: string) => void
  obterEstudantePorId: (id: string) => Estudante | undefined
}

export const useEstudantesStore = create<EstudantesState>((set, get) => ({
  estudantes: estudantesSeed,
  adicionarEstudante: (estudante) =>
    set((state) => ({ estudantes: [...state.estudantes, estudante] })),
  atualizarEstudante: (id, dados) =>
    set((state) => ({
      estudantes: state.estudantes.map((estudante) =>
        estudante.id === id ? { ...estudante, ...dados } : estudante
      ),
    })),
  removerEstudante: (id) =>
    set((state) => ({
      estudantes: state.estudantes.filter((estudante) => estudante.id !== id),
    })),
  obterEstudantePorId: (id) => get().estudantes.find((estudante) => estudante.id === id),
}))
