"use client"

import { type ColumnDef } from "@tanstack/react-table"
import { ArrowUpDownIcon } from "lucide-react"

import type { EstadoPagamento, Pagamento } from "@workspace/mock-data/types"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { formatarData, formatarMoeda } from "@workspace/ui/lib/utils"

import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { METODO_LABELS } from "./registrar-pagamento-dialog"
import { RegistrarPagamentoDialog } from "./registrar-pagamento-dialog"

export interface PagamentoRow extends Pagamento {
  estudanteNome: string
  cursoNome: string
}

export const ESTADO_LABELS: Record<EstadoPagamento, string> = {
  pago: "Pago",
  pendente: "Pendente",
  atrasado: "Atrasado",
}

export const ESTADO_VARIANTS: Record<
  EstadoPagamento,
  "default" | "secondary" | "destructive"
> = {
  pago: "default",
  pendente: "secondary",
  atrasado: "destructive",
}

function AcaoCell({ row }: { row: PagamentoRow }) {
  const permissoes = usePermissoesModulo("pagamentos")

  if (row.estado === "pago") {
    return (
      <span className="text-sm text-muted-foreground">
        {row.dataPagamento && formatarData(row.dataPagamento)}
        {row.metodo && ` · ${METODO_LABELS[row.metodo]}`}
      </span>
    )
  }

  if (!permissoes.editar) {
    return null
  }

  return <RegistrarPagamentoDialog pagamento={row} />
}

export const pagamentosColumns: ColumnDef<PagamentoRow>[] = [
  {
    accessorKey: "estudanteNome",
    header: ({ column }) => (
      <Button
        variant="ghost"
        size="sm"
        onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
      >
        Estudante
        <ArrowUpDownIcon />
      </Button>
    ),
  },
  {
    accessorKey: "cursoNome",
    header: "Curso",
  },
  {
    accessorKey: "mesReferencia",
    header: "Mês",
  },
  {
    accessorKey: "valor",
    header: "Valor",
    cell: ({ row }) => formatarMoeda(row.original.valor),
  },
  {
    accessorKey: "dataVencimento",
    header: "Vencimento",
    cell: ({ row }) => formatarData(row.original.dataVencimento),
  },
  {
    accessorKey: "estado",
    header: "Estado",
    cell: ({ row }) => (
      <Badge variant={ESTADO_VARIANTS[row.original.estado]}>
        {ESTADO_LABELS[row.original.estado]}
      </Badge>
    ),
  },
  {
    id: "acoes",
    header: "Pagamento",
    cell: ({ row }) => <AcaoCell row={row.original} />,
  },
]
