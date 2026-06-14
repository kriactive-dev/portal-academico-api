"use client"

import Link from "next/link"
import { PlusIcon } from "lucide-react"

import { useEstudantesStore } from "@workspace/mock-data/stores"
import { Button } from "@workspace/ui/components/button"

import { DataTable } from "@/components/data-table"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { estudantesColumns } from "./columns"

export default function EstudantesPage() {
  const estudantes = useEstudantesStore((state) => state.estudantes)
  const permissoes = usePermissoesModulo("estudantes")

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Estudantes</h1>
          <p className="text-sm text-muted-foreground">
            Estudantes matriculados no instituto.
          </p>
        </div>
        {permissoes.criar && (
          <Button asChild>
            <Link href="/estudantes/novo">
              <PlusIcon />
              Novo Estudante
            </Link>
          </Button>
        )}
      </div>
      <DataTable
        columns={estudantesColumns}
        data={estudantes}
        searchKey="nome"
        searchPlaceholder="Pesquisar por nome..."
      />
    </div>
  )
}
