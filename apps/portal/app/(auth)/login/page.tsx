"use client"

import { useRouter } from "next/navigation"

import { useAuthStore, useEstudantesStore } from "@workspace/mock-data/stores"
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
import type { Estudante } from "@workspace/mock-data/types"

export default function LoginPage() {
  const router = useRouter()
  const estudantes = useEstudantesStore((state) => state.estudantes)
  const loginComoEstudante = useAuthStore((state) => state.loginComoEstudante)

  function handleLogin(estudante: Estudante) {
    loginComoEstudante(estudante)
    router.replace("/")
  }

  return (
    <div className="flex min-h-svh items-center justify-center p-4">
      <Card className="w-full max-w-sm">
        <CardHeader>
          <CardTitle>Ya Académico — Portal do Estudante</CardTitle>
          <CardDescription>
            Selecione o seu perfil para entrar (demonstração).
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-2">
          {estudantes.map((estudante) => (
            <Button
              key={estudante.id}
              variant="outline"
              className="h-auto justify-start gap-3 py-2"
              onClick={() => handleLogin(estudante)}
            >
              <Avatar size="sm">
                <AvatarFallback>{getIniciais(estudante.nome)}</AvatarFallback>
              </Avatar>
              <div className="flex flex-col items-start text-left">
                <span className="text-sm font-medium">{estudante.nome}</span>
                <span className="text-xs text-muted-foreground">
                  {estudante.numeroEstudante}
                </span>
              </div>
            </Button>
          ))}
        </CardContent>
      </Card>
    </div>
  )
}
