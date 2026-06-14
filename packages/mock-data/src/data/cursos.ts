import type { Curso } from "../types/curso"

export const cursosSeed: Curso[] = [
  {
    id: "cur-1",
    nome: "Informática",
    descricao: "Sistemas e Tecnologias de Informação, manutenção e redes.",
    duracaoMeses: 12,
    mensalidade: 2500,
    ativo: true,
  },
  {
    id: "cur-2",
    nome: "Contabilidade e Gestão",
    descricao: "Contabilidade geral, gestão empresarial e fiscalidade.",
    duracaoMeses: 12,
    mensalidade: 2200,
    ativo: true,
  },
  {
    id: "cur-3",
    nome: "Eletricidade Industrial",
    descricao: "Instalações elétricas, automação e manutenção industrial.",
    duracaoMeses: 18,
    mensalidade: 2800,
    ativo: true,
  },
  {
    id: "cur-4",
    nome: "Mecânica Automóvel",
    descricao: "Diagnóstico, manutenção e reparação de veículos automóveis.",
    duracaoMeses: 18,
    mensalidade: 2600,
    ativo: true,
  },
  {
    id: "cur-5",
    nome: "Inglês Geral",
    descricao: "Curso de inglês para comunicação geral e profissional.",
    duracaoMeses: 6,
    mensalidade: 1500,
    ativo: false,
  },
]
