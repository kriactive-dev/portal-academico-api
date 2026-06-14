"use client"

import Link from "next/link"
import { PlusIcon } from "lucide-react"

import { useFormadoresStore } from "@workspace/mock-data/stores"
import { Button } from "@workspace/ui/components/button"

import { DataTable } from "@/components/data-table"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { formadoresColumns } from "./columns"

export default function FormadoresPage() {
  const formadores = useFormadoresStore((state) => state.formadores)
  const permissoes = usePermissoesModulo("formadores")

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Formadores</h1>
          <p className="text-sm text-muted-foreground">
            Gestão dos formadores do instituto.
          </p>
        </div>
        {permissoes.criar && (
          <Button asChild>
            <Link href="/formadores/novo">
              <PlusIcon />
              Novo Formador
            </Link>
          </Button>
        )}
      </div>
      <DataTable
        columns={formadoresColumns}
        data={formadores}
        searchKey="nome"
        searchPlaceholder="Pesquisar por nome..."
      />
    </div>
  )
}
