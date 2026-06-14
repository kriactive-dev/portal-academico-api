import type { Role } from "../types/usuario"

export const rolesSeed: Role[] = [
  {
    id: "administrador",
    nome: "Administrador",
    descricao: "Acesso total ao sistema.",
  },
  {
    id: "secretaria",
    nome: "Secretaria",
    descricao: "Gestão de estudantes, turmas e atendimento.",
  },
  {
    id: "financeiro",
    nome: "Financeiro",
    descricao: "Gestão de taxas e pagamentos.",
  },
  {
    id: "coordenador_academico",
    nome: "Coordenador Académico",
    descricao: "Gestão pedagógica de cursos, turmas e formadores.",
  },
]
