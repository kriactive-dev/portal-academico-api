export type EstadoFormador = "ativo" | "inativo"

export interface Formador {
  id: string
  nome: string
  email: string
  contacto: string
  especialidade: string
  estado: EstadoFormador
}
