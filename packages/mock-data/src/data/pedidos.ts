import type { Pedido } from "../types/pedido"

export const pedidosSeed: Pedido[] = [
  {
    id: "ped-1",
    tipo: "certificado",
    estudanteId: "est-8",
    estado: "aprovado",
    dataSubmissao: "2026-05-20",
    dataResposta: "2026-05-22",
    detalhes: {
      cursoId: "cur-4",
      finalidade: "Candidatura a emprego",
      urgente: false,
    },
  },
  {
    id: "ped-2",
    tipo: "certificado",
    estudanteId: "est-7",
    estado: "pendente",
    dataSubmissao: "2026-06-10",
    detalhes: {
      cursoId: "cur-4",
      finalidade: "Continuação de estudos",
      urgente: true,
    },
  },
  {
    id: "ped-3",
    tipo: "aprovacao_estagio",
    estudanteId: "est-1",
    estado: "pendente",
    dataSubmissao: "2026-06-08",
    detalhes: {
      empresa: "TechSol Lda",
      cargoEstagio: "Estagiário de Suporte Técnico",
      dataInicioEstagio: "2026-07-01",
      dataFimEstagio: "2026-09-30",
    },
  },
  {
    id: "ped-4",
    tipo: "aprovacao_estagio",
    estudanteId: "est-2",
    estado: "negado",
    dataSubmissao: "2026-05-15",
    dataResposta: "2026-05-18",
    motivoNegacao: "Documentação incompleta - falta carta de aceitação da empresa.",
    detalhes: {
      empresa: "Vodacom Moçambique",
      cargoEstagio: "Estagiário de Redes",
      dataInicioEstagio: "2026-06-01",
      dataFimEstagio: "2026-08-31",
    },
  },
  {
    id: "ped-5",
    tipo: "certificado",
    estudanteId: "est-3",
    estado: "pendente",
    dataSubmissao: "2026-06-12",
    detalhes: {
      cursoId: "cur-2",
      finalidade: "Processo de bolsa de estudo",
      urgente: false,
    },
  },
  {
    id: "ped-6",
    tipo: "aprovacao_estagio",
    estudanteId: "est-5",
    estado: "aprovado",
    dataSubmissao: "2026-05-25",
    dataResposta: "2026-05-27",
    detalhes: {
      empresa: "EDM - Electricidade de Moçambique",
      cargoEstagio: "Estagiária Técnica",
      dataInicioEstagio: "2026-06-15",
      dataFimEstagio: "2026-12-15",
    },
  },
]
