"use client"

import { use } from "react"
import { notFound } from "next/navigation"
import { CheckIcon } from "lucide-react"
import { toast } from "sonner"

import {
  useCursosStore,
  useEstudantesStore,
  usePedidosStore,
} from "@workspace/mock-data/stores"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"
import { formatarData } from "@workspace/ui/lib/utils"

import { usePermissoesModulo } from "@/hooks/use-permissoes-modulo"

import { ESTADO_LABELS, ESTADO_VARIANTS, TIPO_LABELS } from "../constants"
import { NegarPedidoDialog } from "../negar-pedido-dialog"

export default function PedidoDetalhePage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const pedido = usePedidosStore((state) =>
    state.pedidos.find((pedido) => pedido.id === id)
  )
  const aprovarPedido = usePedidosStore((state) => state.aprovarPedido)
  const estudantes = useEstudantesStore((state) => state.estudantes)
  const cursos = useCursosStore((state) => state.cursos)
  const permissoes = usePermissoesModulo("pedidos")

  if (!pedido) {
    notFound()
  }

  const estudante = estudantes.find(
    (estudante) => estudante.id === pedido.estudanteId
  )

  const onAprovar = () => {
    aprovarPedido(pedido.id, new Date().toISOString().slice(0, 10))
    toast.success("Pedido aprovado com sucesso.")
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">{TIPO_LABELS[pedido.tipo]}</h1>
          <p className="text-sm text-muted-foreground">
            Pedido submetido por {estudante?.nome ?? "-"}.
          </p>
        </div>
        <Badge variant={ESTADO_VARIANTS[pedido.estado]}>
          {ESTADO_LABELS[pedido.estado]}
        </Badge>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Detalhes do pedido</CardTitle>
          <CardDescription>
            Submetido em {formatarData(pedido.dataSubmissao)}
            {pedido.dataResposta &&
              ` · Respondido em ${formatarData(pedido.dataResposta)}`}
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-4 text-sm">
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <p className="text-muted-foreground">Estudante</p>
              <p className="font-medium">{estudante?.nome ?? "-"}</p>
            </div>
            <div>
              <p className="text-muted-foreground">Número de estudante</p>
              <p className="font-medium">
                {estudante?.numeroEstudante ?? "-"}
              </p>
            </div>
          </div>
          {pedido.tipo === "certificado" ? (
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <p className="text-muted-foreground">Curso</p>
                <p className="font-medium">
                  {cursos.find((curso) => curso.id === pedido.detalhes.cursoId)
                    ?.nome ?? "-"}
                </p>
              </div>
              <div>
                <p className="text-muted-foreground">Finalidade</p>
                <p className="font-medium">{pedido.detalhes.finalidade}</p>
              </div>
              {pedido.detalhes.urgente && (
                <Badge variant="destructive" className="w-fit">
                  Urgente
                </Badge>
              )}
            </div>
          ) : (
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <p className="text-muted-foreground">Empresa</p>
                <p className="font-medium">{pedido.detalhes.empresa}</p>
              </div>
              <div>
                <p className="text-muted-foreground">Cargo</p>
                <p className="font-medium">{pedido.detalhes.cargoEstagio}</p>
              </div>
              <div>
                <p className="text-muted-foreground">Início do estágio</p>
                <p className="font-medium">
                  {formatarData(pedido.detalhes.dataInicioEstagio)}
                </p>
              </div>
              <div>
                <p className="text-muted-foreground">Fim do estágio</p>
                <p className="font-medium">
                  {formatarData(pedido.detalhes.dataFimEstagio)}
                </p>
              </div>
            </div>
          )}
          {pedido.estado === "negado" && pedido.motivoNegacao && (
            <div>
              <p className="text-muted-foreground">Motivo da negação</p>
              <p className="font-medium">{pedido.motivoNegacao}</p>
            </div>
          )}
        </CardContent>
        {permissoes.aprovar && pedido.estado === "pendente" && (
          <CardFooter className="justify-end gap-2">
            <NegarPedidoDialog pedidoId={pedido.id} />
            <Button onClick={onAprovar}>
              <CheckIcon />
              Aprovar
            </Button>
          </CardFooter>
        )}
      </Card>
    </div>
  )
}
