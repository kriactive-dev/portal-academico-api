"use client"

import Link from "next/link"
import { PlusIcon, ShieldIcon } from "lucide-react"

import { useUsuariosStore } from "@workspace/mock-data/stores"
import { Button } from "@workspace/ui/components/button"

import { DataTable } from "@/components/data-table"
import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { usuariosColumns } from "./columns"

export default function UsuariosPage() {
  const usuarios = useUsuariosStore((state) => state.usuarios)
  const permissoes = usePermissoesModulo("usuarios")

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Usuários</h1>
          <p className="text-sm text-muted-foreground">
            Contas de acesso ao sistema e respetivos perfis.
          </p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" asChild>
            <Link href="/usuarios/permissoes">
              <ShieldIcon />
              Permissões
            </Link>
          </Button>
          {permissoes.criar && (
            <Button asChild>
              <Link href="/usuarios/novo">
                <PlusIcon />
                Novo Usuário
              </Link>
            </Button>
          )}
        </div>
      </div>
      <DataTable
        columns={usuariosColumns}
        data={usuarios}
        searchKey="nome"
        searchPlaceholder="Pesquisar por nome..."
      />
    </div>
  )
}
