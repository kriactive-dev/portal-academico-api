"use client"

import Link from "next/link"

import { useCursosStore } from "@workspace/mock-data/stores"
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
  const cursos = useCursosStore((state) => state.cursos)

  const resumo =
    pedido.tipo === "certificado"
      ? cursos.find((curso) => curso.id === pedido.detalhes.cursoId)?.nome ??
        "-"
      : pedido.detalhes.empresa

  return (
    <Link href={`/pedidos/${pedido.id}`}>
      <Card className="transition-colors hover:bg-muted/50">
        <CardHeader>
          <div className="flex items-center justify-between gap-2">
            <CardTitle className="text-base">
              {TIPO_LABELS[pedido.tipo]}
            </CardTitle>
            <Badge variant={ESTADO_VARIANTS[pedido.estado]}>
              {ESTADO_LABELS[pedido.estado]}
            </Badge>
          </div>
          <CardDescription>{resumo}</CardDescription>
        </CardHeader>
        <CardContent className="text-sm text-muted-foreground">
          Submetido em {formatarData(pedido.dataSubmissao)}
        </CardContent>
      </Card>
    </Link>
  )
}
