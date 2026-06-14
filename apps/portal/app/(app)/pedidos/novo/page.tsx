"use client"

import { useRouter } from "next/navigation"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { toast } from "sonner"
import { z } from "zod"

import { gerarId } from "@workspace/mock-data/lib"
import {
  useAuthStore,
  useCursosStore,
  usePedidosStore,
} from "@workspace/mock-data/stores"
import type { Pedido } from "@workspace/mock-data/types"
import { Button } from "@workspace/ui/components/button"
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@workspace/ui/components/form"
import { Input } from "@workspace/ui/components/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@workspace/ui/components/select"
import { Switch } from "@workspace/ui/components/switch"
import { Textarea } from "@workspace/ui/components/textarea"

import { TIPO_LABELS } from "../constants"

const novoPedidoSchema = z
  .object({
    tipo: z.enum(["certificado", "aprovacao_estagio"]),
    cursoId: z.string().optional(),
    finalidade: z.string().optional(),
    urgente: z.boolean(),
    empresa: z.string().optional(),
    cargoEstagio: z.string().optional(),
    dataInicioEstagio: z.string().optional(),
    dataFimEstagio: z.string().optional(),
  })
  .superRefine((data, ctx) => {
    if (data.tipo === "certificado") {
      if (!data.cursoId) {
        ctx.addIssue({
          code: "custom",
          path: ["cursoId"],
          message: "Selecione o curso.",
        })
      }
      if (!data.finalidade?.trim()) {
        ctx.addIssue({
          code: "custom",
          path: ["finalidade"],
          message: "Indique a finalidade do certificado.",
        })
      }
    } else {
      if (!data.empresa?.trim()) {
        ctx.addIssue({
          code: "custom",
          path: ["empresa"],
          message: "Indique o nome da empresa.",
        })
      }
      if (!data.cargoEstagio?.trim()) {
        ctx.addIssue({
          code: "custom",
          path: ["cargoEstagio"],
          message: "Indique o cargo do estágio.",
        })
      }
      if (!data.dataInicioEstagio) {
        ctx.addIssue({
          code: "custom",
          path: ["dataInicioEstagio"],
          message: "Indique a data de início.",
        })
      }
      if (!data.dataFimEstagio) {
        ctx.addIssue({
          code: "custom",
          path: ["dataFimEstagio"],
          message: "Indique a data de fim.",
        })
      }
    }
  })

type NovoPedidoValues = z.infer<typeof novoPedidoSchema>

export default function NovoPedidoPage() {
  const router = useRouter()
  const estudanteAtualId = useAuthStore((state) => state.estudanteAtualId)
  const cursos = useCursosStore((state) => state.cursos)
  const adicionarPedido = usePedidosStore((state) => state.adicionarPedido)

  const form = useForm<NovoPedidoValues>({
    resolver: zodResolver(novoPedidoSchema),
    defaultValues: {
      tipo: "certificado",
      cursoId: "",
      finalidade: "",
      urgente: false,
      empresa: "",
      cargoEstagio: "",
      dataInicioEstagio: "",
      dataFimEstagio: "",
    },
  })

  const tipo = form.watch("tipo")

  function onSubmit(values: NovoPedidoValues) {
    const dataSubmissao = new Date().toISOString().slice(0, 10)

    const pedido: Pedido =
      values.tipo === "certificado"
        ? {
            id: gerarId("ped"),
            tipo: "certificado",
            estudanteId: estudanteAtualId ?? "",
            estado: "pendente",
            dataSubmissao,
            detalhes: {
              cursoId: values.cursoId ?? "",
              finalidade: values.finalidade ?? "",
              urgente: values.urgente,
            },
          }
        : {
            id: gerarId("ped"),
            tipo: "aprovacao_estagio",
            estudanteId: estudanteAtualId ?? "",
            estado: "pendente",
            dataSubmissao,
            detalhes: {
              empresa: values.empresa ?? "",
              cargoEstagio: values.cargoEstagio ?? "",
              dataInicioEstagio: values.dataInicioEstagio ?? "",
              dataFimEstagio: values.dataFimEstagio ?? "",
            },
          }

    adicionarPedido(pedido)
    toast.success("Pedido submetido com sucesso.")
    router.push("/pedidos")
  }

  return (
    <div className="flex flex-col gap-4">
      <h1 className="text-2xl font-semibold">Novo Pedido</h1>
      <Card>
        <CardHeader>
          <CardTitle>Detalhes do pedido</CardTitle>
        </CardHeader>
        <CardContent>
          <Form {...form}>
            <form
              onSubmit={form.handleSubmit(onSubmit)}
              className="flex flex-col gap-4"
            >
              <FormField
                control={form.control}
                name="tipo"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Tipo de pedido</FormLabel>
                    <Select onValueChange={field.onChange} value={field.value}>
                      <FormControl>
                        <SelectTrigger className="w-full">
                          <SelectValue />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {Object.entries(TIPO_LABELS).map(([value, label]) => (
                          <SelectItem key={value} value={value}>
                            {label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />
              {tipo === "certificado" ? (
                <>
                  <FormField
                    control={form.control}
                    name="cursoId"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Curso</FormLabel>
                        <Select
                          onValueChange={field.onChange}
                          value={field.value}
                        >
                          <FormControl>
                            <SelectTrigger className="w-full">
                              <SelectValue placeholder="Selecione o curso" />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {cursos.map((curso) => (
                              <SelectItem key={curso.id} value={curso.id}>
                                {curso.nome}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <FormField
                    control={form.control}
                    name="finalidade"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Finalidade</FormLabel>
                        <FormControl>
                          <Textarea
                            placeholder="Para que finalidade necessita do certificado?"
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <FormField
                    control={form.control}
                    name="urgente"
                    render={({ field }) => (
                      <FormItem className="flex flex-row items-center justify-between rounded-lg border p-3">
                        <FormLabel className="flex flex-col gap-1">
                          Pedido urgente
                          <span className="font-normal text-muted-foreground">
                            Solicite prioridade na emissão do certificado.
                          </span>
                        </FormLabel>
                        <FormControl>
                          <Switch
                            checked={field.value}
                            onCheckedChange={field.onChange}
                          />
                        </FormControl>
                      </FormItem>
                    )}
                  />
                </>
              ) : (
                <>
                  <FormField
                    control={form.control}
                    name="empresa"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Empresa</FormLabel>
                        <FormControl>
                          <Input
                            placeholder="Nome da empresa"
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <FormField
                    control={form.control}
                    name="cargoEstagio"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Cargo</FormLabel>
                        <FormControl>
                          <Input
                            placeholder="Cargo a desempenhar no estágio"
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <div className="grid gap-4 sm:grid-cols-2">
                    <FormField
                      control={form.control}
                      name="dataInicioEstagio"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Início do estágio</FormLabel>
                          <FormControl>
                            <Input type="date" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <FormField
                      control={form.control}
                      name="dataFimEstagio"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Fim do estágio</FormLabel>
                          <FormControl>
                            <Input type="date" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>
                </>
              )}
              <div className="flex justify-end gap-2">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => router.push("/pedidos")}
                >
                  Cancelar
                </Button>
                <Button type="submit">Submeter pedido</Button>
              </div>
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  )
}
