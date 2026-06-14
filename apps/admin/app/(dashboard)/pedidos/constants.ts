import type { EstadoPedido, TipoPedido } from "@workspace/mock-data/types"

export const TIPO_LABELS: Record<TipoPedido, string> = {
  certificado: "Certificado",
  aprovacao_estagio: "Aprovação de Estágio",
}

export const ESTADO_LABELS: Record<EstadoPedido, string> = {
  pendente: "Pendente",
  aprovado: "Aprovado",
  negado: "Negado",
}

export const ESTADO_VARIANTS: Record<
  EstadoPedido,
  "default" | "secondary" | "destructive"
> = {
  pendente: "secondary",
  aprovado: "default",
  negado: "destructive",
}
