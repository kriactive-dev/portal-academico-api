"use client"

import { use } from "react"
import { notFound } from "next/navigation"

import {
  useAuthStore,
  useCursosStore,
  usePedidosStore,
} from "@workspace/mock-data/stores"
import { Badge } from "@workspace/ui/components/badge"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"
import { formatarData } from "@workspace/ui/lib/utils"

import { ESTADO_LABELS, ESTADO_VARIANTS, TIPO_LABELS } from "../constants"

export default function PedidoDetalhePage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const estudanteAtualId = useAuthStore((state) => state.estudanteAtualId)
  const pedido = usePedidosStore((state) =>
    state.pedidos.find((pedido) => pedido.id === id)
  )
  const cursos = useCursosStore((state) => state.cursos)

  if (!pedido || pedido.estudanteId !== estudanteAtualId) {
    notFound()
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">{TIPO_LABELS[pedido.tipo]}</h1>
        <Badge variant={ESTADO_VARIANTS[pedido.estado]}>
          {ESTADO_LABELS[pedido.estado]}
        </Badge>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Detalhes do pedido</CardTitle>
          <CardDescription>
            Submetido em {formatarData(pedido.dataSubmissao)}
            {pedido.dataResposta &&
              ` · Respondido em ${formatarData(pedido.dataResposta)}`}
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-4 text-sm">
          {pedido.tipo === "certificado" ? (
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <p className="text-muted-foreground">Curso</p>
                <p className="font-medium">
                  {cursos.find((curso) => curso.id === pedido.detalhes.cursoId)
                    ?.nome ?? "-"}
                </p>
              </div>
              <div>
                <p className="text-muted-foreground">Finalidade</p>
                <p className="font-medium">{pedido.detalhes.finalidade}</p>
              </div>
              {pedido.detalhes.urgente && (
                <Badge variant="destructive" className="w-fit">
                  Urgente
                </Badge>
              )}
            </div>
          ) : (
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <p className="text-muted-foreground">Empresa</p>
                <p className="font-medium">{pedido.detalhes.empresa}</p>
              </div>
              <div>
                <p className="text-muted-foreground">Cargo</p>
                <p className="font-medium">{pedido.detalhes.cargoEstagio}</p>
              </div>
              <div>
                <p className="text-muted-foreground">Início do estágio</p>
                <p className="font-medium">
                  {formatarData(pedido.detalhes.dataInicioEstagio)}
                </p>
              </div>
              <div>
                <p className="text-muted-foreground">Fim do estágio</p>
                <p className="font-medium">
                  {formatarData(pedido.detalhes.dataFimEstagio)}
                </p>
              </div>
            </div>
          )}
          {pedido.estado === "negado" && pedido.motivoNegacao && (
            <div>
              <p className="text-muted-foreground">Motivo da negação</p>
              <p className="font-medium">{pedido.motivoNegacao}</p>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
