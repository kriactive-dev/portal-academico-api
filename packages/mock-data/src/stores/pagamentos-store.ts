import { create } from "zustand"

import type { MetodoPagamento, Pagamento } from "../types/pagamento"
import { pagamentosSeed } from "../data/pagamentos"

interface PagamentosState {
  pagamentos: Pagamento[]
  adicionarPagamento: (pagamento: Pagamento) => void
  atualizarPagamento: (id: string, dados: Partial<Pagamento>) => void
  removerPagamento: (id: string) => void
  obterPagamentoPorId: (id: string) => Pagamento | undefined
  registarPagamento: (
    id: string,
    dados: { metodo: MetodoPagamento; dataPagamento: string }
  ) => void
}

export const usePagamentosStore = create<PagamentosState>((set, get) => ({
  pagamentos: pagamentosSeed,
  adicionarPagamento: (pagamento) =>
    set((state) => ({ pagamentos: [...state.pagamentos, pagamento] })),
  atualizarPagamento: (id, dados) =>
    set((state) => ({
      pagamentos: state.pagamentos.map((pagamento) =>
        pagamento.id === id ? { ...pagamento, ...dados } : pagamento
      ),
    })),
  removerPagamento: (id) =>
    set((state) => ({
      pagamentos: state.pagamentos.filter((pagamento) => pagamento.id !== id),
    })),
  obterPagamentoPorId: (id) => get().pagamentos.find((pagamento) => pagamento.id === id),
  registarPagamento: (id, dados) =>
    set((state) => ({
      pagamentos: state.pagamentos.map((pagamento) =>
        pagamento.id === id
          ? { ...pagamento, ...dados, estado: "pago" }
          : pagamento
      ),
    })),
}))
