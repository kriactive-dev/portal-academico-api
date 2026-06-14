import { create } from "zustand"

import type { Pedido } from "../types/pedido"
import { pedidosSeed } from "../data/pedidos"

interface PedidosState {
  pedidos: Pedido[]
  adicionarPedido: (pedido: Pedido) => void
  atualizarPedido: (id: string, dados: Partial<Pedido>) => void
  removerPedido: (id: string) => void
  obterPedidoPorId: (id: string) => Pedido | undefined
  aprovarPedido: (id: string, dataResposta: string) => void
  negarPedido: (id: string, motivoNegacao: string, dataResposta: string) => void
}

export const usePedidosStore = create<PedidosState>((set, get) => ({
  pedidos: pedidosSeed,
  adicionarPedido: (pedido) => set((state) => ({ pedidos: [...state.pedidos, pedido] })),
  atualizarPedido: (id, dados) =>
    set((state) => ({
      pedidos: state.pedidos.map((pedido) =>
        pedido.id === id ? { ...pedido, ...dados } as Pedido : pedido
      ),
    })),
  removerPedido: (id) =>
    set((state) => ({ pedidos: state.pedidos.filter((pedido) => pedido.id !== id) })),
  obterPedidoPorId: (id) => get().pedidos.find((pedido) => pedido.id === id),
  aprovarPedido: (id, dataResposta) =>
    set((state) => ({
      pedidos: state.pedidos.map((pedido) =>
        pedido.id === id
          ? { ...pedido, estado: "aprovado", dataResposta, motivoNegacao: undefined }
          : pedido
      ),
    })),
  negarPedido: (id, motivoNegacao, dataResposta) =>
    set((state) => ({
      pedidos: state.pedidos.map((pedido) =>
        pedido.id === id
          ? { ...pedido, estado: "negado", motivoNegacao, dataResposta }
          : pedido
      ),
    })),
}))
