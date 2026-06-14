"use client"

import { useRouter } from "next/navigation"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { toast } from "sonner"
import { z } from "zod"

import { gerarId } from "@workspace/mock-data/lib"
import { useCursosStore } from "@workspace/mock-data/stores"
import type { Curso } from "@workspace/mock-data/types"
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
import { Switch } from "@workspace/ui/components/switch"
import { Textarea } from "@workspace/ui/components/textarea"

const cursoSchema = z.object({
  nome: z.string().min(3, "O nome deve ter pelo menos 3 caracteres."),
  descricao: z.string().min(5, "Descreva brevemente o curso."),
  duracaoMeses: z.coerce.number().int().positive("Indique a duração em meses."),
  mensalidade: z.coerce.number().positive("Indique o valor da mensalidade."),
  ativo: z.boolean(),
})

type CursoFormValues = z.infer<typeof cursoSchema>

interface CursoFormProps {
  curso?: Curso
}

export function CursoForm({ curso }: CursoFormProps) {
  const router = useRouter()
  const adicionarCurso = useCursosStore((state) => state.adicionarCurso)
  const atualizarCurso = useCursosStore((state) => state.atualizarCurso)

  const form = useForm<CursoFormValues>({
    resolver: zodResolver(cursoSchema),
    defaultValues: {
      nome: curso?.nome ?? "",
      descricao: curso?.descricao ?? "",
      duracaoMeses: curso?.duracaoMeses ?? 6,
      mensalidade: curso?.mensalidade ?? 0,
      ativo: curso?.ativo ?? true,
    },
  })

  function onSubmit(values: CursoFormValues) {
    if (curso) {
      atualizarCurso(curso.id, values)
      toast.success("Curso atualizado com sucesso.")
    } else {
      adicionarCurso({ id: gerarId("cur"), ...values })
      toast.success("Curso criado com sucesso.")
    }
    router.push("/cursos")
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
              <FormLabel>Nome do curso</FormLabel>
              <FormControl>
                <Input placeholder="Ex: Informática" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="descricao"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Descrição</FormLabel>
              <FormControl>
                <Textarea
                  placeholder="Breve descrição do curso"
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
            name="duracaoMeses"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Duração (meses)</FormLabel>
                <FormControl>
                  <Input type="number" min={1} {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          <FormField
            control={form.control}
            name="mensalidade"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Mensalidade (MZN)</FormLabel>
                <FormControl>
                  <Input type="number" min={0} step="0.01" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
        </div>
        <FormField
          control={form.control}
          name="ativo"
          render={({ field }) => (
            <FormItem className="flex flex-row items-center justify-between rounded-lg border p-3">
              <FormLabel className="flex flex-col gap-1">
                Curso ativo
                <span className="font-normal text-muted-foreground">
                  Cursos inativos não aparecem para novas matrículas.
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
            onClick={() => router.push("/cursos")}
          >
            Cancelar
          </Button>
          <Button type="submit">
            {curso ? "Guardar alterações" : "Criar curso"}
          </Button>
        </div>
      </form>
    </Form>
  )
}
