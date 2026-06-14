"use client"

import { useState } from "react"

import {
  useCursosStore,
  useEstudantesStore,
  usePagamentosStore,
} from "@workspace/mock-data/stores"
import type { EstadoPagamento } from "@workspace/mock-data/types"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@workspace/ui/components/select"

import { DataTable } from "@/components/data-table"

import { ESTADO_LABELS, pagamentosColumns, type PagamentoRow } from "./columns"

export default function PagamentosPage() {
  const pagamentos = usePagamentosStore((state) => state.pagamentos)
  const estudantes = useEstudantesStore((state) => state.estudantes)
  const cursos = useCursosStore((state) => state.cursos)
  const [filtroEstado, setFiltroEstado] = useState<"todos" | EstadoPagamento>(
    "todos"
  )

  const rows: PagamentoRow[] = pagamentos
    .map((pagamento) => ({
      ...pagamento,
      estudanteNome:
        estudantes.find((estudante) => estudante.id === pagamento.estudanteId)
          ?.nome ?? "-",
      cursoNome:
        cursos.find((curso) => curso.id === pagamento.cursoId)?.nome ?? "-",
    }))
    .filter((row) => filtroEstado === "todos" || row.estado === filtroEstado)

  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Pagamentos</h1>
        <p className="text-sm text-muted-foreground">
          Mensalidades dos estudantes por mês de referência.
        </p>
      </div>
      <Select
        value={filtroEstado}
        onValueChange={(value) =>
          setFiltroEstado(value as "todos" | EstadoPagamento)
        }
      >
        <SelectTrigger className="w-48">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="todos">Todos os estados</SelectItem>
          {Object.entries(ESTADO_LABELS).map(([value, label]) => (
            <SelectItem key={value} value={value}>
              {label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
      <DataTable
        columns={pagamentosColumns}
        data={rows}
        searchKey="estudanteNome"
        searchPlaceholder="Pesquisar por estudante..."
      />
    </div>
  )
}
