"use client"

import { useRouter } from "next/navigation"
import { LogOutIcon } from "lucide-react"

import {
  useAuthStore,
  useCursosStore,
  useEstudantesStore,
  useTurmasStore,
} from "@workspace/mock-data/stores"
import type { EstadoEstudante } from "@workspace/mock-data/types"
import { Avatar, AvatarFallback } from "@workspace/ui/components/avatar"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"
import { formatarData, getIniciais } from "@workspace/ui/lib/utils"

const ESTADO_LABELS: Record<EstadoEstudante, string> = {
  ativo: "Ativo",
  inativo: "Inativo",
  concluido: "Concluído",
}

const ESTADO_VARIANTS: Record<
  EstadoEstudante,
  "default" | "secondary" | "outline"
> = {
  ativo: "default",
  inativo: "secondary",
  concluido: "outline",
}

export default function PerfilPage() {
  const router = useRouter()
  const estudanteAtualId = useAuthStore((state) => state.estudanteAtualId)
  const logout = useAuthStore((state) => state.logout)
  const estudante = useEstudantesStore((state) =>
    state.estudantes.find((estudante) => estudante.id === estudanteAtualId)
  )
  const turmas = useTurmasStore((state) => state.turmas)
  const cursos = useCursosStore((state) => state.cursos)

  if (!estudante) {
    return null
  }

  const turma = turmas.find((turma) => estudante.turmaIds.includes(turma.id))
  const curso = turma
    ? cursos.find((curso) => curso.id === turma.cursoId)
    : undefined

  function onLogout() {
    logout()
    router.replace("/login")
  }

  return (
    <div className="flex flex-col gap-4">
      <h1 className="text-2xl font-semibold">O meu perfil</h1>
      <Card>
        <CardHeader className="flex flex-row items-center gap-4">
          <Avatar size="lg">
            <AvatarFallback>{getIniciais(estudante.nome)}</AvatarFallback>
          </Avatar>
          <div className="flex flex-col gap-1">
            <CardTitle>{estudante.nome}</CardTitle>
            <p className="text-sm text-muted-foreground">
              {estudante.numeroEstudante}
            </p>
            <Badge variant={ESTADO_VARIANTS[estudante.estado]} className="w-fit">
              {ESTADO_LABELS[estudante.estado]}
            </Badge>
          </div>
        </CardHeader>
        <CardContent className="grid gap-4 text-sm sm:grid-cols-2">
          <div>
            <p className="text-muted-foreground">Email</p>
            <p className="font-medium">{estudante.email}</p>
          </div>
          <div>
            <p className="text-muted-foreground">Contacto</p>
            <p className="font-medium">{estudante.contacto}</p>
          </div>
          <div>
            <p className="text-muted-foreground">Data de nascimento</p>
            <p className="font-medium">
              {formatarData(estudante.dataNascimento)}
            </p>
          </div>
          <div>
            <p className="text-muted-foreground">Data de matrícula</p>
            <p className="font-medium">
              {formatarData(estudante.dataMatricula)}
            </p>
          </div>
          <div>
            <p className="text-muted-foreground">Curso</p>
            <p className="font-medium">{curso?.nome ?? "-"}</p>
          </div>
          <div>
            <p className="text-muted-foreground">Turma</p>
            <p className="font-medium">{turma?.nome ?? "-"}</p>
          </div>
        </CardContent>
      </Card>
      {estudante.encarregado && (
        <Card>
          <CardHeader>
            <CardTitle>Encarregado de Educação</CardTitle>
          </CardHeader>
          <CardContent className="grid gap-4 text-sm sm:grid-cols-2">
            <div>
              <p className="text-muted-foreground">Nome</p>
              <p className="font-medium">{estudante.encarregado.nome}</p>
            </div>
            <div>
              <p className="text-muted-foreground">Contacto</p>
              <p className="font-medium">{estudante.encarregado.contacto}</p>
            </div>
            <div>
              <p className="text-muted-foreground">Parentesco</p>
              <p className="font-medium">
                {estudante.encarregado.parentesco}
              </p>
            </div>
          </CardContent>
        </Card>
      )}
      <Button variant="outline" onClick={onLogout}>
        <LogOutIcon />
        Terminar sessão
      </Button>
    </div>
  )
}
