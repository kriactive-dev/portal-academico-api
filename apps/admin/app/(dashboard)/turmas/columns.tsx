"use client"

import Link from "next/link"
import { type ColumnDef } from "@tanstack/react-table"
import { ArrowUpDownIcon } from "lucide-react"

import { useCursosStore } from "@workspace/mock-data/stores"
import type { EstadoTurma, Turma, TurnoTurma } from "@workspace/mock-data/types"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { formatarData } from "@workspace/ui/lib/utils"

const TURNO_LABELS: Record<TurnoTurma, string> = {
  manha: "Manhã",
  tarde: "Tarde",
  noite: "Noite",
}

const ESTADO_LABELS: Record<EstadoTurma, string> = {
  planeada: "Planeada",
  em_curso: "Em curso",
  concluida: "Concluída",
}

const ESTADO_VARIANTS: Record<EstadoTurma, "default" | "secondary" | "outline"> = {
  planeada: "outline",
  em_curso: "default",
  concluida: "secondary",
}

function CursoCell({ cursoId }: { cursoId: string }) {
  const curso = useCursosStore((state) =>
    state.cursos.find((curso) => curso.id === cursoId)
  )
  return <span>{curso?.nome ?? "-"}</span>
}

export const turmasColumns: ColumnDef<Turma>[] = [
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
        href={`/turmas/${row.original.id}`}
        className="font-medium hover:underline"
      >
        {row.original.nome}
      </Link>
    ),
  },
  {
    accessorKey: "cursoId",
    header: "Curso",
    cell: ({ row }) => <CursoCell cursoId={row.original.cursoId} />,
  },
  {
    accessorKey: "turno",
    header: "Turno",
    cell: ({ row }) => TURNO_LABELS[row.original.turno],
  },
  {
    accessorKey: "dataInicio",
    header: "Início",
    cell: ({ row }) => formatarData(row.original.dataInicio),
  },
  {
    id: "estudantes",
    header: "Estudantes",
    cell: ({ row }) => row.original.estudanteIds.length,
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
