import { Card, CardContent } from "@workspace/ui/components/card"

import { EstudanteForm } from "../estudante-form"

export default function NovoEstudantePage() {
  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Novo Estudante</h1>
        <p className="text-sm text-muted-foreground">
          Matricular um novo estudante no instituto.
        </p>
      </div>
      <Card>
        <CardContent>
          <EstudanteForm />
        </CardContent>
      </Card>
    </div>
  )
}
