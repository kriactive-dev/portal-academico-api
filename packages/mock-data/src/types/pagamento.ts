export type EstadoPagamento = "pago" | "pendente" | "atrasado"

export type MetodoPagamento =
  | "transferencia"
  | "numerario"
  | "mpesa"
  | "emola"
  | "deposito"

export interface Pagamento {
  id: string
  estudanteId: string
  cursoId: string
  /** Formato "YYYY-MM" */
  mesReferencia: string
  valor: number
  estado: EstadoPagamento
  metodo?: MetodoPagamento
  dataPagamento?: string
  dataVencimento: string
}
