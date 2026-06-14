import { Card, CardContent } from "@workspace/ui/components/card"

import { UsuarioForm } from "../usuario-form"

export default function NovoUsuarioPage() {
  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Novo Usuário</h1>
        <p className="text-sm text-muted-foreground">
          Registar uma nova conta de acesso ao sistema.
        </p>
      </div>
      <Card>
        <CardContent>
          <UsuarioForm />
        </CardContent>
      </Card>
    </div>
  )
}
