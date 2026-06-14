export type TipoTaxa = "matricula" | "inscricao" | "exame" | "certificado" | "outro"

export interface Taxa {
  id: string
  nome: string
  tipo: TipoTaxa
  valor: number
  /** undefined = taxa global, aplicável a todos os cursos */
  cursoId?: string
  ativo: boolean
}
