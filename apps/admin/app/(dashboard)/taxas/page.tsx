"use client"

import Link from "next/link"
import { PlusIcon } from "lucide-react"

import { useTaxasStore } from "@workspace/mock-data/stores"
import { Button } from "@workspace/ui/components/button"

import { DataTable } from "@/components/data-table"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { taxasColumns } from "./columns"

export default function TaxasPage() {
  const taxas = useTaxasStore((state) => state.taxas)
  const permissoes = usePermissoesModulo("taxas")

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Taxas</h1>
          <p className="text-sm text-muted-foreground">
            Taxas e emolumentos cobrados pelo instituto.
          </p>
        </div>
        {permissoes.criar && (
          <Button asChild>
            <Link href="/taxas/novo">
              <PlusIcon />
              Nova Taxa
            </Link>
          </Button>
        )}
      </div>
      <DataTable
        columns={taxasColumns}
        data={taxas}
        searchKey="nome"
        searchPlaceholder="Pesquisar por nome..."
      />
    </div>
  )
}
