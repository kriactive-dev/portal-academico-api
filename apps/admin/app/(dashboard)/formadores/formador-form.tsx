"use client"

import { useRouter } from "next/navigation"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { toast } from "sonner"
import { z } from "zod"

import { gerarId } from "@workspace/mock-data/lib"
import { useFormadoresStore } from "@workspace/mock-data/stores"
import type { Formador } from "@workspace/mock-data/types"
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

const formadorSchema = z.object({
  nome: z.string().min(3, "O nome deve ter pelo menos 3 caracteres."),
  email: z.email("Email inválido."),
  contacto: z.string().min(9, "Contacto inválido."),
  especialidade: z.string().min(2, "Indique a especialidade."),
  estado: z.enum(["ativo", "inativo"]),
})

type FormadorFormValues = z.infer<typeof formadorSchema>

interface FormadorFormProps {
  formador?: Formador
}

export function FormadorForm({ formador }: FormadorFormProps) {
  const router = useRouter()
  const adicionarFormador = useFormadoresStore(
    (state) => state.adicionarFormador
  )
  const atualizarFormador = useFormadoresStore(
    (state) => state.atualizarFormador
  )

  const form = useForm<FormadorFormValues>({
    resolver: zodResolver(formadorSchema),
    defaultValues: {
      nome: formador?.nome ?? "",
      email: formador?.email ?? "",
      contacto: formador?.contacto ?? "",
      especialidade: formador?.especialidade ?? "",
      estado: formador?.estado ?? "ativo",
    },
  })

  function onSubmit(values: FormadorFormValues) {
    if (formador) {
      atualizarFormador(formador.id, values)
      toast.success("Formador atualizado com sucesso.")
    } else {
      adicionarFormador({ id: gerarId("for"), ...values })
      toast.success("Formador criado com sucesso.")
    }
    router.push("/formadores")
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
              <FormLabel>Nome completo</FormLabel>
              <FormControl>
                <Input placeholder="Nome do formador" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
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
                    placeholder="nome@instituto.co.mz"
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
            name="especialidade"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Especialidade</FormLabel>
                <FormControl>
                  <Input placeholder="Ex: Informática" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
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
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )}
          />
        </div>
        <div className="flex justify-end gap-2">
          <Button
            type="button"
            variant="outline"
            onClick={() => router.push("/formadores")}
          >
            Cancelar
          </Button>
          <Button type="submit">
            {formador ? "Guardar alterações" : "Criar formador"}
          </Button>
        </div>
      </form>
    </Form>
  )
}
