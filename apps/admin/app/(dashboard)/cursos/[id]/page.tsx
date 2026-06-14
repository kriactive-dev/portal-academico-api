"use client"

import { use } from "react"
import { notFound, useRouter } from "next/navigation"

import { useCursosStore } from "@workspace/mock-data/stores"
import { Card, CardContent } from "@workspace/ui/components/card"

import { DeleteDialog } from "@/components/delete-dialog"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { CursoForm } from "../curso-form"

export default function CursoDetalhePage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const router = useRouter()
  const curso = useCursosStore((state) =>
    state.cursos.find((curso) => curso.id === id)
  )
  const removerCurso = useCursosStore((state) => state.removerCurso)
  const permissoes = usePermissoesModulo("cursos")

  if (!curso) {
    notFound()
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">{curso.nome}</h1>
          <p className="text-sm text-muted-foreground">
            Editar dados do curso.
          </p>
        </div>
        {permissoes.eliminar && (
          <DeleteDialog
            titulo="Eliminar curso"
            descricao={`Tem a certeza que deseja eliminar ${curso.nome}? Esta ação não pode ser revertida.`}
            onConfirm={() => {
              removerCurso(curso.id)
              router.push("/cursos")
            }}
          />
        )}
      </div>
      <Card>
        <CardContent>
          <CursoForm curso={curso} />
        </CardContent>
      </Card>
    </div>
  )
}
