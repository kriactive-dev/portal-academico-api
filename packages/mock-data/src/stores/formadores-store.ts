import { create } from "zustand"

import type { Formador } from "../types/formador"
import { formadoresSeed } from "../data/formadores"

interface FormadoresState {
  formadores: Formador[]
  adicionarFormador: (formador: Formador) => void
  atualizarFormador: (id: string, dados: Partial<Formador>) => void
  removerFormador: (id: string) => void
  obterFormadorPorId: (id: string) => Formador | undefined
}

export const useFormadoresStore = create<FormadoresState>((set, get) => ({
  formadores: formadoresSeed,
  adicionarFormador: (formador) =>
    set((state) => ({ formadores: [...state.formadores, formador] })),
  atualizarFormador: (id, dados) =>
    set((state) => ({
      formadores: state.formadores.map((formador) =>
        formador.id === id ? { ...formador, ...dados } : formador
      ),
    })),
  removerFormador: (id) =>
    set((state) => ({
      formadores: state.formadores.filter((formador) => formador.id !== id),
    })),
  obterFormadorPorId: (id) => get().formadores.find((formador) => formador.id === id),
}))
