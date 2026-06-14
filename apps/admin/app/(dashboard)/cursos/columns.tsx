"use client"

import Link from "next/link"
import { type ColumnDef } from "@tanstack/react-table"
import { ArrowUpDownIcon } from "lucide-react"

import type { Curso } from "@workspace/mock-data/types"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { formatarMoeda } from "@workspace/ui/lib/utils"

export const cursosColumns: ColumnDef<Curso>[] = [
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
        href={`/cursos/${row.original.id}`}
        className="font-medium hover:underline"
      >
        {row.original.nome}
      </Link>
    ),
  },
  {
    accessorKey: "duracaoMeses",
    header: "Duração",
    cell: ({ row }) => `${row.original.duracaoMeses} meses`,
  },
  {
    accessorKey: "mensalidade",
    header: "Mensalidade",
    cell: ({ row }) => formatarMoeda(row.original.mensalidade),
  },
  {
    accessorKey: "ativo",
    header: "Estado",
    cell: ({ row }) => (
      <Badge variant={row.original.ativo ? "default" : "secondary"}>
        {row.original.ativo ? "Ativo" : "Inativo"}
      </Badge>
    ),
  },
]
