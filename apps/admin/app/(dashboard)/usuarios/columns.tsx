"use client"

import Link from "next/link"
import { type ColumnDef } from "@tanstack/react-table"
import { ArrowUpDownIcon } from "lucide-react"

import { rolesSeed } from "@workspace/mock-data/data"
import type { Usuario } from "@workspace/mock-data/types"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"

function RoleCell({ roleId }: { roleId: Usuario["roleId"] }) {
  const role = rolesSeed.find((role) => role.id === roleId)
  return <span>{role?.nome ?? roleId}</span>
}

export const usuariosColumns: ColumnDef<Usuario>[] = [
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
        href={`/usuarios/${row.original.id}`}
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
    accessorKey: "roleId",
    header: "Perfil",
    cell: ({ row }) => <RoleCell roleId={row.original.roleId} />,
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
