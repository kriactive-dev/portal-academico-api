"use client"

import Link from "next/link"
import { type ColumnDef } from "@tanstack/react-table"
import { ArrowUpDownIcon } from "lucide-react"

import type { Estudante, EstadoEstudante } from "@workspace/mock-data/types"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { formatarData } from "@workspace/ui/lib/utils"

const ESTADO_LABELS: Record<EstadoEstudante, string> = {
  ativo: "Ativo",
  inativo: "Inativo",
  concluido: "Concluído",
}

const ESTADO_VARIANTS: Record<EstadoEstudante, "default" | "secondary" | "outline"> = {
  ativo: "default",
  inativo: "secondary",
  concluido: "outline",
}

export const estudantesColumns: ColumnDef<Estudante>[] = [
  {
    accessorKey: "numeroEstudante",
    header: "Nº Estudante",
  },
  {
    accessorKey: "nome",
    header: ({ column }) => (
      <Button
        variant="ghost"
        size="sm"
        onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
      >
        Nome
        <ArrowUpDownIcon />
      </Button>
    ),
    cell: ({ row }) => (
      <Link
        href={`/estudantes/${row.original.id}`}
        className="font-medium hover:underline"
      >
        {row.original.nome}
      </Link>
    ),
  },
  {
    accessorKey: "email",
    header: "Email",
  },
  {
    accessorKey: "contacto",
    header: "Contacto",
  },
  {
    accessorKey: "dataMatricula",
    header: "Matrícula",
    cell: ({ row }) => formatarData(row.original.dataMatricula),
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
]
