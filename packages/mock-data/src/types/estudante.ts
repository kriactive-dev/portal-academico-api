export type EstadoEstudante = "ativo" | "inativo" | "concluido"

export interface EncarregadoDeEducacao {
  nome: string
  contacto: string
  parentesco: string
}

export interface Estudante {
  id: string
  numeroEstudante: string
  nome: string
  email: string
  contacto: string
  dataNascimento: string
  estado: EstadoEstudante
  turmaIds: string[]
  dataMatricula: string
  encarregado?: EncarregadoDeEducacao
}
