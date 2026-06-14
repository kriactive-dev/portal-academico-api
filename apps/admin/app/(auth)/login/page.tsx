"use client"

import { useRouter } from "next/navigation"

import { rolesSeed } from "@workspace/mock-data/data"
import { useAuthStore, useUsuariosStore } from "@workspace/mock-data/stores"
import type { Usuario } from "@workspace/mock-data/types"
import { Avatar, AvatarFallback } from "@workspace/ui/components/avatar"
import { Button } from "@workspace/ui/components/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"
import { getIniciais } from "@workspace/ui/lib/utils"

export default function LoginPage() {
  const router = useRouter()
  const usuarios = useUsuariosStore((state) => state.usuarios)
  const loginComoUsuario = useAuthStore((state) => state.loginComoUsuario)

  function handleLogin(usuario: Usuario) {
    loginComoUsuario(usuario)
    router.replace("/")
  }

  return (
    <div className="flex min-h-svh flex-1 items-center justify-center p-4">
      <Card className="w-full max-w-sm">
        <CardHeader>
          <CardTitle>Ya Académico — Admin</CardTitle>
          <CardDescription>
            Selecione um utilizador para entrar (demonstração)
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-2">
          {usuarios.map((usuario) => {
            const role = rolesSeed.find((role) => role.id === usuario.roleId)
            return (
              <Button
                key={usuario.id}
                variant="outline"
                className="h-auto justify-start gap-3 py-2"
                onClick={() => handleLogin(usuario)}
              >
                <Avatar size="sm">
                  <AvatarFallback>{getIniciais(usuario.nome)}</AvatarFallback>
                </Avatar>
                <div className="flex flex-col items-start">
                  <span className="text-sm font-medium">{usuario.nome}</span>
                  <span className="text-xs text-muted-foreground">{role?.nome}</span>
                </div>
              </Button>
            )
          })}
        </CardContent>
      </Card>
    </div>
  )
}
