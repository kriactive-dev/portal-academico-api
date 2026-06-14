"use client"

import { use } from "react"
import { notFound, useRouter } from "next/navigation"

import { useFormadoresStore } from "@workspace/mock-data/stores"
import { Card, CardContent } from "@workspace/ui/components/card"

import { DeleteDialog } from "@/components/delete-dialog"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { FormadorForm } from "../formador-form"

export default function FormadorDetalhePage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const router = useRouter()
  const formador = useFormadoresStore((state) =>
    state.formadores.find((formador) => formador.id === id)
  )
  const removerFormador = useFormadoresStore((state) => state.removerFormador)
  const permissoes = usePermissoesModulo("formadores")

  if (!formador) {
    notFound()
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">{formador.nome}</h1>
          <p className="text-sm text-muted-foreground">
            Editar dados do formador.
          </p>
        </div>
        {permissoes.eliminar && (
          <DeleteDialog
            titulo="Eliminar formador"
            descricao={`Tem a certeza que deseja eliminar ${formador.nome}? Esta ação não pode ser revertida.`}
            onConfirm={() => {
              removerFormador(formador.id)
              router.push("/formadores")
            }}
          />
        )}
      </div>
      <Card>
        <CardContent>
          <FormadorForm formador={formador} />
        </CardContent>
      </Card>
    </div>
  )
}
