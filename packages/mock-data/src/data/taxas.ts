import type { Taxa } from "../types/taxa"

export const taxasSeed: Taxa[] = [
  {
    id: "tax-1",
    nome: "Taxa de Matrícula",
    tipo: "matricula",
    valor: 1500,
    ativo: true,
  },
  {
    id: "tax-2",
    nome: "Taxa de Inscrição",
    tipo: "inscricao",
    valor: 500,
    ativo: true,
  },
  {
    id: "tax-3",
    nome: "Taxa de Exame Final",
    tipo: "exame",
    valor: 800,
    ativo: true,
  },
  {
    id: "tax-4",
    nome: "Taxa de Emissão de Certificado",
    tipo: "certificado",
    valor: 600,
    ativo: true,
  },
  {
    id: "tax-5",
    nome: "Taxa de Exame Prático - Mecânica Automóvel",
    tipo: "exame",
    valor: 1200,
    cursoId: "cur-4",
    ativo: true,
  },
]
