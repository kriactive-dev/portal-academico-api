export type RoleId =
  | "administrador"
  | "secretaria"
  | "financeiro"
  | "coordenador_academico"

export interface Role {
  id: RoleId
  nome: string
  descricao: string
}

export type ModuloSistema =
  | "dashboard"
  | "estudantes"
  | "cursos"
  | "turmas"
  | "formadores"
  | "taxas"
  | "pagamentos"
  | "usuarios"
  | "pedidos"

export type AcaoPermissao = "ver" | "criar" | "editar" | "eliminar" | "aprovar"

export type PermissoesModulo = Record<AcaoPermissao, boolean>

export type PermissoesPorRole = Record<RoleId, Record<ModuloSistema, PermissoesModulo>>

export interface Usuario {
  id: string
  nome: string
  email: string
  roleId: RoleId
  ativo: boolean
}
