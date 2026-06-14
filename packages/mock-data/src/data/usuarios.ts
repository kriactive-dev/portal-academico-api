import type { Usuario } from "../types/usuario"

export const usuariosSeed: Usuario[] = [
  {
    id: "usr-1",
    nome: "Délcio Massingue",
    email: "delcio.massingue@yaacademico.mz",
    roleId: "administrador",
    ativo: true,
  },
  {
    id: "usr-2",
    nome: "Filomena Bila",
    email: "filomena.bila@yaacademico.mz",
    roleId: "secretaria",
    ativo: true,
  },
  {
    id: "usr-3",
    nome: "Tomás Nhaca",
    email: "tomas.nhaca@yaacademico.mz",
    roleId: "financeiro",
    ativo: true,
  },
  {
    id: "usr-4",
    nome: "Graça Sumbana",
    email: "graca.sumbana@yaacademico.mz",
    roleId: "coordenador_academico",
    ativo: true,
  },
]
