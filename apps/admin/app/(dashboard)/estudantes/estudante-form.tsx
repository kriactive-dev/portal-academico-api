"use client"

import { useRouter } from "next/navigation"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { toast } from "sonner"
import { z } from "zod"

import { gerarId } from "@workspace/mock-data/lib"
import { useEstudantesStore, useTurmasStore } from "@workspace/mock-data/stores"
import type { Estudante } from "@workspace/mock-data/types"
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
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@workspace/ui/components/tabs"

import { MultiSelect } from "@/components/multi-select"

const estudanteSchema = z.object({
  numeroEstudante: z.string().min(3, "Indique o número de estudante."),
  nome: z.string().min(3, "O nome deve ter pelo menos 3 caracteres."),
  email: z.email("Email inválido."),
  contacto: z.string().min(9, "Contacto inválido."),
  dataNascimento: z.string().min(1, "Indique a data de nascimento."),
  dataMatricula: z.string().min(1, "Indique a data de matrícula."),
  estado: z.enum(["ativo", "inativo", "concluido"]),
  turmaIds: z.array(z.string()),
  temEncarregado: z.boolean(),
  encarregadoNome: z.string(),
  encarregadoContacto: z.string(),
  encarregadoParentesco: z.string(),
})

type EstudanteFormValues = z.infer<typeof estudanteSchema>

interface EstudanteFormProps {
  estudante?: Estudante
}

export function EstudanteForm({ estudante }: EstudanteFormProps) {
  const router = useRouter()
  const turmas = useTurmasStore((state) => state.turmas)
  const adicionarEstudante = useEstudantesStore((state) => state.adicionarEstudante)
  const atualizarEstudante = useEstudantesStore((state) => state.atualizarEstudante)

  const form = useForm<EstudanteFormValues>({
    resolver: zodResolver(estudanteSchema),
    defaultValues: {
      numeroEstudante: estudante?.numeroEstudante ?? "",
      nome: estudante?.nome ?? "",
      email: estudante?.email ?? "",
      contacto: estudante?.contacto ?? "",
      dataNascimento: estudante?.dataNascimento ?? "",
      dataMatricula: estudante?.dataMatricula ?? "",
      estado: estudante?.estado ?? "ativo",
      turmaIds: estudante?.turmaIds ?? [],
      temEncarregado: !!estudante?.encarregado,
      encarregadoNome: estudante?.encarregado?.nome ?? "",
      encarregadoContacto: estudante?.encarregado?.contacto ?? "",
      encarregadoParentesco: estudante?.encarregado?.parentesco ?? "",
    },
  })

  const temEncarregado = form.watch("temEncarregado")

  function onSubmit(values: EstudanteFormValues) {
    const {
      temEncarregado,
      encarregadoNome,
      encarregadoContacto,
      encarregadoParentesco,
      ...dados
    } = values

    const payload = {
      ...dados,
      encarregado: temEncarregado
        ? {
            nome: encarregadoNome,
            contacto: encarregadoContacto,
            parentesco: encarregadoParentesco,
          }
        : undefined,
    }

    if (estudante) {
      atualizarEstudante(estudante.id, payload)
      toast.success("Estudante atualizado com sucesso.")
    } else {
      adicionarEstudante({ id: gerarId("est"), ...payload })
      toast.success("Estudante criado com sucesso.")
    }
    router.push("/estudantes")
  }

  return (
    <Form {...form}>
      <form
        onSubmit={form.handleSubmit(onSubmit)}
        className="flex flex-col gap-4"
      >
        <Tabs defaultValue="dados">
          <TabsList>
            <TabsTrigger value="dados">Dados</TabsTrigger>
            <TabsTrigger value="encarregado">Encarregado</TabsTrigger>
            <TabsTrigger value="turmas">Turmas</TabsTrigger>
          </TabsList>
          <TabsContent value="dados" className="flex flex-col gap-4 pt-2">
            <div className="grid gap-4 sm:grid-cols-2">
              <FormField
                control={form.control}
                name="numeroEstudante"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Número de estudante</FormLabel>
                    <FormControl>
                      <Input placeholder="EST-2026-001" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="nome"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Nome completo</FormLabel>
                    <FormControl>
                      <Input placeholder="Nome do estudante" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <FormField
                control={form.control}
                name="email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Email</FormLabel>
                    <FormControl>
                      <Input
                        type="email"
                        placeholder="nome@aluno.yaacademico.mz"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="contacto"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Contacto</FormLabel>
                    <FormControl>
                      <Input placeholder="84 000 0000" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <FormField
                control={form.control}
                name="dataNascimento"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Data de nascimento</FormLabel>
                    <FormControl>
                      <Input type="date" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="dataMatricula"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Data de matrícula</FormLabel>
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
                      <SelectItem value="ativo">Ativo</SelectItem>
                      <SelectItem value="inativo">Inativo</SelectItem>
                      <SelectItem value="concluido">Concluído</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />
          </TabsContent>
          <TabsContent value="encarregado" className="flex flex-col gap-4 pt-2">
            <FormField
              control={form.control}
              name="temEncarregado"
              render={({ field }) => (
                <FormItem className="flex flex-row items-center justify-between rounded-lg border p-3">
                  <FormLabel className="flex flex-col gap-1">
                    Tem encarregado de educação
                    <span className="font-normal text-muted-foreground">
                      Aplicável a estudantes menores ou que tenham um
                      encarregado responsável.
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
            {temEncarregado && (
              <>
                <FormField
                  control={form.control}
                  name="encarregadoNome"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Nome do encarregado</FormLabel>
                      <FormControl>
                        <Input placeholder="Nome completo" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <div className="grid gap-4 sm:grid-cols-2">
                  <FormField
                    control={form.control}
                    name="encarregadoContacto"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Contacto do encarregado</FormLabel>
                        <FormControl>
                          <Input placeholder="84 000 0000" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <FormField
                    control={form.control}
                    name="encarregadoParentesco"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Parentesco</FormLabel>
                        <FormControl>
                          <Input placeholder="Ex: Mãe, Pai, Tio(a)" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>
              </>
            )}
          </TabsContent>
          <TabsContent value="turmas" className="flex flex-col gap-4 pt-2">
            <FormField
              control={form.control}
              name="turmaIds"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Turmas</FormLabel>
                  <FormControl>
                    <MultiSelect
                      options={turmas.map((turma) => ({
                        value: turma.id,
                        label: turma.nome,
                      }))}
                      selected={field.value}
                      onChange={field.onChange}
                      placeholder="Selecionar turmas"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </TabsContent>
        </Tabs>
        <div className="flex justify-end gap-2">
          <Button
            type="button"
            variant="outline"
            onClick={() => router.push("/estudantes")}
          >
            Cancelar
          </Button>
          <Button type="submit">
            {estudante ? "Guardar alterações" : "Criar estudante"}
          </Button>
        </div>
      </form>
    </Form>
  )
}
