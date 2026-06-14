"use client"

import Link from "next/link"
import { type ColumnDef } from "@tanstack/react-table"
import { ArrowUpDownIcon } from "lucide-react"

import { useCursosStore } from "@workspace/mock-data/stores"
import type { Taxa, TipoTaxa } from "@workspace/mock-data/types"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { formatarMoeda } from "@workspace/ui/lib/utils"

export const TIPO_LABELS: Record<TipoTaxa, string> = {
  matricula: "Matrícula",
  inscricao: "Inscrição",
  exame: "Exame",
  certificado: "Certificado",
  outro: "Outro",
}

function CursoCell({ cursoId }: { cursoId?: string }) {
  const curso = useCursosStore((state) =>
    cursoId ? state.cursos.find((curso) => curso.id === cursoId) : undefined
  )

  if (!cursoId) {
    return <span className="text-muted-foreground">Todos os cursos</span>
  }

  return <span>{curso?.nome ?? "-"}</span>
}

export const taxasColumns: ColumnDef<Taxa>[] = [
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
        href={`/taxas/${row.original.id}`}
        className="font-medium hover:underline"
      >
        {row.original.nome}
      </Link>
    ),
  },
  {
    accessorKey: "tipo",
    header: "Tipo",
    cell: ({ row }) => TIPO_LABELS[row.original.tipo],
  },
  {
    accessorKey: "valor",
    header: "Valor",
    cell: ({ row }) => formatarMoeda(row.original.valor),
  },
  {
    accessorKey: "cursoId",
    header: "Curso",
    cell: ({ row }) => <CursoCell cursoId={row.original.cursoId} />,
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
