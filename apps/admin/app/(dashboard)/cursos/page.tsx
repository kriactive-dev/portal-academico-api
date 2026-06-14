"use client"

import Link from "next/link"
import { PlusIcon } from "lucide-react"

import { useCursosStore } from "@workspace/mock-data/stores"
import { Button } from "@workspace/ui/components/button"

import { DataTable } from "@/components/data-table"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { cursosColumns } from "./columns"

export default function CursosPage() {
  const cursos = useCursosStore((state) => state.cursos)
  const permissoes = usePermissoesModulo("cursos")

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Cursos</h1>
          <p className="text-sm text-muted-foreground">
            Catálogo de cursos oferecidos pelo instituto.
          </p>
        </div>
        {permissoes.criar && (
          <Button asChild>
            <Link href="/cursos/novo">
              <PlusIcon />
              Novo Curso
            </Link>
          </Button>
        )}
      </div>
      <DataTable
        columns={cursosColumns}
        data={cursos}
        searchKey="nome"
        searchPlaceholder="Pesquisar por nome..."
      />
    </div>
  )
}
