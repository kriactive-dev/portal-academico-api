"use client"

import { useState } from "react"
import Link from "next/link"
import { PlusIcon } from "lucide-react"

import { useAuthStore, usePedidosStore } from "@workspace/mock-data/stores"
import type { EstadoPedido } from "@workspace/mock-data/types"
import { Button } from "@workspace/ui/components/button"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@workspace/ui/components/tabs"

import { ESTADO_LABELS } from "./constants"
import { PedidoCard } from "./pedido-card"

export default function PedidosPage() {
  const [filtro, setFiltro] = useState<"todos" | EstadoPedido>("todos")
  const estudanteAtualId = useAuthStore((state) => state.estudanteAtualId)
  const pedidos = usePedidosStore((state) => state.pedidos)

  const meusPedidos = pedidos
    .filter((pedido) => pedido.estudanteId === estudanteAtualId)
    .filter((pedido) => filtro === "todos" || pedido.estado === filtro)
    .sort((a, b) => b.dataSubmissao.localeCompare(a.dataSubmissao))

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Os meus pedidos</h1>
        <Button asChild variant="outline" size="icon">
          <Link href="/pedidos/novo">
            <PlusIcon />
          </Link>
        </Button>
      </div>
      <Tabs
        value={filtro}
        onValueChange={(value) => setFiltro(value as "todos" | EstadoPedido)}
      >
        <TabsList className="w-full">
          <TabsTrigger value="todos">Todos</TabsTrigger>
          {Object.entries(ESTADO_LABELS).map(([estado, label]) => (
            <TabsTrigger key={estado} value={estado}>
              {label}
            </TabsTrigger>
          ))}
        </TabsList>
        <TabsContent value={filtro} className="flex flex-col gap-3">
          {meusPedidos.length === 0 ? (
            <p className="py-8 text-center text-sm text-muted-foreground">
              Nenhum pedido encontrado.
            </p>
          ) : (
            meusPedidos.map((pedido) => (
              <PedidoCard key={pedido.id} pedido={pedido} />
            ))
          )}
        </TabsContent>
      </Tabs>
    </div>
  )
}
