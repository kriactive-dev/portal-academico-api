import type {
  ModuloSistema,
  PermissoesModulo,
  PermissoesPorRole,
} from "../types/usuario"

const MODULOS: ModuloSistema[] = [
  "dashboard",
  "estudantes",
  "cursos",
  "turmas",
  "formadores",
  "taxas",
  "pagamentos",
  "usuarios",
  "pedidos",
]

function semAcesso(): PermissoesModulo {
  return { ver: false, criar: false, editar: false, eliminar: false, aprovar: false }
}

function acessoTotal(): PermissoesModulo {
  return { ver: true, criar: true, editar: true, eliminar: true, aprovar: true }
}

function permissao(overrides: Partial<PermissoesModulo>): PermissoesModulo {
  return { ...semAcesso(), ...overrides }
}

function construirMatriz(
  definicoes: Partial<Record<ModuloSistema, PermissoesModulo>>
): Record<ModuloSistema, PermissoesModulo> {
  const matriz = {} as Record<ModuloSistema, PermissoesModulo>
  for (const modulo of MODULOS) {
    matriz[modulo] = definicoes[modulo] ?? semAcesso()
  }
  return matriz
}

export const permissoesPorRoleSeed: PermissoesPorRole = {
  administrador: construirMatriz(
    MODULOS.reduce(
      (acc, modulo) => ({ ...acc, [modulo]: acessoTotal() }),
      {} as Partial<Record<ModuloSistema, PermissoesModulo>>
    )
  ),
  secretaria: construirMatriz({
    dashboard: permissao({ ver: true }),
    estudantes: permissao({ ver: true, criar: true, editar: true }),
    cursos: permissao({ ver: true }),
    turmas: permissao({ ver: true, criar: true, editar: true }),
    formadores: permissao({ ver: true }),
    taxas: permissao({ ver: true }),
    pagamentos: permissao({ ver: true }),
    pedidos: permissao({ ver: true, aprovar: true }),
  }),
  financeiro: construirMatriz({
    dashboard: permissao({ ver: true }),
    estudantes: permissao({ ver: true }),
    cursos: permissao({ ver: true }),
    turmas: permissao({ ver: true }),
    formadores: permissao({ ver: true }),
    taxas: acessoTotal(),
    pagamentos: permissao({ ver: true, criar: true, editar: true, eliminar: true }),
    pedidos: permissao({ ver: true }),
  }),
  coordenador_academico: construirMatriz({
    dashboard: permissao({ ver: true }),
    estudantes: permissao({ ver: true }),
    cursos: permissao({ ver: true, criar: true, editar: true }),
    turmas: permissao({ ver: true, criar: true, editar: true }),
    formadores: permissao({ ver: true, criar: true, editar: true }),
    taxas: permissao({ ver: true }),
    pedidos: permissao({ ver: true, aprovar: true }),
  }),
}
