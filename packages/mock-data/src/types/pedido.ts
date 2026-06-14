export type EstadoPedido = "pendente" | "aprovado" | "negado"

interface PedidoBase {
  id: string
  estudanteId: string
  estado: EstadoPedido
  dataSubmissao: string
  dataResposta?: string
  motivoNegacao?: string
}

export interface PedidoCertificado extends PedidoBase {
  tipo: "certificado"
  detalhes: {
    cursoId: string
    finalidade: string
    urgente: boolean
  }
}

export interface PedidoAprovacaoEstagio extends PedidoBase {
  tipo: "aprovacao_estagio"
  detalhes: {
    empresa: string
    cargoEstagio: string
    dataInicioEstagio: string
    dataFimEstagio: string
  }
}

export type Pedido = PedidoCertificado | PedidoAprovacaoEstagio

export type TipoPedido = Pedido["tipo"]
