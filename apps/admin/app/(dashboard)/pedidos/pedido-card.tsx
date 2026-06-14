"use client"

import Link from "next/link"

import { useCursosStore, useEstudantesStore } from "@workspace/mock-data/stores"
import type { Pedido } from "@workspace/mock-data/types"
import { Badge } from "@workspace/ui/components/badge"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"
import { formatarData } from "@workspace/ui/lib/utils"

import { ESTADO_LABELS, ESTADO_VARIANTS, TIPO_LABELS } from "./constants"

interface PedidoCardProps {
  pedido: Pedido
}

export function PedidoCard({ pedido }: PedidoCardProps) {
  const estudantes = useEstudantesStore((state) => state.estudantes)
  const cursos = useCursosStore((state) => state.cursos)

  const estudanteNome =
    estudantes.find((estudante) => estudante.id === pedido.estudanteId)
      ?.nome ?? "-"

  const resumo =
    pedido.tipo === "certificado"
      ? `Certificado de ${cursos.find((curso) => curso.id === pedido.detalhes.cursoId)?.nome ?? "-"} · ${pedido.detalhes.finalidade}`
      : `Estágio em ${pedido.detalhes.empresa} · ${pedido.detalhes.cargoEstagio}`

  return (
    <Link href={`/pedidos/${pedido.id}`}>
      <Card className="transition-colors hover:bg-muted/50">
        <CardHeader>
          <div className="flex items-center justify-between gap-2">
            <CardTitle>{estudanteNome}</CardTitle>
            <div className="flex items-center gap-2">
              {pedido.tipo === "certificado" && pedido.detalhes.urgente && (
                <Badge variant="destructive">Urgente</Badge>
              )}
              <Badge variant={ESTADO_VARIANTS[pedido.estado]}>
                {ESTADO_LABELS[pedido.estado]}
              </Badge>
            </div>
          </div>
          <CardDescription>{resumo}</CardDescription>
        </CardHeader>
        <CardContent className="flex items-center justify-between text-sm text-muted-foreground">
          <span>{TIPO_LABELS[pedido.tipo]}</span>
          <span>Submetido em {formatarData(pedido.dataSubmissao)}</span>
        </CardContent>
      </Card>
    </Link>
  )
}
