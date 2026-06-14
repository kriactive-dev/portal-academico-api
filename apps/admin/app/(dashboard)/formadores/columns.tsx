"use client"

import Link from "next/link"
import { type ColumnDef } from "@tanstack/react-table"
import { ArrowUpDownIcon } from "lucide-react"

import type { Formador } from "@workspace/mock-data/types"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"

export const formadoresColumns: ColumnDef<Formador>[] = [
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
        href={`/formadores/${row.original.id}`}
        className="font-medium hover:underline"
      >
        {row.original.nome}
      </Link>
    ),
  },
  {
    accessorKey: "especialidade",
    header: "Especialidade",
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
    accessorKey: "estado",
    header: "Estado",
    cell: ({ row }) => {
      const estado = row.original.estado
      return (
        <Badge variant={estado === "ativo" ? "default" : "secondary"}>
          {estado === "ativo" ? "Ativo" : "Inativo"}
        </Badge>
      )
    },
  },
]
