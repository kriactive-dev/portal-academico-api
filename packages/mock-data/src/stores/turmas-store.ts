import { create } from "zustand"

import type { Turma } from "../types/turma"
import { turmasSeed } from "../data/turmas"

interface TurmasState {
  turmas: Turma[]
  adicionarTurma: (turma: Turma) => void
  atualizarTurma: (id: string, dados: Partial<Turma>) => void
  removerTurma: (id: string) => void
  obterTurmaPorId: (id: string) => Turma | undefined
}

export const useTurmasStore = create<TurmasState>((set, get) => ({
  turmas: turmasSeed,
  adicionarTurma: (turma) => set((state) => ({ turmas: [...state.turmas, turma] })),
  atualizarTurma: (id, dados) =>
    set((state) => ({
      turmas: state.turmas.map((turma) => (turma.id === id ? { ...turma, ...dados } : turma)),
    })),
  removerTurma: (id) =>
    set((state) => ({ turmas: state.turmas.filter((turma) => turma.id !== id) })),
  obterTurmaPorId: (id) => get().turmas.find((turma) => turma.id === id),
}))
