"use client"

import { Bar, BarChart, CartesianGrid, XAxis } from "recharts"

import {
  useEstudantesStore,
  usePagamentosStore,
  useTurmasStore,
} from "@workspace/mock-data/stores"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"
import {
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
  type ChartConfig,
} from "@workspace/ui/components/chart"
import { formatarMoeda } from "@workspace/ui/lib/utils"

const chartConfig: ChartConfig = {
  receita: {
    label: "Receita",
    color: "var(--color-chart-1)",
  },
}

export default function DashboardPage() {
  const estudantes = useEstudantesStore((state) => state.estudantes)
  const turmas = useTurmasStore((state) => state.turmas)
  const pagamentos = usePagamentosStore((state) => state.pagamentos)

  const estudantesAtivos = estudantes.filter(
    (estudante) => estudante.estado === "ativo"
  ).length
  const turmasEmCurso = turmas.filter(
    (turma) => turma.estado === "em_curso"
  ).length

  const mesAtual = new Date().toISOString().slice(0, 7)
  const receitaMesAtual = pagamentos
    .filter(
      (pagamento) =>
        pagamento.estado === "pago" && pagamento.mesReferencia === mesAtual
    )
    .reduce((total, pagamento) => total + pagamento.valor, 0)

  const pagamentosPendentes = pagamentos.filter(
    (pagamento) => pagamento.estado !== "pago"
  ).length

  const receitaPorMes = pagamentos
    .filter((pagamento) => pagamento.estado === "pago")
    .reduce<Record<string, number>>((acc, pagamento) => {
      acc[pagamento.mesReferencia] =
        (acc[pagamento.mesReferencia] ?? 0) + pagamento.valor
      return acc
    }, {})

  const chartData = Object.entries(receitaPorMes)
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([mes, receita]) => ({ mes, receita }))

  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Dashboard</h1>
        <p className="text-sm text-muted-foreground">
          Visão geral da gestão académica.
        </p>
      </div>
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader>
            <CardDescription>Estudantes ativos</CardDescription>
            <CardTitle className="text-2xl">{estudantesAtivos}</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader>
            <CardDescription>Turmas em curso</CardDescription>
            <CardTitle className="text-2xl">{turmasEmCurso}</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader>
            <CardDescription>Receita do mês</CardDescription>
            <CardTitle className="text-2xl">
              {formatarMoeda(receitaMesAtual)}
            </CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader>
            <CardDescription>Pagamentos por regularizar</CardDescription>
            <CardTitle className="text-2xl">{pagamentosPendentes}</CardTitle>
          </CardHeader>
        </Card>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Receita mensal</CardTitle>
          <CardDescription>
            Pagamentos recebidos por mês de referência.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <ChartContainer config={chartConfig} className="aspect-auto h-64 w-full">
            <BarChart data={chartData}>
              <CartesianGrid vertical={false} />
              <XAxis dataKey="mes" tickLine={false} axisLine={false} />
              <ChartTooltip
                content={
                  <ChartTooltipContent
                    formatter={(value) => formatarMoeda(Number(value))}
                  />
                }
              />
              <Bar dataKey="receita" fill="var(--color-receita)" radius={4} />
            </BarChart>
          </ChartContainer>
        </CardContent>
      </Card>
    </div>
  )
}
