"use client"

import Link from "next/link"
import { PlusIcon } from "lucide-react"

import { useTurmasStore } from "@workspace/mock-data/stores"
import { Button } from "@workspace/ui/components/button"

import { DataTable } from "@/components/data-table"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { turmasColumns } from "./columns"

export default function TurmasPage() {
  const turmas = useTurmasStore((state) => state.turmas)
  const permissoes = usePermissoesModulo("turmas")

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Turmas</h1>
          <p className="text-sm text-muted-foreground">
            Turmas em curso e planeadas no instituto.
          </p>
        </div>
        {permissoes.criar && (
          <Button asChild>
            <Link href="/turmas/novo">
              <PlusIcon />
              Nova Turma
            </Link>
          </Button>
        )}
      </div>
      <DataTable
        columns={turmasColumns}
        data={turmas}
        searchKey="nome"
        searchPlaceholder="Pesquisar por nome..."
      />
    </div>
  )
}
