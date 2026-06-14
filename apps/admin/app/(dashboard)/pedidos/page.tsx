"use client"

import { useState } from "react"

import { usePedidosStore } from "@workspace/mock-data/stores"
import type { EstadoPedido } from "@workspace/mock-data/types"
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from "@workspace/ui/components/tabs"

import { ESTADO_LABELS } from "./constants"
import { PedidoCard } from "./pedido-card"

export default function PedidosPage() {
  const pedidos = usePedidosStore((state) => state.pedidos)
  const [filtro, setFiltro] = useState<"todos" | EstadoPedido>("todos")

  const pedidosFiltrados = pedidos
    .filter((pedido) => filtro === "todos" || pedido.estado === filtro)
    .sort((a, b) => b.dataSubmissao.localeCompare(a.dataSubmissao))

  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Pedidos</h1>
        <p className="text-sm text-muted-foreground">
          Pedidos de certificado e aprovação de estágio submetidos pelos
          estudantes.
        </p>
      </div>
      <Tabs
        value={filtro}
        onValueChange={(value) => setFiltro(value as "todos" | EstadoPedido)}
      >
        <TabsList>
          <TabsTrigger value="todos">Todos</TabsTrigger>
          {Object.entries(ESTADO_LABELS).map(([value, label]) => (
            <TabsTrigger key={value} value={value}>
              {label}
            </TabsTrigger>
          ))}
        </TabsList>
        <TabsContent value={filtro} className="flex flex-col gap-3 pt-2">
          {pedidosFiltrados.length === 0 ? (
            <p className="text-sm text-muted-foreground">
              Nenhum pedido encontrado.
            </p>
          ) : (
            pedidosFiltrados.map((pedido) => (
              <PedidoCard key={pedido.id} pedido={pedido} />
            ))
          )}
        </TabsContent>
      </Tabs>
    </div>
  )
}
