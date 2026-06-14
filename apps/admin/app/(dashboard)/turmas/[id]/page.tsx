"use client"

import { use } from "react"
import { notFound, useRouter } from "next/navigation"

import { useTurmasStore } from "@workspace/mock-data/stores"
import { Card, CardContent } from "@workspace/ui/components/card"

import { DeleteDialog } from "@/components/delete-dialog"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { TurmaForm } from "../turma-form"

export default function TurmaDetalhePage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const router = useRouter()
  const turma = useTurmasStore((state) =>
    state.turmas.find((turma) => turma.id === id)
  )
  const removerTurma = useTurmasStore((state) => state.removerTurma)
  const permissoes = usePermissoesModulo("turmas")

  if (!turma) {
    notFound()
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">{turma.nome}</h1>
          <p className="text-sm text-muted-foreground">
            Editar dados da turma.
          </p>
        </div>
        {permissoes.eliminar && (
          <DeleteDialog
            titulo="Eliminar turma"
            descricao={`Tem a certeza que deseja eliminar ${turma.nome}? Esta ação não pode ser revertida.`}
            onConfirm={() => {
              removerTurma(turma.id)
              router.push("/turmas")
            }}
          />
        )}
      </div>
      <Card>
        <CardContent>
          <TurmaForm turma={turma} />
        </CardContent>
      </Card>
    </div>
  )
}
