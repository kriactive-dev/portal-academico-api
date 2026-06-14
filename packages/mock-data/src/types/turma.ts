export type TurnoTurma = "manha" | "tarde" | "noite"

export type EstadoTurma = "planeada" | "em_curso" | "concluida"

export interface Turma {
  id: string
  nome: string
  cursoId: string
  formadorIds: string[]
  estudanteIds: string[]
  turno: TurnoTurma
  dataInicio: string
  dataFim?: string
  estado: EstadoTurma
}
