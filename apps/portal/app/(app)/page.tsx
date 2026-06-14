"use client"

import Link from "next/link"
import { PlusIcon } from "lucide-react"

import {
  useAuthStore,
  useCursosStore,
  useEstudantesStore,
  usePedidosStore,
  useTurmasStore,
} from "@workspace/mock-data/stores"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"

import { PedidoCard } from "./pedidos/pedido-card"

export default function InicioPage() {
  const estudanteAtualId = useAuthStore((state) => state.estudanteAtualId)
  const estudante = useEstudantesStore((state) =>
    state.estudantes.find((estudante) => estudante.id === estudanteAtualId)
  )
  const turmas = useTurmasStore((state) => state.turmas)
  const cursos = useCursosStore((state) => state.cursos)
  const pedidos = usePedidosStore((state) => state.pedidos)

  if (!estudante) {
    return null
  }

  const turma = turmas.find((turma) => estudante.turmaIds.includes(turma.id))
  const curso = turma
    ? cursos.find((curso) => curso.id === turma.cursoId)
    : undefined

  const meusPedidos = pedidos
    .filter((pedido) => pedido.estudanteId === estudante.id)
    .sort((a, b) => b.dataSubmissao.localeCompare(a.dataSubmissao))
  const pedidosPendentes = meusPedidos.filter(
    (pedido) => pedido.estado === "pendente"
  )

  const primeiroNome = estudante.nome.split(" ")[0]

  return (
    <div className="flex flex-1 flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Olá, {primeiroNome}</CardTitle>
          <CardDescription>
            {curso && turma ? `${curso.nome} · ${turma.nome}` : "-"}
          </CardDescription>
        </CardHeader>
        <CardContent className="flex items-center justify-between gap-2">
          <p className="text-sm text-muted-foreground">
            Pedidos pendentes
          </p>
          <Badge variant="secondary">{pedidosPendentes.length}</Badge>
        </CardContent>
      </Card>
      <Button asChild>
        <Link href="/pedidos/novo">
          <PlusIcon />
          Novo Pedido
        </Link>
      </Button>
      <div className="flex flex-col gap-3">
        <h2 className="text-lg font-semibold">Pedidos recentes</h2>
        {meusPedidos.length === 0 ? (
          <p className="py-4 text-center text-sm text-muted-foreground">
            Ainda não submeteu nenhum pedido.
          </p>
        ) : (
          meusPedidos
            .slice(0, 3)
            .map((pedido) => <PedidoCard key={pedido.id} pedido={pedido} />)
        )}
      </div>
    </div>
  )
}
