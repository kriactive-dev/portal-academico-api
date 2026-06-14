"use client"

import { useRouter } from "next/navigation"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { toast } from "sonner"
import { z } from "zod"

import { gerarId } from "@workspace/mock-data/lib"
import { useCursosStore, useTaxasStore } from "@workspace/mock-data/stores"
import type { Taxa } from "@workspace/mock-data/types"
import { Button } from "@workspace/ui/components/button"
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

import { TIPO_LABELS } from "./columns"

const SEM_CURSO = "global"

const taxaSchema = z.object({
  nome: z.string().min(3, "O nome deve ter pelo menos 3 caracteres."),
  tipo: z.enum(["matricula", "inscricao", "exame", "certificado", "outro"]),
  valor: z.number().positive("Indique o valor da taxa."),
  cursoId: z.string(),
  ativo: z.boolean(),
})

type TaxaFormValues = z.infer<typeof taxaSchema>

interface TaxaFormProps {
  taxa?: Taxa
}

export function TaxaForm({ taxa }: TaxaFormProps) {
  const router = useRouter()
  const cursos = useCursosStore((state) => state.cursos)
  const adicionarTaxa = useTaxasStore((state) => state.adicionarTaxa)
  const atualizarTaxa = useTaxasStore((state) => state.atualizarTaxa)

  const form = useForm<TaxaFormValues>({
    resolver: zodResolver(taxaSchema),
    defaultValues: {
      nome: taxa?.nome ?? "",
      tipo: taxa?.tipo ?? "matricula",
      valor: taxa?.valor ?? 0,
      cursoId: taxa?.cursoId ?? SEM_CURSO,
      ativo: taxa?.ativo ?? true,
    },
  })

  function onSubmit(values: TaxaFormValues) {
    const dados = {
      ...values,
      cursoId: values.cursoId === SEM_CURSO ? undefined : values.cursoId,
    }

    if (taxa) {
      atualizarTaxa(taxa.id, dados)
      toast.success("Taxa atualizada com sucesso.")
    } else {
      adicionarTaxa({ id: gerarId("tax"), ...dados })
      toast.success("Taxa criada com sucesso.")
    }
    router.push("/taxas")
  }

  return (
    <Form {...form}>
      <form
        onSubmit={form.handleSubmit(onSubmit)}
        className="flex flex-col gap-4"
      >
        <FormField
          control={form.control}
          name="nome"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Nome da taxa</FormLabel>
              <FormControl>
                <Input placeholder="Ex: Taxa de Matrícula" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <div className="grid gap-4 sm:grid-cols-2">
          <FormField
            control={form.control}
            name="tipo"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Tipo</FormLabel>
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
          <FormField
            control={form.control}
            name="valor"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Valor (MZN)</FormLabel>
                <FormControl>
                  <Input
                    type="number"
                    min={0}
                    step="0.01"
                    {...field}
                    onChange={(event) =>
                      field.onChange(event.target.valueAsNumber)
                    }
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
        </div>
        <FormField
          control={form.control}
          name="cursoId"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Curso</FormLabel>
              <Select onValueChange={field.onChange} value={field.value}>
                <FormControl>
                  <SelectTrigger className="w-full">
                    <SelectValue />
                  </SelectTrigger>
                </FormControl>
                <SelectContent>
                  <SelectItem value={SEM_CURSO}>Todos os cursos</SelectItem>
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
          name="ativo"
          render={({ field }) => (
            <FormItem className="flex flex-row items-center justify-between rounded-lg border p-3">
              <FormLabel className="flex flex-col gap-1">
                Taxa ativa
                <span className="font-normal text-muted-foreground">
                  Taxas inativas não são aplicadas a novos pagamentos.
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
        <div className="flex justify-end gap-2">
          <Button
            type="button"
            variant="outline"
            onClick={() => router.push("/taxas")}
          >
            Cancelar
          </Button>
          <Button type="submit">
            {taxa ? "Guardar alterações" : "Criar taxa"}
          </Button>
        </div>
      </form>
    </Form>
  )
}
