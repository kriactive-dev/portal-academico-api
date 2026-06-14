"use client"

import { useState } from "react"

import { rolesSeed } from "@workspace/mock-data/data"
import { usePermissoesStore } from "@workspace/mock-data/stores"
import type {
  AcaoPermissao,
  ModuloSistema,
  RoleId,
} from "@workspace/mock-data/types"
import { Checkbox } from "@workspace/ui/components/checkbox"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@workspace/ui/components/table"
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from "@workspace/ui/components/tabs"

import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

const MODULO_LABELS: Record<ModuloSistema, string> = {
  dashboard: "Dashboard",
  estudantes: "Estudantes",
  cursos: "Cursos",
  turmas: "Turmas",
  formadores: "Formadores",
  taxas: "Taxas",
  pagamentos: "Pagamentos",
  usuarios: "Usuários",
  pedidos: "Pedidos",
}

const ACOES: AcaoPermissao[] = ["ver", "criar", "editar", "eliminar", "aprovar"]

const ACAO_LABELS: Record<AcaoPermissao, string> = {
  ver: "Ver",
  criar: "Criar",
  editar: "Editar",
  eliminar: "Eliminar",
  aprovar: "Aprovar",
}

export default function PermissoesPage() {
  const permissoes = usePermissoesStore((state) => state.permissoes)
  const atualizarPermissao = usePermissoesStore(
    (state) => state.atualizarPermissao
  )
  const permissoesUsuarios = usePermissoesModulo("usuarios")
  const [roleAtiva, setRoleAtiva] = useState<RoleId>("administrador")

  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Permissões</h1>
        <p className="text-sm text-muted-foreground">
          Defina o acesso de cada perfil aos módulos do sistema.
        </p>
      </div>
      <Tabs
        value={roleAtiva}
        onValueChange={(value) => setRoleAtiva(value as RoleId)}
      >
        <TabsList>
          {rolesSeed.map((role) => (
            <TabsTrigger key={role.id} value={role.id}>
              {role.nome}
            </TabsTrigger>
          ))}
        </TabsList>
        {rolesSeed.map((role) => (
          <TabsContent key={role.id} value={role.id} className="pt-2">
            <p className="pb-4 text-sm text-muted-foreground">
              {role.descricao}
            </p>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Módulo</TableHead>
                  {ACOES.map((acao) => (
                    <TableHead key={acao} className="text-center">
                      {ACAO_LABELS[acao]}
                    </TableHead>
                  ))}
                </TableRow>
              </TableHeader>
              <TableBody>
                {Object.entries(MODULO_LABELS).map(([modulo, label]) => (
                  <TableRow key={modulo}>
                    <TableCell className="font-medium">{label}</TableCell>
                    {ACOES.map((acao) => (
                      <TableCell key={acao} className="text-center">
                        <Checkbox
                          checked={
                            permissoes[role.id][modulo as ModuloSistema][acao]
                          }
                          disabled={!permissoesUsuarios.editar}
                          onCheckedChange={(checked) =>
                            atualizarPermissao(
                              role.id,
                              modulo as ModuloSistema,
                              acao,
                              checked === true
                            )
                          }
                        />
                      </TableCell>
                    ))}
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TabsContent>
        ))}
      </Tabs>
    </div>
  )
}
