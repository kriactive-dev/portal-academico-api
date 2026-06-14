"use client"

import { useRouter } from "next/navigation"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { toast } from "sonner"
import { z } from "zod"

import { rolesSeed } from "@workspace/mock-data/data"
import { gerarId } from "@workspace/mock-data/lib"
import { useUsuariosStore } from "@workspace/mock-data/stores"
import type { Usuario } from "@workspace/mock-data/types"
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

const usuarioSchema = z.object({
  nome: z.string().min(3, "O nome deve ter pelo menos 3 caracteres."),
  email: z.email("Email inválido."),
  roleId: z.enum([
    "administrador",
    "secretaria",
    "financeiro",
    "coordenador_academico",
  ]),
  ativo: z.boolean(),
})

type UsuarioFormValues = z.infer<typeof usuarioSchema>

interface UsuarioFormProps {
  usuario?: Usuario
}

export function UsuarioForm({ usuario }: UsuarioFormProps) {
  const router = useRouter()
  const adicionarUsuario = useUsuariosStore((state) => state.adicionarUsuario)
  const atualizarUsuario = useUsuariosStore((state) => state.atualizarUsuario)

  const form = useForm<UsuarioFormValues>({
    resolver: zodResolver(usuarioSchema),
    defaultValues: {
      nome: usuario?.nome ?? "",
      email: usuario?.email ?? "",
      roleId: usuario?.roleId ?? "secretaria",
      ativo: usuario?.ativo ?? true,
    },
  })

  function onSubmit(values: UsuarioFormValues) {
    if (usuario) {
      atualizarUsuario(usuario.id, values)
      toast.success("Usuário atualizado com sucesso.")
    } else {
      adicionarUsuario({ id: gerarId("usr"), ...values })
      toast.success("Usuário criado com sucesso.")
    }
    router.push("/usuarios")
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
                <Input placeholder="Nome do usuário" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="email"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Email</FormLabel>
              <FormControl>
                <Input
                  type="email"
                  placeholder="nome@yaacademico.mz"
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
            name="roleId"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Perfil de acesso</FormLabel>
                <Select onValueChange={field.onChange} value={field.value}>
                  <FormControl>
                    <SelectTrigger className="w-full">
                      <SelectValue />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    {rolesSeed.map((role) => (
                      <SelectItem key={role.id} value={role.id}>
                        {role.nome}
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
                <FormLabel>Usuário ativo</FormLabel>
                <FormControl>
                  <Switch
                    checked={field.value}
                    onCheckedChange={field.onChange}
                  />
                </FormControl>
              </FormItem>
            )}
          />
        </div>
        <div className="flex justify-end gap-2">
          <Button
            type="button"
            variant="outline"
            onClick={() => router.push("/usuarios")}
          >
            Cancelar
          </Button>
          <Button type="submit">
            {usuario ? "Guardar alterações" : "Criar usuário"}
          </Button>
        </div>
      </form>
    </Form>
  )
}
