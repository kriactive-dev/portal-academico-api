"use client"

import { use } from "react"
import { notFound, useRouter } from "next/navigation"

import { useEstudantesStore } from "@workspace/mock-data/stores"
import { Card, CardContent } from "@workspace/ui/components/card"

import { DeleteDialog } from "@/components/delete-dialog"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { EstudanteForm } from "../estudante-form"

export default function EstudanteDetalhePage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const router = useRouter()
  const estudante = useEstudantesStore((state) =>
    state.estudantes.find((estudante) => estudante.id === id)
  )
  const removerEstudante = useEstudantesStore((state) => state.removerEstudante)
  const permissoes = usePermissoesModulo("estudantes")

  if (!estudante) {
    notFound()
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">{estudante.nome}</h1>
          <p className="text-sm text-muted-foreground">
            Editar dados do estudante.
          </p>
        </div>
        {permissoes.eliminar && (
          <DeleteDialog
            titulo="Eliminar estudante"
            descricao={`Tem a certeza que deseja eliminar ${estudante.nome}? Esta ação não pode ser revertida.`}
            onConfirm={() => {
              removerEstudante(estudante.id)
              router.push("/estudantes")
            }}
          />
        )}
      </div>
      <Card>
        <CardContent>
          <EstudanteForm estudante={estudante} />
        </CardContent>
      </Card>
    </div>
  )
}
