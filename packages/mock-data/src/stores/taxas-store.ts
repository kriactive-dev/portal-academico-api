import { create } from "zustand"

import type { Taxa } from "../types/taxa"
import { taxasSeed } from "../data/taxas"

interface TaxasState {
  taxas: Taxa[]
  adicionarTaxa: (taxa: Taxa) => void
  atualizarTaxa: (id: string, dados: Partial<Taxa>) => void
  removerTaxa: (id: string) => void
  obterTaxaPorId: (id: string) => Taxa | undefined
}

export const useTaxasStore = create<TaxasState>((set, get) => ({
  taxas: taxasSeed,
  adicionarTaxa: (taxa) => set((state) => ({ taxas: [...state.taxas, taxa] })),
  atualizarTaxa: (id, dados) =>
    set((state) => ({
      taxas: state.taxas.map((taxa) => (taxa.id === id ? { ...taxa, ...dados } : taxa)),
    })),
  removerTaxa: (id) =>
    set((state) => ({ taxas: state.taxas.filter((taxa) => taxa.id !== id) })),
  obterTaxaPorId: (id) => get().taxas.find((taxa) => taxa.id === id),
}))
