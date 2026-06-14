"use client"

import { use } from "react"
import { notFound, useRouter } from "next/navigation"

import { useUsuariosStore } from "@workspace/mock-data/stores"
import { Card, CardContent } from "@workspace/ui/components/card"

import { DeleteDialog } from "@/components/delete-dialog"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { UsuarioForm } from "../usuario-form"

export default function UsuarioDetalhePage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const router = useRouter()
  const usuario = useUsuariosStore((state) =>
    state.usuarios.find((usuario) => usuario.id === id)
  )
  const removerUsuario = useUsuariosStore((state) => state.removerUsuario)
  const permissoes = usePermissoesModulo("usuarios")

  if (!usuario) {
    notFound()
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">{usuario.nome}</h1>
          <p className="text-sm text-muted-foreground">
            Editar dados do usuário.
          </p>
        </div>
        {permissoes.eliminar && (
          <DeleteDialog
            titulo="Eliminar usuário"
            descricao={`Tem a certeza que deseja eliminar ${usuario.nome}? Esta ação não pode ser revertida.`}
            onConfirm={() => {
              removerUsuario(usuario.id)
              router.push("/usuarios")
            }}
          />
        )}
      </div>
      <Card>
        <CardContent>
          <UsuarioForm usuario={usuario} />
        </CardContent>
      </Card>
    </div>
  )
}
