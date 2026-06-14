"use client"

import { useRouter } from "next/navigation"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { toast } from "sonner"
import { z } from "zod"

import { gerarId } from "@workspace/mock-data/lib"
import {
  useCursosStore,
  useEstudantesStore,
  useFormadoresStore,
  useTurmasStore,
} from "@workspace/mock-data/stores"
import type { Turma } from "@workspace/mock-data/types"
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

import { MultiSelect } from "@/components/multi-select"

const turmaSchema = z.object({
  nome: z.string().min(3, "O nome deve ter pelo menos 3 caracteres."),
  cursoId: z.string().min(1, "Selecione um curso."),
  formadorIds: z.array(z.string()),
  estudanteIds: z.array(z.string()),
  turno: z.enum(["manha", "tarde", "noite"]),
  dataInicio: z.string().min(1, "Indique a data de início."),
  dataFim: z.string(),
  estado: z.enum(["planeada", "em_curso", "concluida"]),
})

type TurmaFormValues = z.infer<typeof turmaSchema>

interface TurmaFormProps {
  turma?: Turma
}

export function TurmaForm({ turma }: TurmaFormProps) {
  const router = useRouter()
  const cursos = useCursosStore((state) => state.cursos)
  const formadores = useFormadoresStore((state) => state.formadores)
  const estudantes = useEstudantesStore((state) => state.estudantes)
  const adicionarTurma = useTurmasStore((state) => state.adicionarTurma)
  const atualizarTurma = useTurmasStore((state) => state.atualizarTurma)

  const form = useForm<TurmaFormValues>({
    resolver: zodResolver(turmaSchema),
    defaultValues: {
      nome: turma?.nome ?? "",
      cursoId: turma?.cursoId ?? "",
      formadorIds: turma?.formadorIds ?? [],
      estudanteIds: turma?.estudanteIds ?? [],
      turno: turma?.turno ?? "manha",
      dataInicio: turma?.dataInicio ?? "",
      dataFim: turma?.dataFim ?? "",
      estado: turma?.estado ?? "planeada",
    },
  })

  function onSubmit(values: TurmaFormValues) {
    const dados = {
      ...values,
      dataFim: values.dataFim || undefined,
    }

    if (turma) {
      atualizarTurma(turma.id, dados)
      toast.success("Turma atualizada com sucesso.")
    } else {
      adicionarTurma({ id: gerarId("tur"), ...dados })
      toast.success("Turma criada com sucesso.")
    }
    router.push("/turmas")
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
              <FormLabel>Nome da turma</FormLabel>
              <FormControl>
                <Input placeholder="Ex: Informática - Turma A" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <div className="grid gap-4 sm:grid-cols-2">
          <FormField
            control={form.control}
            name="cursoId"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Curso</FormLabel>
                <Select onValueChange={field.onChange} value={field.value}>
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
            name="turno"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Turno</FormLabel>
                <Select onValueChange={field.onChange} value={field.value}>
                  <FormControl>
                    <SelectTrigger className="w-full">
                      <SelectValue />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    <SelectItem value="manha">Manhã</SelectItem>
                    <SelectItem value="tarde">Tarde</SelectItem>
                    <SelectItem value="noite">Noite</SelectItem>
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )}
          />
        </div>
        <div className="grid gap-4 sm:grid-cols-2">
          <FormField
            control={form.control}
            name="dataInicio"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Data de início</FormLabel>
                <FormControl>
                  <Input type="date" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          <FormField
            control={form.control}
            name="dataFim"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Data de fim (opcional)</FormLabel>
                <FormControl>
                  <Input type="date" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
        </div>
        <FormField
          control={form.control}
          name="estado"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Estado</FormLabel>
              <Select onValueChange={field.onChange} value={field.value}>
                <FormControl>
                  <SelectTrigger className="w-full">
                    <SelectValue />
                  </SelectTrigger>
                </FormControl>
                <SelectContent>
                  <SelectItem value="planeada">Planeada</SelectItem>
                  <SelectItem value="em_curso">Em curso</SelectItem>
                  <SelectItem value="concluida">Concluída</SelectItem>
                </SelectContent>
              </Select>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="formadorIds"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Formadores</FormLabel>
              <FormControl>
                <MultiSelect
                  options={formadores.map((formador) => ({
                    value: formador.id,
                    label: formador.nome,
                  }))}
                  selected={field.value}
                  onChange={field.onChange}
                  placeholder="Selecionar formadores"
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="estudanteIds"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Estudantes</FormLabel>
              <FormControl>
                <MultiSelect
                  options={estudantes.map((estudante) => ({
                    value: estudante.id,
                    label: estudante.nome,
                  }))}
                  selected={field.value}
                  onChange={field.onChange}
                  placeholder="Selecionar estudantes"
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <div className="flex justify-end gap-2">
          <Button
            type="button"
            variant="outline"
            onClick={() => router.push("/turmas")}
          >
            Cancelar
          </Button>
          <Button type="submit">
            {turma ? "Guardar alterações" : "Criar turma"}
          </Button>
        </div>
      </form>
    </Form>
  )
}
