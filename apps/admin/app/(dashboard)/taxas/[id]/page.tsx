"use client"

import { use } from "react"
import { notFound, useRouter } from "next/navigation"

import { useTaxasStore } from "@workspace/mock-data/stores"
import { Card, CardContent } from "@workspace/ui/components/card"

import { DeleteDialog } from "@/components/delete-dialog"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { TaxaForm } from "../taxa-form"

export default function TaxaDetalhePage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const router = useRouter()
  const taxa = useTaxasStore((state) =>
    state.taxas.find((taxa) => taxa.id === id)
  )
  const removerTaxa = useTaxasStore((state) => state.removerTaxa)
  const permissoes = usePermissoesModulo("taxas")

  if (!taxa) {
    notFound()
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">{taxa.nome}</h1>
          <p className="text-sm text-muted-foreground">
            Editar dados da taxa.
          </p>
        </div>
        {permissoes.eliminar && (
          <DeleteDialog
            titulo="Eliminar taxa"
            descricao={`Tem a certeza que deseja eliminar ${taxa.nome}? Esta ação não pode ser revertida.`}
            onConfirm={() => {
              removerTaxa(taxa.id)
              router.push("/taxas")
            }}
          />
        )}
      </div>
      <Card>
        <CardContent>
          <TaxaForm taxa={taxa} />
        </CardContent>
      </Card>
    </div>
  )
}
